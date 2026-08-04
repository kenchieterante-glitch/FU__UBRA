<?php

namespace App\Controllers;

use App\Models\JanitorialAssignmentModel;
use App\Models\JanitorialTaskModel;
use App\Models\ConsumableInventoryModel;

class JanitorialController extends BaseController
{
    protected $session;
    protected $assignmentModel;
    protected $taskModel;
    protected $inventoryModel;

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
    }

    public function index()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

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

            foreach ($zoneAssignments as $a) {
                $tasks = $tasksByAssignment[$a['id']] ?? [];
                $done  = count(array_filter($tasks, fn($t) => (int) $t['is_done'] === 1));
                $total = count($tasks);
                $shift = date('gA', strtotime($a['shift_start'])) . '-' . date('gA', strtotime($a['shift_end']));

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
                    'name'  => $a['staff_name'],
                    'zone'  => $zoneName,
                    'tasks' => $total,
                    'done'  => $done,
                    'photo' => strtoupper(substr($a['staff_name'], 0, 1)),
                    'shift' => $shift,
                    'area'  => $slug,
                ];
            }

            $checklists[$slug] = [
                'staff' => implode(' & ', $staffNames),
                'shift' => implode(' / ', array_unique($shiftLabels)),
                'tasks' => $mergedTasks,
            ];
        }

        $inventory = $this->inventoryModel->findAll();

        // A zone counts as cleaned only when every task across every
        // assignment mapped to it is done — read straight from the same
        // merged checklist the map and drill-down panel use.
        $totalZones   = count($checklists);
        $cleanedZones = count(array_filter($checklists, function ($c) {
            if (empty($c['tasks'])) return false;
            foreach ($c['tasks'] as $t) {
                if (!$t['done']) return false;
            }
            return true;
        }));
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

    public function checklists()
    {
        return $this->index();
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

        return redirect()->to('/janitorial')->with('success', 'Item added to inventory.');
    }
}
