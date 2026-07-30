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

        // Build the zone map (AREAS) and per-zone checklists from the SAME assignment rows,
        // so the "Total Zones" stat and the "X/Y cleaned" stat can never disagree again.
        $areas = [];
        $checklists = [];
        $staff = [];

        foreach ($assignments as $a) {
            $slug = self::ZONE_SLUGS[$a['assigned_zone']] ?? strtolower(preg_replace('/[^a-z0-9]+/', '-', $a['assigned_zone']));
            $tasks = $tasksByAssignment[$a['id']] ?? [];
            $done  = count(array_filter($tasks, fn($t) => (int) $t['is_done'] === 1));
            $total = count($tasks);

            $areas[$slug] = ['name' => $a['assigned_zone']];

            $checklists[$slug] = [
                'staff' => $a['staff_name'],
                'shift' => date('gA', strtotime($a['shift_start'])) . '-' . date('gA', strtotime($a['shift_end'])),
                'tasks' => array_map(fn($t) => [
                    't'    => $t['task_name'],
                    'done' => (bool) $t['is_done'],
                    'time' => $t['completed_at'] ? date('H:i', strtotime($t['completed_at'])) : null,
                ], $tasks),
            ];

            $staff[] = [
                'name'  => $a['staff_name'],
                'zone'  => $a['assigned_zone'],
                'tasks' => $total,
                'done'  => $done,
                'photo' => strtoupper(substr($a['staff_name'], 0, 1)),
                'shift' => $checklists[$slug]['shift'],
                'area'  => $slug,
            ];
        }

        $inventory = $this->inventoryModel->findAll();

        $totalZones    = count($areas);
        $cleanedZones  = count(array_filter($staff, fn($s) => $s['tasks'] > 0 && $s['done'] === $s['tasks']));
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
