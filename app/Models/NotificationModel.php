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
        'Inventory Low Stock'              => 'tools',
        'Janitorial Assignment'            => 'janitorial',
        'Cleaning Scheduled'               => 'janitorial',
        'Urgent Cleaning Scheduled'        => 'janitorial',
        'Air-Con Cleaning'                 => 'safety',
        'Fire Extinguisher Installed'      => 'safety',
        'Fire Extinguisher Expiring Soon'  => 'safety',
        'Aircon Unit Registered'           => 'safety',
        'Aircon Needs Cleaning'            => 'safety',
        'Maintenance Scheduled'            => 'safety',
        'Job Order Expiring Soon'          => 'personnel',
        'Job Order Expired'                => 'personnel',
        'Contract Expiring Soon'           => 'personnel',
        'Contract Expired'                 => 'personnel',
        'Personnel Document Incomplete'    => 'personnel',
    ];

    // Only roles with a restricted sidebar are scoped here — everyone else
    // (Administrator, and any other existing role) keeps seeing everything,
    // unchanged.
    public static function allowedModulesForRole(string $role): ?array
    {
        return match (strtolower($role)) {
            'facilities' => ['tools', 'personnel'],
            'security'   => ['safety', 'vehicles'],
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
