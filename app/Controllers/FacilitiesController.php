<?php

namespace App\Controllers;

use App\Models\AirconChecklistItemModel;
use App\Models\AirconUnitModel;
use App\Models\ConsumableInventoryModel;
use App\Models\FireExtinguisherModel;
use App\Models\JanitorialAssignmentModel;
use App\Models\JanitorialInspectionModel;
use App\Models\JanitorialTaskModel;
use App\Models\WorkOrderModel;

class FacilitiesController extends BaseController
{
    private const SECTIONS = [
        'work-orders' => 'Repair Requests',
        'aircon'      => 'Aircon Care',
        'janitorial'  => 'Janitorial Check',
        'buildings'   => 'Building Check',
    ];

    public function overview()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $db = db_connect();
        $soon = date('Y-m-d', strtotime('+7 days'));
        $month = date('Y-m');

        $workOrders = new WorkOrderModel();
        $airconAlerts = (new AirconUnitModel())->where('next_schedule <=', $soon)->countAllResults();
        $tasks = new JanitorialTaskModel();

        $stats = [
            ['key' => 'open', 'label' => 'Open Repair Requests', 'value' => $workOrders->where('status !=', 'Completed')->countAllResults(), 'icon' => 'bi-hourglass-split', 'tone' => 'gold'],
            ['key' => 'urgent', 'label' => 'Urgent Requests', 'value' => $workOrders->where('status !=', 'Completed')->where('priority', 'Urgent')->countAllResults(), 'icon' => 'bi-exclamation-triangle-fill', 'tone' => 'red'],
            ['key' => 'overdue', 'label' => 'Aircon Due or Overdue', 'value' => $airconAlerts, 'icon' => 'bi-snow2', 'tone' => 'amber'],
            ['key' => 'tasks', 'label' => 'Cleaning Tasks Done', 'value' => $tasks->where('is_done', 1)->countAllResults() . ' / ' . $tasks->countAllResults(), 'icon' => 'bi-brush', 'tone' => 'green'],
            ['key' => 'supplies', 'label' => 'Supplies Out of Stock', 'value' => (new ConsumableInventoryModel())->where('department', 'Facilities')->where('current_stock <= 0', null, false)->countAllResults(), 'icon' => 'bi-box-seam-fill', 'tone' => 'red'],
            ['key' => 'checked', 'label' => 'Buildings Checked This Month', 'value' => (int) $db->table('janitorial_inspections')->select('COUNT(DISTINCT building) AS n')->where('inspection_month', $month)->get()->getRow()->n . ' / ' . count(FireExtinguisherModel::BUILDINGS), 'icon' => 'bi-calendar-check', 'tone' => 'maroon'],
            ['key' => 'attention', 'label' => 'Checks Needing Attention', 'value' => (new JanitorialInspectionModel())->where('inspection_month', $month)->where('result', 'Needs Attention')->countAllResults(), 'icon' => 'bi-clipboard2-x', 'tone' => 'red'],
        ];

        $details = [];
        foreach ($this->statusCards() as $card) $details[$card['key']] = ['title' => $card['title'], 'columns' => $card['columns'], 'rows' => $card['rows']];
        // "Checks Needing Attention" counts buildings (their latest check), so it matches the records shown under it.
        foreach ($stats as &$st) { if ($st['key'] === 'attention') $st['value'] = count($details['attention']['rows']); }
        unset($st);

        return view('facilities/dashboard', [
            'title'   => strtolower((string) session()->get('role')) === 'facilities' ? 'Facilities Administration and General Services' : 'Unified Buildings Resources Administration',
            'pageCss' => 'safety.css',
            'stats'   => $stats,
            'details' => $details,
            'sections' => [
                ['label' => 'Repair Requests', 'url' => 'facilities/work-orders', 'icon' => 'bi-clipboard2-check', 'desc' => 'Ask for repairs and see what is waiting or finished.'],
                ['label' => 'Aircon Care', 'url' => 'facilities/aircon', 'icon' => 'bi-snow2', 'desc' => 'Check each aircon unit and when it needs care.'],
                ['label' => 'Janitorial Check', 'url' => 'facilities/janitorial', 'icon' => 'bi-brush', 'desc' => 'Daily cleaning checks, all zones, and supplies.'],
                ['label' => 'Building Check', 'url' => 'facilities/buildings', 'icon' => 'bi-building-check', 'desc' => 'Monthly building checks and the building map.'],
            ],
        ]);
    }

    public function status()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $cards = $this->statusCards();

        return view('facilities/status', [
            'title'   => 'Facilities Status',
            'pageCss' => 'safety.css',
            'cards'   => $cards,
        ]);
    }

    // Every status box with the records behind it — used by the status page and by the dashboard boxes.
    private function statusCards(): array
    {
        $today = date('Y-m-d');
        $soon = date('Y-m-d', strtotime('+7 days'));
        $month = date('Y-m');
        $buildingTotal = count(FireExtinguisherModel::BUILDINGS);

        $schedule = function ($next) use ($today, $soon) {
            if (empty($next)) return 'No Date';
            if ($next < $today) return 'Overdue';
            if ($next <= $soon) return 'Due Soon';
            return 'Scheduled';
        };

        $workOrders = (new WorkOrderModel())->orderBy('id', 'DESC')->findAll();
        $woRow = fn($w) => ['WO-' . str_pad((string) $w['id'], 4, '0', STR_PAD_LEFT), $w['title'], $w['building'], $w['priority'], $w['requested_by'], $w['status']];
        $woCols = ['No.', 'Request', 'Building', 'Priority', 'Requested By', 'Status'];
        $open = array_values(array_filter($workOrders, fn($w) => $w['status'] !== 'Completed'));
        $urgent = array_values(array_filter($open, fn($w) => $w['priority'] === 'Urgent'));
        $finished = array_values(array_filter($workOrders, fn($w) => $w['status'] === 'Completed'));

        $units = (new AirconUnitModel())->orderBy('location', 'ASC')->findAll();
        $unitRow = fn($u) => [$u['location'], $u['floor'], $u['unit_name'], $u['condition_status'] ?? 'Operational', !empty($u['next_schedule']) ? date('M d, Y', strtotime($u['next_schedule'])) : '—', (fn($st) => $st === 'Due Soon' ? 'Overdue in 7 Days' : $st)($schedule($u['next_schedule']))];
        $unitCols = ['Building', 'Floor', 'Unit', 'Condition', 'Next Schedule', 'Schedule Status'];
        $overdueUnits = array_values(array_filter($units, fn($u) => $schedule($u['next_schedule']) === 'Overdue'));
        $soonUnits = array_values(array_filter($units, fn($u) => $schedule($u['next_schedule']) === 'Due Soon'));
        $notWorking = array_values(array_filter($units, fn($u) => ($u['condition_status'] ?? '') === 'Not Working'));

        $assignments = (new JanitorialAssignmentModel())->orderBy('assigned_zone', 'ASC')->findAll();
        $tasksBy = [];
        $allTasks = [];
        foreach ((new JanitorialTaskModel())->findAll() as $t) {
            $tasksBy[(int) $t['assignment_id']][] = $t;
            $allTasks[] = $t;
        }
        $zoneRows = [];
        $doneZoneRows = [];
        foreach ($assignments as $a) {
            $list = $tasksBy[(int) $a['id']] ?? [];
            $done = count(array_filter($list, fn($t) => (int) $t['is_done'] === 1));
            $total = count($list);
            $row = [$a['assigned_zone'], $a['floor'], $a['staff_name'], $done . ' / ' . $total, $total === 0 ? 'No Tasks' : ($done === $total ? 'Completed' : ($done > 0 ? 'In Progress' : 'Pending'))];
            $zoneRows[] = $row;
            if ($total > 0 && $done === $total) $doneZoneRows[] = $row;
        }
        $zoneCols = ['Zone', 'Floor', 'Staff', 'Tasks Done', 'Progress'];
        $taskDone = count(array_filter($allTasks, fn($t) => (int) $t['is_done'] === 1));

        $supplies = (new ConsumableInventoryModel())->where('department', 'Facilities')->orderBy('item_name', 'ASC')->findAll();
        $outSupplies = array_values(array_filter($supplies, fn($c) => (float) $c['current_stock'] <= 0));
        $supplyRow = fn($c) => [$c['item_name'], $c['category'], trim(($c['building'] ?? '') . (!empty($c['floor']) ? ', ' . $c['floor'] : ''), ', ') ?: '—', (string) (float) $c['current_stock'] . ' ' . $c['unit']];
        $supplyCols = ['Item', 'Category', 'Location', 'Stock'];

        $checks = (new JanitorialInspectionModel())->where('inspection_month', $month)->orderBy('inspected_at', 'DESC')->findAll();
        $latest = [];
        foreach ($checks as $c) {
            $latest[$c['building']] ??= $c;
        }
        $checkRow = fn($c) => [$c['building'], $c['result'], $c['inspected_by'], date('M d, Y', strtotime($c['inspected_at']))];
        $checkCols = ['Building', 'Result', 'Checked By', 'Date'];
        $checked = array_values($latest);
        $attention = array_values(array_filter($checked, fn($c) => $c['result'] === 'Needs Attention'));
        $notChecked = array_map(fn($b) => [$b], array_values(array_filter(FireExtinguisherModel::BUILDINGS, fn($b) => !isset($latest[$b]))));

        $cards = [
            ['key' => 'open', 'label' => 'Open Requests', 'value' => count($open), 'icon' => 'bi-hourglass-split', 'tone' => 'gold', 'title' => 'Open Repair Requests', 'columns' => $woCols, 'rows' => array_map($woRow, $open)],
            ['key' => 'urgent', 'label' => 'Urgent', 'value' => count($urgent), 'icon' => 'bi-exclamation-triangle-fill', 'tone' => 'red', 'title' => 'Urgent Repair Requests', 'columns' => $woCols, 'rows' => array_map($woRow, $urgent)],
            ['key' => 'finished', 'label' => 'Finished', 'value' => count($finished), 'icon' => 'bi-check-circle-fill', 'tone' => 'green', 'title' => 'Finished Repair Requests', 'columns' => $woCols, 'rows' => array_map($woRow, $finished)],
            ['key' => 'units', 'label' => 'Aircon Units', 'value' => count($units), 'icon' => 'bi-snow2', 'tone' => 'blue', 'title' => 'All Aircon Units', 'columns' => $unitCols, 'rows' => array_map($unitRow, $units)],
            ['key' => 'overdue', 'label' => 'Overdue & In 7 Days', 'value' => count($overdueUnits) + count($soonUnits), 'icon' => 'bi-calendar-x', 'tone' => 'red', 'title' => 'Aircon Units Overdue or Overdue in 7 Days', 'columns' => $unitCols, 'rows' => array_merge(array_map($unitRow, $overdueUnits), array_map($unitRow, $soonUnits))],
            ['key' => 'broken', 'label' => 'Not Working', 'value' => count($notWorking), 'icon' => 'bi-x-octagon-fill', 'tone' => 'red', 'title' => 'Aircon Units Not Working', 'columns' => $unitCols, 'rows' => array_map($unitRow, $notWorking)],
            ['key' => 'zones', 'label' => 'Zones Completed', 'value' => count($doneZoneRows) . ' / ' . count($assignments), 'icon' => 'bi-check2-square', 'tone' => 'green', 'title' => 'Completed Cleaning Zones', 'columns' => $zoneCols, 'rows' => $doneZoneRows],
            ['key' => 'tasks', 'label' => 'Tasks Done', 'value' => $taskDone . ' / ' . count($allTasks), 'icon' => 'bi-brush', 'tone' => 'maroon', 'title' => 'Cleaning Zones and Tasks', 'columns' => $zoneCols, 'rows' => $zoneRows],
            ['key' => 'supplies', 'label' => 'Supplies Out', 'value' => count($outSupplies), 'icon' => 'bi-box-seam', 'tone' => 'red', 'title' => 'Supplies Out of Stock', 'columns' => $supplyCols, 'rows' => array_map($supplyRow, $outSupplies)],
            ['key' => 'checked', 'label' => 'Checked This Month', 'value' => count($checked) . ' / ' . $buildingTotal, 'icon' => 'bi-calendar-check', 'tone' => 'green', 'title' => 'Buildings Checked This Month', 'columns' => $checkCols, 'rows' => array_map($checkRow, $checked)],
            ['key' => 'attention', 'label' => 'Needs Attention', 'value' => count($attention), 'icon' => 'bi-exclamation-circle-fill', 'tone' => 'red', 'title' => 'Buildings Needing Attention', 'columns' => $checkCols, 'rows' => array_map($checkRow, $attention)],
            ['key' => 'notchecked', 'label' => 'Not Checked Yet', 'value' => $buildingTotal - count($checked), 'icon' => 'bi-hourglass', 'tone' => 'gold', 'title' => 'Buildings Not Checked This Month', 'columns' => ['Building'], 'rows' => $notChecked],
        ];

        return $cards;
    }

    public function index(string $section = 'work-orders')
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');
        if (!isset(self::SECTIONS[$section])) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $workOrders = new WorkOrderModel();
        $all = $workOrders->orderBy('id', 'DESC')->findAll();

        $pending = array_values(array_filter($all, fn($w) => $w['status'] !== 'Completed'));
        $history = array_values(array_filter($all, fn($w) => $w['status'] === 'Completed'));

        $buildings = array_map(function ($b) use ($all) {
            $open = array_filter($all, fn($w) => $w['building'] === $b && $w['status'] !== 'Completed');
            $lastDone = array_values(array_filter($all, fn($w) => $w['building'] === $b && $w['status'] === 'Completed'));
            return [
                'name'      => $b,
                'open'      => count($open),
                'last_done' => $lastDone[0]['completed_at'] ?? null,
            ];
        }, FireExtinguisherModel::BUILDINGS);

        $today = date('Y-m-d');
        $soon = date('Y-m-d', strtotime('+7 days'));
        $checklistModel = new AirconChecklistItemModel();

        $aircon = array_map(function ($u) use ($today, $soon, $checklistModel) {
            $tasks = $checklistModel->getForUnit((int) $u['id']);
            $done = count(array_filter($tasks, fn($t) => (int) $t['is_done'] === 1));

            if (empty($u['next_schedule'])) {
                $schedule = 'No Date';
            } elseif ($u['next_schedule'] < $today) {
                $schedule = 'Overdue';
            } elseif ($u['next_schedule'] <= $soon) {
                $schedule = 'Due Soon';
            } else {
                $schedule = 'Scheduled';
            }

            return [
                'id'           => (int) $u['id'],
                'building'     => $u['location'],
                'floor'        => $u['floor'],
                'unit'         => $u['unit_name'],
                'condition'    => $u['condition_status'] ?? 'Operational',
                'last_cleaning' => $u['last_cleaning'],
                'next_schedule' => $u['next_schedule'],
                'schedule'     => $schedule,
                'tech'         => $u['assigned_tech'],
                'installed_by' => $u['installed_by'],
                'tasks'        => array_map(fn($t) => ['task' => $t['task_name'], 'done' => (bool) $t['is_done']], $tasks),
                'tasks_done'   => $done,
                'tasks_total'  => count($tasks),
            ];
        }, (new AirconUnitModel())->orderBy('location', 'ASC')->orderBy('unit_name', 'ASC')->findAll());

        $aircon_alert = count(array_filter($aircon, fn($u) => in_array($u['schedule'], ['Overdue', 'Due Soon'], true)));

        $assignments = (new JanitorialAssignmentModel())->orderBy('shift_start', 'ASC')->orderBy('assigned_zone', 'ASC')->findAll();
        $tasksByAssignment = [];
        foreach ((new JanitorialTaskModel())->findAll() as $t) {
            $tasksByAssignment[(int) $t['assignment_id']][] = $t;
        }

        $zones = array_map(function ($a) use ($tasksByAssignment) {
            $tasks = $tasksByAssignment[(int) $a['id']] ?? [];
            $done = count(array_filter($tasks, fn($t) => (int) $t['is_done'] === 1));
            $total = count($tasks);

            if ($total === 0) {
                $progress = 'No Tasks';
            } elseif ($done === $total) {
                $progress = 'Completed';
            } elseif ($done > 0) {
                $progress = 'In Progress';
            } else {
                $progress = 'Pending';
            }

            return [
                'id'        => (int) $a['id'],
                'staff'     => $a['staff_name'],
                'zone'      => $a['assigned_zone'],
                'floor'     => $a['floor'],
                'shift'     => date('gA', strtotime($a['shift_start'])) . ' - ' . date('gA', strtotime($a['shift_end'])),
                'session'   => ((int) date('G', strtotime($a['shift_start']))) < 12 ? 'Morning' : 'Afternoon',
                'priority'  => $a['priority'],
                'status'    => $a['status'],
                'done'      => $done,
                'total'     => $total,
                'last'      => (function () use ($tasks) { $dates = array_filter(array_column($tasks, 'completed_at')); return $dates ? max($dates) : ''; })(),
                'progress'  => $progress,
                'badge'     => ($progress === 'Completed') ? ['Completed', 'zb-done'] : ((strtotime($a['shift_end']) > strtotime($a['shift_start']) && date('H:i:s') > $a['shift_end']) ? ['Overdue', 'zb-overdue'] : [$progress === 'In Progress' ? 'In Progress' : 'Needs Cleaning', 'zb-needs']),
                'tasks'     => array_map(fn($t) => ['task' => $t['task_name'], 'done' => (bool) $t['is_done']], $tasks),
            ];
        }, $assignments);

        $month = date('Y-m');
        $inspectionRows = (new JanitorialInspectionModel())->where('inspection_month', $month)->orderBy('inspected_at', 'DESC')->findAll();
        $latestInspection = [];
        foreach ($inspectionRows as $r) {
            $latestInspection[$r['building']] ??= $r;
        }
        // Latest check this month for each building + floor (only checks where a floor was recorded).
        $floorChecks = [];
        foreach ($inspectionRows as $r) {
            if (!empty($r['floor'])) $floorChecks[$r['building']][$r['floor']] ??= ['result' => $r['result'], 'by' => $r['inspected_by'], 'at' => $r['inspected_at'], 'notes' => $r['notes']];
        }

        $inspections = array_map(fn($b) => [
            'building'    => $b,
            'result'      => $latestInspection[$b]['result'] ?? null,
            'inspected_by'=> $latestInspection[$b]['inspected_by'] ?? null,
            'inspected_at'=> $latestInspection[$b]['inspected_at'] ?? null,
            'notes'       => $latestInspection[$b]['notes'] ?? null,
            'floor'       => $latestInspection[$b]['floor'] ?? null,
        ], FireExtinguisherModel::BUILDINGS);

        $consumables = (new ConsumableInventoryModel())->where('department', 'Facilities')->orderBy('category', 'ASC')->orderBy('item_name', 'ASC')->findAll();
        $consumableGroups = [];
        foreach ($consumables as $c) {
            $stock = (float) $c['current_stock'];
            $consumableGroups[$c['category']][] = [
                'name'     => $c['item_name'],
                'unit'     => $c['unit'],
                'stock'    => $stock,
                'building' => $c['building'],
                'floor'    => $c['floor'],
                'place'    => $c['location_note'],
                'status'   => $stock <= 0 ? 'Out of Stock' : 'Good',
            ];
        }

        $status = match ($section) {
            'work-orders' => [
                ['key' => 'wo_open', 'label' => 'Open Requests', 'value' => count($pending), 'icon' => 'bi-hourglass-split', 'tone' => 'gold'],
                ['key' => 'wo_urgent', 'label' => 'Urgent', 'value' => count(array_filter($pending, fn($w) => $w['priority'] === 'Urgent')), 'icon' => 'bi-exclamation-triangle-fill', 'tone' => 'red'],
                ['key' => 'wo_done', 'label' => 'Finished', 'value' => count($history), 'icon' => 'bi-check-circle-fill', 'tone' => 'green'],
            ],
            'aircon' => [
                ['key' => 'ac_units', 'label' => 'Aircon Units', 'value' => count($aircon), 'icon' => 'bi-snow2', 'tone' => 'blue'],
                ['key' => 'ac_alert', 'label' => 'Overdue & In 7 Days', 'value' => count(array_filter($aircon, fn($u) => in_array($u['schedule'], ['Overdue', 'Due Soon'], true))), 'icon' => 'bi-calendar-x', 'tone' => 'red'],
                ['key' => 'ac_broken', 'label' => 'Not Working', 'value' => count(array_filter($aircon, fn($u) => $u['condition'] === 'Not Working')), 'icon' => 'bi-x-octagon-fill', 'tone' => 'red'],
            ],
            'janitorial' => [
                ['key' => 'zones', 'label' => 'Zones Completed', 'value' => count(array_filter($zones, fn($z) => $z['progress'] === 'Completed')) . ' / ' . count($zones), 'icon' => 'bi-check2-square', 'tone' => 'green'],
                ['key' => 'tasks', 'label' => 'Tasks Done', 'value' => array_sum(array_column($zones, 'done')) . ' / ' . array_sum(array_column($zones, 'total')), 'icon' => 'bi-brush', 'tone' => 'maroon'],
                ['key' => 'supplies', 'label' => 'Supplies Out of Stock', 'value' => count(array_filter($consumables, fn($c) => (float) $c['current_stock'] <= 0)), 'icon' => 'bi-box-seam', 'tone' => 'red'],
            ],
            'buildings' => [
                ['key' => 'checked', 'label' => 'Checked This Month', 'value' => count(array_filter($inspections, fn($i) => $i['result'] !== null)) . ' / ' . count(FireExtinguisherModel::BUILDINGS), 'icon' => 'bi-calendar-check', 'tone' => 'green'],
                ['key' => 'attention', 'label' => 'Needs Attention', 'value' => count(array_filter($inspections, fn($i) => $i['result'] === 'Needs Attention')), 'icon' => 'bi-exclamation-circle-fill', 'tone' => 'red'],
                ['key' => 'notchecked', 'label' => 'Not Checked Yet', 'value' => count(array_filter($inspections, fn($i) => $i['result'] === null)), 'icon' => 'bi-hourglass', 'tone' => 'gold'],
            ],
        };

        $acAlerts = [];
        foreach ($aircon as $u) {
            if ($u['schedule'] === 'Overdue' || $u['schedule'] === 'Due Soon') {
                $acAlerts[] = ['cols' => [$u['building'], $u['floor'], $u['unit'], !empty($u['next_schedule']) ? date('M d, Y', strtotime($u['next_schedule'])) : '—', $u['schedule'] === 'Due Soon' ? 'Overdue in 7 Days' : 'Overdue'], 'level' => $u['schedule'] === 'Due Soon' ? 'circle' : 'red'];
            }
        }
        $cleanAlerts = [];
        foreach ($zones as $z) {
            if ($z['badge'][1] !== 'zb-done') {
                $cleanAlerts[] = ['cols' => [$z['zone'], $z['floor'], $z['staff'], $z['shift'], $z['badge'][0]], 'level' => $z['badge'][1] === 'zb-overdue' ? 'red' : 'yellow'];
            }
        }
        $bldAlerts = [];
        foreach ($inspections as $i) {
            if ($i['result'] !== 'Passed') {
                $bldAlerts[] = ['cols' => [$i['building'], $i['result'] ?? 'Not checked', $i['inspected_by'] ?? '—'], 'level' => $i['result'] === null ? 'yellow' : 'red'];
            }
        }
        $zoneToBuilding = [
            'Admin Building' => 'Administration Building',
            'Library' => 'University Library',
            'Engineering' => 'Old College of Industrial Engineering and Technology',
            'Science Building' => 'College of Art & Sciences Building',
            'Canteen' => 'University Cafeteria, Bookstore, Sewing',
            'CCS Building' => 'LG Sinco Computer Center Building',
        ];
        $assignEnd = [];
        foreach ($assignments as $a) {
            $assignEnd[(int) $a['id']] = [$a['shift_start'], $a['shift_end']];
        }
        $nowTime = date('H:i:s');
        $cleanState = [];
        foreach ($zones as $z) {
            $building = $zoneToBuilding[$z['zone']] ?? null;
            if ($building === null) continue;
            [$start, $end] = $assignEnd[$z['id']] ?? ['00:00:00', '23:59:59'];
            $cleanState[$building] ??= ['red' => null, 'yellow' => null, 'done' => false];
            if ($z['progress'] === 'Completed') {
                $cleanState[$building]['done'] = true;
            } elseif ($end > $start && $nowTime > $end) {
                $cleanState[$building]['red'][] = $z['floor'];
            } else {
                $cleanState[$building]['yellow'][] = $z['floor'];
            }
        }
        foreach ($cleanState as $b => $v) {
            foreach (['red', 'yellow'] as $k) {
                if ($v[$k] !== null) $cleanState[$b][$k] = array_values(array_unique($v[$k]));
            }
        }

        $zoneCols = ['Zone', 'Floor', 'Staff', 'Tasks Done', 'Progress'];
        $zoneRow = fn($z) => [$z['zone'], $z['floor'], $z['staff'], $z['done'] . ' / ' . $z['total'], $z['badge'][0]];
        $outRows = [];
        foreach ($consumableGroups as $cat => $items) {
            foreach ($items as $it) {
                if ($it['status'] === 'Out of Stock') {
                    $outRows[] = [$it['name'], $cat, trim(($it['building'] ?? '—') . (!empty($it['floor']) ? ', ' . $it['floor'] : '')), $it['stock'] . ' ' . $it['unit']];
                }
            }
        }
        $statDetail = [
            'zones' => ['title' => 'Completed Cleaning Zones', 'columns' => $zoneCols, 'rows' => array_values(array_map($zoneRow, array_filter($zones, fn($z) => $z['progress'] === 'Completed')))],
            'tasks' => ['title' => 'Cleaning Tasks by Zone', 'columns' => $zoneCols, 'rows' => array_map($zoneRow, $zones)],
            'supplies' => ['title' => 'Supplies Out of Stock', 'columns' => ['Item', 'Category', 'Location', 'Stock'], 'rows' => $outRows],
        ];
        $bldCols = ['Building', 'Result', 'Checked By', 'Date'];
        $bldRow = fn($i) => [$i['building'], $i['result'] ?? 'Not checked', $i['inspected_by'] ?? '—', !empty($i['inspected_at']) ? date('M d, Y', strtotime($i['inspected_at'])) : '—'];
        $statDetail['checked'] = ['title' => 'Buildings Checked This Month', 'columns' => $bldCols, 'rows' => array_values(array_map($bldRow, array_filter($inspections, fn($i) => $i['result'] !== null)))];
        $statDetail['attention'] = ['title' => 'Buildings Needing Attention', 'columns' => $bldCols, 'rows' => array_values(array_map($bldRow, array_filter($inspections, fn($i) => $i['result'] === 'Needs Attention')))];
        $statDetail['notchecked'] = ['title' => 'Buildings Not Checked Yet', 'columns' => $bldCols, 'rows' => array_values(array_map($bldRow, array_filter($inspections, fn($i) => $i['result'] === null)))];
        $woCols2 = ['No.', 'Request', 'Building', 'Priority', 'Requested By', 'Status'];
        $woRow2 = fn($w) => ['WO-' . str_pad((string) $w['id'], 4, '0', STR_PAD_LEFT), $w['title'], $w['building'], $w['priority'], $w['requested_by'], $w['status']];
        $acCols2 = ['Building', 'Floor', 'Unit', 'Condition', 'Next Schedule', 'Schedule Status'];
        $acRow2 = fn($u) => [$u['building'], $u['floor'], $u['unit'], $u['condition'], !empty($u['next_schedule']) ? date('M d, Y', strtotime($u['next_schedule'])) : '—', $u['schedule'] === 'Due Soon' ? 'Overdue in 7 Days' : $u['schedule']];
        $acAlertList = array_merge(
            array_filter($aircon, fn($u) => $u['schedule'] === 'Overdue'),
            array_filter($aircon, fn($u) => $u['schedule'] === 'Due Soon')
        );
        $statDetail['wo_open'] = ['title' => 'Open Repair Requests', 'columns' => $woCols2, 'rows' => array_map($woRow2, $pending)];
        $statDetail['wo_urgent'] = ['title' => 'Urgent Repair Requests', 'columns' => $woCols2, 'rows' => array_values(array_map($woRow2, array_filter($pending, fn($w) => $w['priority'] === 'Urgent')))];
        $statDetail['wo_done'] = ['title' => 'Finished Repair Requests', 'columns' => $woCols2, 'rows' => array_map($woRow2, $history)];
        $statDetail['ac_units'] = ['title' => 'All Aircon Units', 'columns' => $acCols2, 'rows' => array_map($acRow2, $aircon)];
        $statDetail['ac_alert'] = ['title' => 'Aircon Units Overdue or Overdue in 7 Days', 'columns' => $acCols2, 'rows' => array_values(array_map($acRow2, $acAlertList))];
        $statDetail['ac_broken'] = ['title' => 'Aircon Units Not Working', 'columns' => $acCols2, 'rows' => array_values(array_map($acRow2, array_filter($aircon, fn($u) => $u['condition'] === 'Not Working')))];



        return view('facilities/index', [
            'title'      => self::SECTIONS[$section],
            'status'     => $status,
            'section'    => $section,
            'pageCss'    => 'safety.css',
            'buildings'  => FireExtinguisherModel::BUILDINGS,
            'floors_json' => $this->jsonForScript(\App\Libraries\FloorPlanCatalog::floorsByBuilding()),
            'floor_checks_json' => $this->jsonForScript($floorChecks),
            'pending'    => $pending,
            'history'    => $history,
            'building_rows' => $buildings,
            'open_count'   => count($pending),
            'done_count'   => count($history),
            'wo_json'      => $this->jsonForScript(array_map(fn($w) => ['id' => (int) $w['id'], 'title' => $w['title'], 'building' => $w['building'], 'floor' => $w['floor'], 'details' => $w['details'], 'priority' => $w['priority'], 'status' => $w['status'], 'requested_by' => $w['requested_by'], 'created_at' => $w['created_at'], 'completed_at' => $w['completed_at']], $all)),
            'aircon'       => $aircon,
            'aircon_alert' => $aircon_alert,
            'aircon_json'  => $this->jsonForScript($aircon),
            'zones'          => $zones,
            'morning_zones'  => array_values(array_filter($zones, fn($z) => $z['session'] === 'Morning')),
            'afternoon_zones'=> array_values(array_filter($zones, fn($z) => $z['session'] === 'Afternoon')),
            'inspections'    => $inspections,
            'inspection_month' => date('F Y'),
            'consumable_groups' => $consumableGroups,
            'active_stat' => $this->request->getGet('stat'),
            'stat_rows' => $statDetail[$this->request->getGet('stat')] ?? null,
            'clean_state_json'  => $this->jsonForScript($cleanState),
            'clean_alerts_json' => $this->jsonForScript($cleanAlerts),
            'ac_alerts_json'    => $this->jsonForScript($acAlerts),
            'bld_alerts_json'   => $this->jsonForScript($bldAlerts),
            'zones_json'        => $this->jsonForScript(array_map(fn($z) => ['id' => $z['id'], 'zone' => $z['zone'], 'building' => $zoneToBuilding[$z['zone']] ?? $z['zone'], 'floor' => $z['floor'], 'staff' => $z['staff'], 'shift' => $z['shift'], 'priority' => $z['priority'], 'status' => $z['status'], 'progress' => $z['progress'], 'done' => $z['done'], 'total' => $z['total'], 'tasks' => $z['tasks']], $zones)),
        ]);
    }

    public function store()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $title    = trim((string) $this->request->getPost('title'));
        $building = trim((string) $this->request->getPost('building'));

        if ($title === '' || $building === '') {
            return redirect()->to('/facilities/work-orders')->with('error', 'Request and building are required.');
        }

        $priority = $this->request->getPost('priority') === 'Urgent' ? 'Urgent' : 'Routine';

        (new WorkOrderModel())->insert([
            'title'        => $title,
            'building'     => $building,
            'floor'        => trim((string) $this->request->getPost('floor')) ?: null,
            'details'      => trim((string) $this->request->getPost('details')) ?: null,
            'priority'     => $priority,
            'status'       => 'Pending',
            'requested_by' => (string) (session()->get('full_name') ?? 'Unknown'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/facilities/work-orders')->with('success', "Work order submitted for {$building}.");
    }

    public function storeAircon()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $unit     = trim((string) $this->request->getPost('unit_name'));
        $building = trim((string) $this->request->getPost('building'));
        if ($unit === '' || !in_array($building, FireExtinguisherModel::BUILDINGS, true)) {
            return redirect()->to('/facilities/aircon')->with('error', 'Unit name and building are required.');
        }

        $condition = (string) $this->request->getPost('condition');
        if (!in_array($condition, ['Operational', 'Needs Cleaning', 'Not Working'], true)) {
            $condition = 'Operational';
        }

        $model = new AirconUnitModel();
        $id = $model->insert([
            'location'         => $building,
            'floor'            => trim((string) $this->request->getPost('floor')) ?: 'Ground Floor',
            'unit_name'        => $unit,
            'last_cleaning'    => $this->request->getPost('last_cleaning') ?: null,
            'next_schedule'    => $this->request->getPost('next_schedule') ?: null,
            'condition_status' => $condition,
            'assigned_tech'    => trim((string) $this->request->getPost('assigned_tech')) ?: null,
            'installed_by'     => trim((string) $this->request->getPost('installed_by')) ?: null,
        ], true);

        (new AirconChecklistItemModel())->seedDefaultTasks((int) $id);

        return redirect()->to('/facilities/aircon')->with('success', "Aircon unit {$unit} added.");
    }

    public function storeSupply()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $name = trim((string) $this->request->getPost('item_name'));
        if ($name === '') {
            return redirect()->to('/facilities/janitorial?tab=supplies')->with('error', 'Item name is required.');
        }

        $category = (string) $this->request->getPost('category');
        if (!in_array($category, ['Cleaning Detergent', 'Disposable', 'Equipment'], true)) {
            $category = 'Cleaning Detergent';
        }

        (new ConsumableInventoryModel())->insert([
            'item_name'         => $name,
            'category'          => $category,
            'department'        => 'Facilities',
            'unit'              => trim((string) $this->request->getPost('unit')) ?: 'Pieces',
            'building'          => $this->request->getPost('building') ?: null,
            'floor'             => trim((string) $this->request->getPost('floor')) ?: null,
            'location_note'     => trim((string) $this->request->getPost('location_note')) ?: null,
            'current_stock'     => (float) $this->request->getPost('current_stock'),
            'reorder_threshold' => 0,
            'last_refill'       => date('Y-m-d'),
        ]);

        return redirect()->to('/facilities/janitorial?tab=supplies')->with('success', "{$name} added to Supplies.");
    }

    public function storeInspection()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $building = trim((string) $this->request->getPost('building'));
        $result   = (string) $this->request->getPost('result');

        if (!in_array($building, FireExtinguisherModel::BUILDINGS, true) || !in_array($result, JanitorialInspectionModel::RESULTS, true)) {
            return redirect()->to('/facilities/buildings')->with('error', 'Choose a building and a result.');
        }

        $floor = trim((string) $this->request->getPost('floor'));
        if ($floor !== '' && !in_array($floor, \App\Libraries\FloorPlanCatalog::floorsByBuilding()[$building] ?? [], true)) $floor = '';

        (new JanitorialInspectionModel())->insert([
            'building'         => $building,
            'floor'            => $floor !== '' ? $floor : null,
            'inspection_month' => date('Y-m'),
            'result'           => $result,
            'inspected_by'     => (string) (session()->get('full_name') ?? 'Unknown'),
            'notes'            => trim((string) $this->request->getPost('notes')) ?: null,
            'inspected_at'     => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/facilities/buildings')->with('success', "Monthly inspection saved for {$building}.");
    }

    public function updateStatus(int $id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $status = (string) $this->request->getPost('status');
        if (!in_array($status, WorkOrderModel::STATUSES, true)) {
            return redirect()->to('/facilities/work-orders?tab=pending')->with('error', 'Unknown status.');
        }

        (new WorkOrderModel())->update($id, [
            'status'       => $status,
            'updated_at'   => date('Y-m-d H:i:s'),
            'completed_at' => $status === 'Completed' ? date('Y-m-d H:i:s') : null,
        ]);

        return redirect()->to('/facilities/work-orders?tab=pending')->with('success', "Work order marked {$status}.");
    }
}
