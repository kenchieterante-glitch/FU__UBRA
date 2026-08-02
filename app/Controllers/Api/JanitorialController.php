<?php

namespace App\Controllers\Api;

use App\Libraries\ApiAuth;
use App\Models\ConsumableInventoryModel;
use App\Models\JanitorialAssignmentModel;
use App\Models\JanitorialTaskModel;

class JanitorialController extends BaseApiController
{
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

    protected JanitorialAssignmentModel $assignmentModel;
    protected JanitorialTaskModel $taskModel;
    protected ConsumableInventoryModel $inventoryModel;

    public function __construct()
    {
        $this->assignmentModel = new JanitorialAssignmentModel();
        $this->taskModel       = new JanitorialTaskModel();
        $this->inventoryModel  = new ConsumableInventoryModel();
    }

    public function index()
    {
        $assignments = $this->assignmentModel->findAll();
        $tasks       = $this->taskModel->findAll();
        $inventory   = $this->inventoryModel->findAll();

        $tasksByAssignment = [];
        foreach ($tasks as $t) {
            $tasksByAssignment[$t['assignment_id']][] = $t;
        }

        $staff = [];
        $cleanedZones = 0;
        $pendingZones = 0;

        foreach ($assignments as $a) {
            $myTasks = $tasksByAssignment[$a['id']] ?? [];
            $done    = count(array_filter($myTasks, fn ($t) => (int) $t['is_done'] === 1));
            $total   = count($myTasks);
            if ($total > 0 && $done === $total) {
                $cleanedZones++;
            } else {
                $pendingZones++;
            }

            $staff[] = [
                'id'    => $a['id'],
                'name'  => $a['staff_name'],
                'zone'  => $a['assigned_zone'],
                'area'  => self::ZONE_SLUGS[$a['assigned_zone']] ?? null,
                'shift' => trim(($a['shift_start'] ?? '') . ' - ' . ($a['shift_end'] ?? '')),
                'status'=> $a['status'],
                'tasks' => $myTasks,
                'done'  => $done,
                'total' => $total,
            ];
        }

        $lowStock    = array_values(array_filter($inventory, fn ($i) => $i['current_stock'] > 0 && $i['current_stock'] <= $i['reorder_threshold']));
        $outOfStock  = array_values(array_filter($inventory, fn ($i) => $i['current_stock'] <= 0));

        return $this->ok([
            'areas'     => self::ZONE_SLUGS,
            'staff'     => $staff,
            'inventory' => $inventory,
            'summary'   => [
                'total_zones'    => count(self::ZONE_SLUGS),
                'active_shifts'  => count(array_filter($assignments, fn ($a) => $a['status'] === 'Active')),
                'cleaned_zones'  => $cleanedZones,
                'pending_zones'  => $pendingZones,
                'low_stock'      => count($lowStock),
                'out_of_stock'   => count($outOfStock),
            ],
        ]);
    }

    public function checklists()
    {
        return $this->index();
    }

    // GET /api/janitorial/my — assignments matching the logged-in user's name.
    // janitorial_assignments only stores a free-text staff_name (no personnel/user
    // link exists in the schema), so this is a best-effort case-insensitive match.
    public function my()
    {
        $fullName = trim((string) (ApiAuth::user()['full_name'] ?? ''));

        $assignments = $fullName !== ''
            ? array_values(array_filter(
                $this->assignmentModel->findAll(),
                fn ($a) => strcasecmp(trim($a['staff_name']), $fullName) === 0
            ))
            : [];

        if ($assignments === []) {
            return $this->ok([
                'assignments' => [],
                'linked'      => false,
                'message'     => 'No janitorial assignment found under your name. Contact your supervisor if this is unexpected.',
            ]);
        }

        $result = [];
        foreach ($assignments as $a) {
            $tasks = $this->taskModel->getForAssignment((int) $a['id']);
            $result[] = [
                'id'    => $a['id'],
                'zone'  => $a['assigned_zone'],
                'area'  => self::ZONE_SLUGS[$a['assigned_zone']] ?? null,
                'shift' => trim(($a['shift_start'] ?? '') . ' - ' . ($a['shift_end'] ?? '')),
                'status'=> $a['status'],
                'date'  => $a['date_assigned'],
                'tasks' => $tasks,
            ];
        }

        return $this->ok(['assignments' => $result, 'linked' => true]);
    }

    // POST /api/janitorial/tasks/(:num)/toggle
    public function toggleTask($id = null)
    {
        $task = $this->taskModel->find($id);
        if (!$task) {
            return $this->fail('Task not found.', 404);
        }

        $nowDone = !((int) $task['is_done'] === 1);

        $this->taskModel->update($id, [
            'is_done'      => $nowDone ? 1 : 0,
            'completed_at' => $nowDone ? date('Y-m-d H:i:s') : null,
        ]);

        return $this->ok(['is_done' => $nowDone]);
    }

    public function refillInventory($id = null)
    {
        $qty = (float) $this->request->getPost('quantity');
        if ($qty <= 0) {
            return $this->fail('Quantity must be greater than zero.', 422);
        }

        $item = $this->inventoryModel->find($id);
        if (!$item) {
            return $this->fail('Inventory item not found.', 404);
        }

        $this->inventoryModel->update($id, [
            'current_stock' => $item['current_stock'] + $qty,
            'last_refill'   => date('Y-m-d'),
        ]);

        return $this->ok();
    }

    public function addInventoryItem()
    {
        $name = trim((string) $this->request->getPost('item_name'));
        if ($name === '') {
            return $this->fail('Item name is required.', 422);
        }

        $id = $this->inventoryModel->insert([
            'item_name'         => $name,
            'category'          => $this->request->getPost('category') ?: 'Cleaning Agent',
            'unit'              => $this->request->getPost('unit') ?: 'Pieces',
            'current_stock'     => $this->request->getPost('current_stock') ?: 0,
            'reorder_threshold' => $this->request->getPost('reorder_threshold') ?: 5,
            'last_refill'       => date('Y-m-d'),
        ], true);

        return $this->ok(['id' => $id], 201);
    }
}
