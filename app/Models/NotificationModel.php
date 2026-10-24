<?php
namespace App\Models;
use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'category', 'description', 'recipient', 'priority', 'status', 'channel', 'is_read', 'read_at', 'created_at'
    ];

    // Category -> module, matching the same mapping the Notification Center
    // view uses to route a row click to its owning page. Shared single
    // source of truth for scoping notifications to a restricted role's
    // sidebar, used by both NotificationController and the topbar badge
    // in layouts/main.php.
    public const CATEGORY_MODULE = [
        'Vehicle Inspection'               => 'vehicles',
        'Vehicle Expiry'                   => 'vehicles',
        'Trip Ticket Request'              => 'vehicles',
        'Trip Ticket Assignment'           => 'vehicles',
        'Travel Reminder'                  => 'vehicles',
        'Vehicle Service Due'              => 'motorpool',
        'Motor Pool Work Order'            => 'motorpool',
        'Equipment Needs Repair'           => 'motorpool',
        'Sports Equipment Borrowed'        => 'sports',
        'Sports Equipment Overdue'         => 'sports',
        'Sports Equipment Needs Repair'    => 'sports',
        'Inventory Low Stock'              => 'tools',
        'Tool Borrowed'                    => 'tools',
        'Tool Due Soon'                    => 'tools',
        'Tool Overdue'                     => 'tools',
        'Sports Equipment Due Soon'        => 'sports',
        'Janitorial Assignment'            => 'janitorial',
        'Cleaning Scheduled'               => 'janitorial',
        'Urgent Cleaning Scheduled'        => 'janitorial',
        'Consumable Low Stock'             => 'janitorial',
        'Air-Con Cleaning'                 => 'aircon',
        'Fire Extinguisher Installed'      => 'firesafety',
        'Fire Extinguisher Expiring Soon'  => 'firesafety',
        'Aircon Unit Registered'           => 'aircon',
        'Aircon Needs Cleaning'            => 'aircon',
        'Maintenance Scheduled'            => 'safety',
        'Job Order Expiring Soon'          => 'personnel',
        'Job Order Expired'                => 'personnel',
        'Contract Expiring Soon'           => 'personnel',
        'Contract Expired'                 => 'personnel',
        'Personnel Document Incomplete'    => 'personnel',
    ];

    // Only roles with a restricted sidebar are scoped here — everyone else
    // (Administrator — the only "general", unfiltered view of the log — and
    // any other existing role) keeps seeing everything, unchanged.
    public static function allowedModulesForRole(string $role): ?array
    {
        return match (strtolower($role)) {
            'facilities' => ['tools', 'personnel', 'aircon', 'safety'],
            // Safety & Security: fire safety only (vehicles belong to Asset Acquisition now).
            'security'   => ['firesafety'],
            // Asset Acquisition and Monitoring: vehicles, trips and the motor pool.
            'assets'     => ['vehicles', 'motorpool'],
            // Sports Equipment Monitoring: only sports equipment notifications.
            'sports'     => ['sports'],
            // Tools Head = the Tools & Equipment / "Maintenance" office —
            // only its own inventory/borrow notifications.
            'tools'      => ['tools'],
            'janitorial' => ['janitorial'],
            'safety'     => ['safety', 'firesafety', 'aircon'],
            default      => null,
        };
    }

    // A notification whose category isn't in CATEGORY_MODULE (general/
    // unclassified, e.g. a hand-composed draft) is always kept rather than
    // silently hidden.
    public static function scopeToRole(array $rows, string $role): array
    {
        $allowed = self::allowedModulesForRole($role);
        if ($allowed === null) {
            return $rows;
        }

        return array_values(array_filter($rows, function ($n) use ($allowed) {
            $module = self::CATEGORY_MODULE[$n['category'] ?? ''] ?? null;
            return $module === null || in_array($module, $allowed, true);
        }));
    }

    /**
     * Inserts a notification unless an identical unread one (same category +
     * description) is already waiting, so repeated syncs / re-scans don't
     * pile up duplicates.
     */
    public function notifyOnce(string $category, string $description, string $recipient, string $priority, string $status = 'Pending'): void
    {
        $exists = $this->where('category', $category)
                       ->where('description', $description)
                       ->where('is_read', 0)
                       ->countAllResults();
        if ($exists) {
            return;
        }

        $this->insert([
            'category'    => $category,
            'description' => $description,
            'recipient'   => $recipient,
            'priority'    => $priority,
            'status'      => $status,
            'channel'     => 'system',
            'is_read'     => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * "Consumable Low Stock" alert (CRITICAL at zero, MODERATE at/below the
     * reorder threshold). No-op while stock is still above the threshold.
     * Shared by every path that can lower consumable stock — the mobile
     * app's stock report and scan-borrow — so they all raise the same alert.
     */
    public function notifyLowStock(string $itemName, float $qty, float $threshold, string $unit, string $note = ''): void
    {
        if ($qty > $threshold) {
            return;
        }

        $state = $qty <= 0 ? 'is out of stock' : 'is running low';
        $this->notifyOnce(
            'Consumable Low Stock',
            "{$itemName} {$state}{$note} — {$qty} {$unit} left, reorder threshold is {$threshold} {$unit}.",
            'Facilities Team',
            $qty <= 0 ? 'CRITICAL' : 'MODERATE'
        );
    }

    /** Sports Equipment Monitoring alerts (borrowed / overdue / needs repair). */
    public function notifySports(string $category, string $description, string $priority = 'ROUTINE'): void
    {
        $this->notifyOnce($category, $description, 'Sports Equipment Office', $priority);
    }

    /**
     * Raises "Sports Equipment Overdue" for every open sports borrow past its
     * due date. The description is stable per borrow (it names the due
     * date, not "N days ago") so notifyOnce() raises it once, not daily.
     */
    public function syncSportsOverdue(): void
    {
        $rows = db_connect()->query(
            "SELECT t.asset_name, b.borrower, b.department, b.expected_return
               FROM borrow_records b JOIN tools t ON t.id = b.tool_id
              WHERE t.category = 'Sports Equipment' AND b.status = 'Borrowed' AND b.is_archived = 0
                AND b.expected_return IS NOT NULL AND b.expected_return < CURDATE()"
        )->getResultArray();

        foreach ($rows as $r) {
            $who = ($r['borrower'] ?: 'an unrecorded borrower') . ($r['department'] ? " ({$r['department']})" : '');
            $this->notifySports(
                'Sports Equipment Overdue',
                "{$r['asset_name']} was due back on " . date('M j, Y', strtotime($r['expected_return'])) . " — borrowed by {$who}.",
                'CRITICAL'
            );
        }
    }

    /**
     * Raises "due soon" (due today or tomorrow) and "overdue" alerts for every
     * open, non-consumable borrow - tools and sports equipment alike. Runs
     * from the shared layout on page loads, throttled so it's one cheap query
     * every few minutes rather than one per request. Descriptions name the
     * due date, so notifyOnce() raises each alert once; an extension moves
     * the due date and therefore (correctly) raises a fresh alert later.
     * Sports overdue keeps its existing category via syncSportsOverdue().
     */
    public function syncBorrowDueAlerts(): void
    {
        $cache = cache();
        if ($cache->get('borrow_due_sync_ran')) {
            return;
        }
        $cache->save('borrow_due_sync_ran', 1, 300);

        $rows = db_connect()->query(
            "SELECT t.asset_name, t.category, b.borrower, b.department, b.expected_return
               FROM borrow_records b JOIN tools t ON t.id = b.tool_id
              WHERE b.status = 'Borrowed' AND b.is_archived = 0 AND t.is_archived = 0
                AND t.category <> 'Consumable'
                AND b.expected_return IS NOT NULL
                AND b.expected_return <= DATE_ADD(CURDATE(), INTERVAL 1 DAY)"
        )->getResultArray();

        $today = date('Y-m-d');
        foreach ($rows as $r) {
            $isSports = $r['category'] === 'Sports Equipment';
            $who  = ($r['borrower'] ?: 'an unrecorded borrower') . ($r['department'] ? " ({$r['department']})" : '');
            $due  = date('M j, Y', strtotime($r['expected_return']));
            $over = $r['expected_return'] < $today;

            if ($over && $isSports) {
                continue; // handled by syncSportsOverdue() under its own category
            }

            $category  = $over ? 'Tool Overdue' : ($isSports ? 'Sports Equipment Due Soon' : 'Tool Due Soon');
            $text      = $over
                ? "{$r['asset_name']} was due back on {$due} - borrowed by {$who}."
                : "{$r['asset_name']} is due back on {$due} - borrowed by {$who}.";
            $recipient = $isSports ? 'Sports Equipment Office' : 'Tools & Equipment Office';

            $this->notifyOnce($category, $text, $recipient, $over ? 'CRITICAL' : 'MODERATE');
        }

        $this->syncSportsOverdue();
    }

    public function getAllSorted()
    {
        return $this->where('status !=', 'Draft')->orderBy('created_at', 'DESC')->findAll();
    }

    public function getDrafts()
    {
        return $this->where('status', 'Draft')->orderBy('created_at', 'DESC')->findAll();
    }

    public function getUnreadCount()
    {
        return $this->where('is_read', 0)->where('status !=', 'Draft')->countAllResults();
    }

    public function getUnreadCountForRole(string $role): int
    {
        $rows = self::scopeToRole($this->getAllSorted(), $role);
        return count(array_filter($rows, fn($n) => (int) ($n['is_read'] ?? 0) === 0));
    }

    public function markAllRead()
    {
        $this->where('is_read', 0)->set(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')])->update();
    }
}
