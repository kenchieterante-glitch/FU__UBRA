<?php

namespace App\Controllers;

use App\Models\JanitorialAssignmentModel;
use App\Models\JanitorialTaskModel;
use App\Models\JanitorialTaskHistoryModel;
use App\Models\ConsumableInventoryModel;
use App\Models\RefillLogModel;
use App\Models\NotificationModel;

class JanitorialController extends BaseController
{
    protected $session;
    protected $assignmentModel;
    protected $taskModel;
    protected $inventoryModel;
    protected $refillLogModel;

    // Maps each real assigned_zone value onto the fixed slug used by the campus map SVG.
    private const ZONE_SLUGS = [
        'Admin Building'    => 'admin',
        'Library'           => 'library',
        'Science Building'  => 'science',
        'Gymnasium'         => 'gym',
        'Canteen'           => 'canteen',
        'Engineering'       => 'engr',
        'CCS Building'      => 'ccs',
        'Clinic'            => 'clinic',
    ];

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->assignmentModel = new JanitorialAssignmentModel();
        $this->taskModel = new JanitorialTaskModel();
        $this->inventoryModel = new ConsumableInventoryModel();
        $this->refillLogModel = new RefillLogModel();
    }

    public function index()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $this->resetStaleCompletedTasks();

        $assignments = $this->assignmentModel->findAll();
        $allTasks    = $this->taskModel->findAll();

        $tasksByAssignment = [];
        foreach ($allTasks as $task) {
            $tasksByAssignment[$task['assignment_id']][] = $task;
        }

        // A building can have more than one staff/shift assigned to it (e.g.
        // CCS Building has two). Group by zone FIRST, then build one merged
        // checklist per zone — so the map color, the drill-down panel, and
        // the "X/Y cleaned" stat all read from the exact same merged data
        // and can never disagree with each other.
        $assignmentsByZone = [];
        foreach ($assignments as $a) {
            $slug = self::ZONE_SLUGS[$a['assigned_zone']] ?? strtolower(preg_replace('/[^a-z0-9]+/', '-', $a['assigned_zone']));
            $assignmentsByZone[$slug][] = $a;
        }

        $areas = [];
        $checklists = [];
        $staff = [];

        foreach ($assignmentsByZone as $slug => $zoneAssignments) {
            $zoneName   = $zoneAssignments[0]['assigned_zone'];
            $multiStaff = count($zoneAssignments) > 1;
            $areas[$slug] = ['name' => $zoneName];

            $mergedTasks  = [];
            $staffNames   = [];
            $shiftLabels  = [];
            $anyStaffDone = false;

            // Grouped alongside the zone-wide merge below (not instead of
            // it) — this only feeds the drill-down panel's per-floor tabs.
            // The zone-level "cleaned" rule above (any one shift fully
            // done) and everywhere it's used (Dashboard, mobile API) stays
            // exactly as it was before floors existed.
            $assignmentsByFloor = [];

            foreach ($zoneAssignments as $a) {
                $tasks = $tasksByAssignment[$a['id']] ?? [];
                $done  = count(array_filter($tasks, fn($t) => (int) $t['is_done'] === 1));
                $total = count($tasks);
                $shift = date('gA', strtotime($a['shift_start'])) . '-' . date('gA', strtotime($a['shift_end']));

                if ($total > 0 && $done === $total) {
                    $anyStaffDone = true;
                }

                $staffNames[]  = $a['staff_name'];
                $shiftLabels[] = $shift;

                foreach ($tasks as $t) {
                    $mergedTasks[] = [
                        't'    => $multiStaff ? "{$t['task_name']} ({$a['staff_name']})" : $t['task_name'],
                        'done' => (bool) $t['is_done'],
                        'time' => $t['completed_at'] ? date('H:i', strtotime($t['completed_at'])) : null,
                    ];
                }

                $staff[] = [
                    'id'          => (int) $a['id'],
                    'name'        => $a['staff_name'],
                    'zone'        => $zoneName,
                    'floor'       => $a['floor'] ?: 'Ground Floor',
                    'priority'    => $a['priority'] ?? 'Routine',
                    'shiftStart'  => substr($a['shift_start'], 0, 5),
                    'shiftEnd'    => substr($a['shift_end'], 0, 5),
                    'tasks' => $total,
                    'done'  => $done,
                    'photo' => strtoupper(substr($a['staff_name'], 0, 1)),
                    'shift' => $shift,
                    'area'  => $slug,
                ];

                $assignmentsByFloor[$a['floor'] ?: 'Ground Floor'][] = $a;
            }

            // Completed tasks are listed before pending ones (adviser feedback).
            usort($mergedTasks, fn($a, $b) => (int) $b['done'] <=> (int) $a['done']);

            $byFloor = [];
            foreach ($assignmentsByFloor as $floorName => $floorAssignments) {
                $byFloor[$floorName] = $this->buildMergedChecklist($floorAssignments, $tasksByAssignment);
            }
            uksort($byFloor, [$this, 'compareFloorNames']);

            $checklists[$slug] = [
                'staff'       => implode(' & ', $staffNames),
                'shift'       => implode(' / ', array_unique($shiftLabels)),
                'tasks'       => $mergedTasks,
                'anyStaffDone' => $anyStaffDone,
                'byFloor'     => $byFloor,
            ];
        }

        $inventory = $this->inventoryModel->findAll();

        // A zone counts as cleaned as soon as at least one staff member
        // assigned to it has finished all of their own tasks — a zone with
        // several staff (e.g. CCS Building) doesn't wait on everyone.
        $totalZones   = count($checklists);
        $cleanedZones = count(array_filter($checklists, fn($c) => $c['anyStaffDone']));
        $pendingZones  = $totalZones - $cleanedZones;
        $lowStock      = count(array_filter($inventory, fn($i) => (float) $i['current_stock'] <= (float) $i['reorder_threshold'] && (float) $i['current_stock'] > 0));
        $outOfStock    = count(array_filter($inventory, fn($i) => (float) $i['current_stock'] <= 0));

        $data = [
            'title'       => 'Janitorial Monitoring',
            'pageCss'     => 'safety.css',
            'areas_json'      => $this->jsonForScript($areas),
            'checklists_json' => $this->jsonForScript($checklists),
            'staff_json'      => $this->jsonForScript(array_values($staff)),
            'inventory_json'  => $this->jsonForScript(array_map(fn($i) => [
                'id'         => $i['id'],
                'name'       => $i['item_name'],
                'cat'        => $i['category'],
                'unit'       => $i['unit'],
                'stock'      => (float) $i['current_stock'],
                'reorder'    => (float) $i['reorder_threshold'],
                'lastRefill' => $i['last_refill'],
            ], $inventory)),
            'refill_log_json' => $this->jsonForScript(array_map(fn($l) => [
                'item'      => $l['item_name'],
                'qty'       => (float) $l['quantity_added'],
                'unit'      => $l['unit'],
                'by'        => $l['performed_by'],
                'at'        => $l['performed_at'],
            ], $this->refillLogModel->getRecent(50))),
            'zone_total'   => $totalZones,
            'zone_cleaned' => $cleanedZones,
            'summary' => [
                'total_zones'   => $totalZones,
                'active_shifts' => count($staff),
                'cleaned_zones' => $cleanedZones,
                'pending_zones' => $pendingZones,
                'low_stock'     => $lowStock,
                'out_of_stock'  => $outOfStock,
            ],
            'flash_success' => $this->session->getFlashdata('success'),
            'flash_error'   => $this->session->getFlashdata('error'),
        ];

        return view('janitorial/index', $data);
    }

    // Same merge rule used for a whole zone (see index()), just scoped to
    // whichever assignments are passed in — reused per-floor so a
    // building's drill-down panel can show one floor's staff/shift/tasks
    // without duplicating the merge logic a second time.
    private function buildMergedChecklist(array $assignments, array $tasksByAssignment): array
    {
        $multiStaff = count($assignments) > 1;
        $tasks = [];
        $staffNames = [];
        $shiftLabels = [];

        foreach ($assignments as $a) {
            $myTasks = $tasksByAssignment[$a['id']] ?? [];
            $shift = date('gA', strtotime($a['shift_start'])) . '-' . date('gA', strtotime($a['shift_end']));
            $staffNames[] = $a['staff_name'];
            $shiftLabels[] = $shift;

            foreach ($myTasks as $t) {
                $tasks[] = [
                    't'    => $multiStaff ? "{$t['task_name']} ({$a['staff_name']})" : $t['task_name'],
                    'done' => (bool) $t['is_done'],
                    'time' => $t['completed_at'] ? date('H:i', strtotime($t['completed_at'])) : null,
                ];
            }
        }

        usort($tasks, fn($a, $b) => (int) $b['done'] <=> (int) $a['done']);

        return [
            'staff' => implode(' & ', $staffNames),
            'shift' => implode(' / ', array_unique($shiftLabels)),
            'tasks' => $tasks,
        ];
    }

    // "Ground Floor" always first, then numeric floors in order (2nd, 3rd,
    // …) — plain string sort would put "2nd Floor" after "Ground Floor"
    // but before "3rd Floor" is fine alphabetically, this just guarantees it.
    private function compareFloorNames(string $a, string $b): int
    {
        if ($a === $b) return 0;
        if ($a === 'Ground Floor') return -1;
        if ($b === 'Ground Floor') return 1;
        return ((int) $a) <=> ((int) $b);
    }

    public function checklists()
    {
        return $this->index();
    }

    /**
     * Daily maintenance tasks reset once finished so staff see a clean
     * checklist next shift — but pending/missed tasks must NOT reset; they
     * stay open until someone actually completes them. Only tasks marked
     * done on a previous day are touched, and each is archived into
     * janitorial_task_history first so activity history keeps the record.
     */
    private function resetStaleCompletedTasks(): void
    {
        $today = date('Y-m-d');

        $staleTasks = array_filter(
            $this->taskModel->where('is_done', 1)->findAll(),
            fn($t) => !empty($t['completed_at']) && substr($t['completed_at'], 0, 10) !== $today
        );

        if (empty($staleTasks)) return;

        $assignmentsById = [];
        foreach ($this->assignmentModel->findAll() as $a) {
            $assignmentsById[$a['id']] = $a;
        }

        $historyModel = new JanitorialTaskHistoryModel();

        foreach ($staleTasks as $t) {
            $a = $assignmentsById[$t['assignment_id']] ?? null;

            $historyModel->insert([
                'assignment_id' => $t['assignment_id'],
                'zone'          => $a['assigned_zone'] ?? 'Unknown',
                'task_name'     => $t['task_name'],
                'status'        => 'done',
                'completed_at'  => $t['completed_at'],
                'performed_by'  => $a['staff_name'] ?? null,
                'archived_at'   => date('Y-m-d H:i:s'),
            ]);

            $this->taskModel->update($t['id'], ['is_done' => 0, 'completed_at' => null]);
        }
    }

    public function refillInventory($id)
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $qty = (float) $this->request->getPost('quantity');
        $item = $this->inventoryModel->find($id);
        if (!$item || $qty <= 0) {
            return redirect()->to('/janitorial')->with('error', 'Invalid refill quantity.');
        }

        $this->inventoryModel->update($id, [
            'current_stock' => (float) $item['current_stock'] + $qty,
            'last_refill'   => date('Y-m-d'),
        ]);

        $this->refillLogModel->insert([
            'inventory_item_id' => $id,
            'item_name'         => $item['item_name'],
            'quantity_added'    => $qty,
            'unit'              => $item['unit'],
            'performed_by'      => (string) ($this->session->get('full_name') ?? $this->session->get('emp_id') ?? 'Unknown'),
            'performed_at'      => date('Y-m-d H:i:s'),
        ]);
        $this->logActivity('Janitorial', "Refilled {$item['item_name']} by {$qty} {$item['unit']}");

        return redirect()->to('/janitorial')->with('success', $item['item_name'] . ' refilled by ' . $qty . ' ' . $item['unit'] . '.');
    }

    public function addInventoryItem()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $name = trim((string) $this->request->getPost('item_name'));
        if ($name === '') {
            return redirect()->to('/janitorial')->with('error', 'Item name is required.');
        }

        $this->inventoryModel->insert([
            'item_name'         => $name,
            'category'          => $this->request->getPost('category') ?: 'Cleaning Agent',
            'unit'              => $this->request->getPost('unit') ?: 'Pieces',
            'current_stock'     => (float) $this->request->getPost('current_stock'),
            'reorder_threshold' => (float) $this->request->getPost('reorder_threshold'),
            'last_refill'       => date('Y-m-d'),
        ]);
        $this->logActivity('Janitorial', "Added inventory item {$name}");

        return redirect()->to('/janitorial')->with('success', 'Item added to inventory.');
    }

    public function assignStaff()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $staffName  = trim((string) $this->request->getPost('staff_name'));
        $zone       = trim((string) $this->request->getPost('assigned_zone'));
        $floor      = trim((string) $this->request->getPost('floor')) ?: 'Ground Floor';
        $priority   = $this->request->getPost('priority') === 'Urgent' ? 'Urgent' : 'Routine';
        $shiftStart = (string) $this->request->getPost('shift_start');
        $shiftEnd   = (string) $this->request->getPost('shift_end');

        if ($staffName === '' || $zone === '' || $shiftStart === '' || $shiftEnd === '') {
            return redirect()->to('/janitorial')->with('error', 'Staff name, zone, and shift times are all required.');
        }

        $assignmentId = $this->assignmentModel->insert([
            'staff_name'    => $staffName,
            'assigned_zone' => $zone,
            'floor'         => $floor,
            'shift_start'   => $shiftStart,
            'shift_end'     => $shiftEnd,
            'date_assigned' => date('Y-m-d'),
            'status'        => 'Active',
            'priority'      => $priority,
        ], true);

        // Each line becomes one checklist task for this assignment — without
        // this, a brand-new shift would have 0 tasks and show as "0 of 0"
        // (NaN%) on the Active Shifts card instead of a real pending count.
        $taskNames = array_filter(array_map('trim', explode("\n", (string) $this->request->getPost('tasks'))));
        foreach ($taskNames as $taskName) {
            $this->taskModel->insert([
                'assignment_id' => $assignmentId,
                'task_name'     => ($priority === 'Urgent' ? 'URGENT: ' : '') . $taskName,
                'is_done'       => 0,
            ]);
        }

        // Same "Urgent Cleaning Scheduled" notification category the
        // Calendar's urgent-cleaning shortcut already uses, so an urgent
        // zone assigned here shows up the same way everywhere else in the
        // system that reads notifications.
        if ($priority === 'Urgent') {
            (new NotificationModel())->insert([
                'category'    => 'Urgent Cleaning Scheduled',
                'description' => "Urgent cleaning assigned to {$staffName} for {$zone} ({$floor}) — needs cleaning ASAP.",
                'recipient'   => $staffName,
                'priority'    => 'CRITICAL',
                'status'      => 'Pending',
                'is_read'     => 0,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        $this->logActivity('Janitorial', "Assigned {$staffName} to {$zone} ({$floor})" . ($priority === 'Urgent' ? ' — URGENT' : ''));

        return redirect()->to('/janitorial')->with('success', $staffName . ' assigned to ' . $zone . ' (' . $floor . ').');
    }

    public function updateAssignment($id)
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $assignment = $this->assignmentModel->find($id);
        if (!$assignment) {
            return redirect()->to('/janitorial')->with('error', 'Shift assignment not found.');
        }

        $staffName  = trim((string) $this->request->getPost('staff_name'));
        $zone       = trim((string) $this->request->getPost('assigned_zone'));
        $floor      = trim((string) $this->request->getPost('floor')) ?: 'Ground Floor';
        $priority   = $this->request->getPost('priority') === 'Urgent' ? 'Urgent' : 'Routine';
        $shiftStart = (string) $this->request->getPost('shift_start');
        $shiftEnd   = (string) $this->request->getPost('shift_end');

        if ($staffName === '' || $zone === '' || $shiftStart === '' || $shiftEnd === '') {
            return redirect()->to('/janitorial')->with('error', 'Staff name, zone, and shift times are all required.');
        }

        $wasUrgent = ($assignment['priority'] ?? 'Routine') === 'Urgent';

        $this->assignmentModel->update($id, [
            'staff_name'    => $staffName,
            'assigned_zone' => $zone,
            'floor'         => $floor,
            'shift_start'   => $shiftStart,
            'shift_end'     => $shiftEnd,
            'priority'      => $priority,
        ]);

        if ($priority === 'Urgent' && !$wasUrgent) {
            (new NotificationModel())->insert([
                'category'    => 'Urgent Cleaning Scheduled',
                'description' => "Shift for {$staffName} at {$zone} ({$floor}) was marked urgent — needs cleaning ASAP.",
                'recipient'   => $staffName,
                'priority'    => 'CRITICAL',
                'status'      => 'Pending',
                'is_read'     => 0,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        $this->logActivity('Janitorial', "Updated {$staffName}'s assignment at {$zone} ({$floor})" . ($priority === 'Urgent' ? ' — URGENT' : ''));

        return redirect()->to('/janitorial')->with('success', 'Shift assignment updated.');
    }

    public function deleteAssignment($id)
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $assignment = $this->assignmentModel->find($id);
        if (!$assignment) {
            return redirect()->to('/janitorial')->with('error', 'Shift assignment not found.');
        }

        // No FK cascade on janitorial_tasks, so its rows are removed
        // explicitly first — otherwise they'd be orphaned (still pointing
        // at an assignment_id that no longer exists).
        $this->taskModel->where('assignment_id', $id)->delete();
        $this->assignmentModel->delete($id);

        $this->logActivity('Janitorial', "Removed {$assignment['staff_name']}'s assignment at {$assignment['assigned_zone']}");

        return redirect()->to('/janitorial')->with('success', 'Shift assignment removed.');
    }
}
