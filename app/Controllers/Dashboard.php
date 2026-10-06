<?php

namespace App\Controllers;

use App\Models\VehicleModel;
use App\Models\BorrowModel;
use App\Models\ToolsModel;
use App\Models\FireExtinguisherModel;
use App\Models\SafetyWorkOrderModel;
use App\Models\JanitorialAssignmentModel;
use App\Models\JanitorialTaskModel;
use App\Models\TravelModel;
use App\Models\WorkOrderModel;
use App\Models\AirconUnitModel;
use App\Models\JanitorialInspectionModel;
use App\Models\KeyBorrowLogModel;

class Dashboard extends BaseController
{
    public function index()
    {
        return view('dashboard/index', $this->buildDashboardData());
    }

    /**
     * GET /dashboard/refresh — same data as index() but as JSON, so the KPI
     * cards, alerts, and activity/travel feeds can pick up whatever's
     * changed (borrows, work orders, janitorial tasks, trips) since page
     * load without a full reload. Same pattern as Janitorial Monitoring's
     * own refreshData() — see JanitorialController.
     */
    public function refreshData()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        return $this->response->setJSON($this->buildDashboardData());
    }

    private function buildDashboardData(): array
    {
        $vehicleModel     = new VehicleModel();
        $borrowModel      = new BorrowModel();
        $toolsModel       = new ToolsModel();
        $fireModel        = new FireExtinguisherModel();
        $workOrderModel   = new SafetyWorkOrderModel();
        $assignmentModel  = new JanitorialAssignmentModel();
        $taskModel        = new JanitorialTaskModel();
        $travelModel      = new TravelModel();

        $today = date('Y-m-d');

        $allBorrowRecords = $borrowModel->getAllWithDetails();

        // ── Pending Requests: real open items across Tools and Maintenance ──
        // Source of truth is tools.availability, same as Tools Management —
        // not borrow_records — so this number matches exactly what shows up
        // when you click through and filter the Tools table to "Borrowed".
        $borrowedToolRows   = $toolsModel->where('availability', 'Borrowed')->where('is_archived', 0)->findAll();
        $openWorkOrdersList = $workOrderModel->where('stage !=', 'Completed/Verified')->orderBy('priority', 'DESC')->findAll();

        // Not every "Borrowed" tool necessarily has a matching open borrow_records
        // row (the two can drift) — match up what we can, and say so when we can't.
        $openBorrowByTool = [];
        foreach ($allBorrowRecords as $b) {
            if ($b['status'] === 'Borrowed') {
                $openBorrowByTool[$b['tool_id']] = $b;
            }
        }
        $borrowedToolsList = array_map(function ($t) use ($openBorrowByTool) {
            $record = $openBorrowByTool[$t['id']] ?? null;
            return [
                'name'     => $t['asset_name'],
                'borrower' => $record['borrower'] ?? 'Not on record',
                'due'      => $record['expected_return'] ?? null,
            ];
        }, $borrowedToolRows);

        $borrowedTools   = count($borrowedToolRows);
        $openWorkOrders  = count($openWorkOrdersList);
        $pendingRequests = $borrowedTools + $openWorkOrders;

        // ── Active Borrowings — same tools.availability count as above, so
        // both boxes agree with each other and with the Tools page. ──
        $activeBorrowings = $borrowedTools;
        $dueBackToday     = $borrowModel->where('status', 'Borrowed')->where('expected_return', $today)->countAllResults();

        // ── Vehicles in Use — same shared query used by Vehicle Management and GPS Tracker ──
        $fleetStats       = $vehicleModel->getFleetStats();

        // ── Maintenance Due: open work orders + fire extinguishers overdue for inspection ──
        $overdueFe       = $fireModel->where('next_due <', $today)->countAllResults();
        // Same shared calculation Security Dashboard's own "Building
        // Coverage" card uses (FireExtinguisherModel::getBuildingCoverage())
        // — "every real campus building has at least one fire extinguisher
        // installed", not just a raw unit total.
        $buildingCoverage = $fireModel->getBuildingCoverage();
        // Same idea, one floor finer: a building can already count as
        // "covered" above from just one Ground Floor unit while its upper
        // floors have none — this catches that.
        $floorCoverage    = $fireModel->getFloorCoverage();
        $maintenanceDue  = $openWorkOrders + $overdueFe;

        // ── Cleaning Completion: same zones/tasks data used by Janitorial Monitoring ──
        $assignments = $assignmentModel->findAll();
        $allTasks    = $taskModel->findAll();
        $assignmentsById = [];
        foreach ($assignments as $a) {
            $assignmentsById[$a['id']] = $a;
        }
        $tasksByAssignment = [];
        foreach ($allTasks as $task) {
            $tasksByAssignment[$task['assignment_id']][] = $task;
        }
        // A zone can have more than one shift/assignment (e.g. two staff
        // covering the same building) — count it once, and as cleaned as
        // soon as ANY ONE assignment mapped to it is done (a zone with
        // several staff doesn't wait on everyone). Same rule as
        // JanitorialAssignmentModel::getZoneCleanCounts() and
        // JanitorialController::index(), so this box, the incomplete-zones
        // list below it, and the Janitorial page's own "Janitorial
        // Completion" stat can never disagree with each other.
        $zoneShifts = [];
        foreach ($assignments as $a) {
            $tasks = $tasksByAssignment[$a['id']] ?? [];
            $done  = count(array_filter($tasks, fn($t) => (int) $t['is_done'] === 1));
            $zoneShifts[$a['assigned_zone']][] = ['done' => $done, 'total' => count($tasks)];
        }
        $zoneIsDone = function (array $shifts): bool {
            foreach ($shifts as $s) {
                if ($s['total'] > 0 && $s['done'] === $s['total']) return true;
            }
            return false;
        };
        $totalZones   = count($zoneShifts);
        $cleanedZones = count(array_filter($zoneShifts, $zoneIsDone));

        $cleaningIncompleteList = [];
        foreach ($zoneShifts as $zoneName => $shifts) {
            if ($zoneIsDone($shifts)) continue;
            $doneCount  = array_sum(array_column($shifts, 'done'));
            $totalCount = array_sum(array_column($shifts, 'total'));
            $cleaningIncompleteList[] = [
                'title'    => $zoneName,
                'subtitle' => "{$doneCount} of {$totalCount} tasks done",
            ];
        }

        // ── Detail lists behind each KPI banner — same idea as the Pending
        // Requests panel's two columns, just one real itemized list per
        // card instead of repeating the card's own summary text back to itself. ──
        $vehiclesInUseList = array_map(fn($v) => [
            'title'    => $v['vehicle_name'] . ' (' . $v['plate_no'] . ')',
            'subtitle' => 'Driver: ' . ($v['driver_name'] ?? 'Unassigned'),
        ], array_values(array_filter($vehicleModel->getAllWithDetails(), fn($v) => $v['availability'] === 'In Use')));

        $overdueFeRows = $fireModel->where('next_due <', $today)->findAll();
        $maintenanceDueList = array_merge(
            array_map(fn($w) => [
                'title'    => $w['wo_number'] . ' — ' . $w['issue'],
                'subtitle' => $w['location'] . ' · ' . $w['priority'] . ' priority',
            ], $openWorkOrdersList),
            array_map(fn($f) => [
                'title'    => 'FE ' . $f['unit_id'] . ' overdue',
                'subtitle' => $f['location'] . ' · due ' . $f['next_due'],
            ], $overdueFeRows)
        );

        $activeBorrowingsList = array_map(fn($t) => [
            'title'    => $t['name'],
            'subtitle' => $t['borrower'] . ' · due ' . ($t['due'] ?? '—'),
        ], $borrowedToolsList);

        // ── Recent Activity: merged from real timestamped events (no activity_logs data exists yet) ──
        $activityFeed = [];
        foreach ($allBorrowRecords as $b) {
            if (empty($b['last_activity_at'])) continue;
            $activityFeed[] = [
                'ts'   => $b['last_activity_at'],
                'tag'  => 'Tools',
                'text' => ($b['status'] === 'Returned')
                    ? "{$b['asset_name']} returned by {$b['borrower']}."
                    : "{$b['asset_name']} borrowed by {$b['borrower']}.",
            ];
        }
        foreach ($allTasks as $task) {
            if ((int) $task['is_done'] !== 1 || empty($task['completed_at'])) continue;
            $zone = $assignmentsById[$task['assignment_id']]['assigned_zone'] ?? 'a zone';
            $activityFeed[] = [
                'ts'   => $task['completed_at'],
                'tag'  => 'Janitorial',
                'text' => "\"{$task['task_name']}\" completed in {$zone}.",
            ];
        }
        usort($activityFeed, fn($a, $b) => strcmp($b['ts'], $a['ts']));
        $activity = array_map(fn($a) => [
            'time' => date('H:i', strtotime($a['ts'])),
            'tag'  => $a['tag'],
            'text' => $a['text'],
        ], array_slice($activityFeed, 0, 7));

        // ── Travel History: same TravelModel/travel_requests data used by the
        // Driver's Trip Ticket page and the Guard page, so all three
        // always agree on trip status. ──
        $recentTrips = $travelModel->getAllWithDetails();
        usort($recentTrips, fn($a, $b) => strcmp($b['last_activity_at'], $a['last_activity_at']));
        $travelHistory = array_map(fn($t) => [
            'trip_id'       => $t['trip_id'],
            'destination'   => $t['destination'],
            'requester'     => $t['requester_name'] ?? 'Unknown',
            'date'          => date('M j, Y', strtotime($t['travel_date'])),
            'driver'        => $t['driver_name'] ?? 'Unassigned',
            'vehicle'       => $t['vehicle_name'] ? "{$t['vehicle_name']} ({$t['plate_no']})" : 'Unassigned',
            'tire_pressure' => $t['tire_pressure_psi'] !== null ? $t['tire_pressure_psi'] . ' PSI' : '—',
            'status'        => $t['status'],
        ], array_slice($recentTrips, 0, 6));

        $alerts = [
            [
                'icon' => 'bi-exclamation-circle-fill',
                'tone' => 'urgent',
                'title' => "{$overdueFe} overdue maintenance tasks",
                'subtitle' => 'Fire extinguisher inspections past due',
                'time' => 'Today',
                'url' => 'safety',
            ],
            [
                'icon' => 'bi-hourglass-split',
                'tone' => 'pending',
                'title' => "{$borrowedTools} tools currently borrowed",
                'subtitle' => 'Tracked in Tools Management',
                'time' => 'Today',
                'url' => 'tools',
            ],
            [
                'icon' => 'bi-exclamation-circle-fill',
                'tone' => 'urgent',
                'title' => ($totalZones - $cleanedZones) . ' janitorial zones not yet complete',
                'subtitle' => 'Janitorial Monitoring',
                'time' => 'Today',
                'url' => 'janitorial',
            ],
            [
                'icon' => 'bi-hourglass-split',
                'tone' => 'pending',
                'title' => "{$openWorkOrders} safety work orders open",
                'subtitle' => 'Safety and Security',
                'time' => 'This week',
                'url' => 'safety',
            ],
        ];

        // Department sections: one card per sidebar group, with its sub-pages and a live count where there is one.
        $month = date('Y-m');
        $facOpen  = (new WorkOrderModel())->whereIn('status', ['Pending', 'In Progress'])->countAllResults();
        $facAir   = (new AirconUnitModel())->where('next_schedule <=', date('Y-m-d', strtotime('+7 days')))->countAllResults();
        $facCheck = (new JanitorialInspectionModel())->where('inspection_month', $month)->where('result', 'Needs Attention')->countAllResults();
        $keysOut  = (new KeyBorrowLogModel())->where('status', 'Active')->countAllResults();
        $gpsOnline = $vehicleModel->where('is_archived', 0)->where('gps_status', 'Online')->countAllResults();
        $tripsOpen = $travelModel->whereIn('status', ['Pending', 'Reviewed', 'Approved', 'In Transit'])->countAllResults();
        $deptSections = [
            ['title' => 'Facilities', 'icon' => 'bi-building-gear', 'url' => 'facilities/status', 'links' => [
                // Repair Requests hidden for now: ['label' => 'Repair Requests', 'url' => 'facilities/work-orders', 'count' => $facOpen . ' open'],
                ['label' => 'Aircon Care', 'url' => 'facilities/aircon', 'icon' => 'bi-snow2', 'count' => $facAir . ' due'],
                ['label' => 'Janitorial Check', 'url' => 'facilities/janitorial', 'icon' => 'bi-brush', 'count' => ($totalZones - $cleanedZones) . ' zones left'],
                ['label' => 'Building Check', 'url' => 'facilities/buildings', 'icon' => 'bi-building-check', 'count' => $facCheck . ' need attention'],
            ]],
            ['title' => 'Safety and Security', 'icon' => 'bi-shield-fill-check', 'url' => 'safety', 'links' => [
                ['label' => 'Fire Safety', 'url' => 'safety', 'icon' => 'bi-fire', 'count' => $overdueFe . ' overdue'],
                ['label' => 'Safety Inspection', 'url' => 'security-dept/inspection', 'icon' => 'bi-clipboard2-check', 'count' => ''],
                ['label' => 'Guard Monitoring', 'url' => 'security-dept/guard', 'icon' => 'bi-shield-check', 'count' => ''],
                ['label' => 'List of Keys', 'url' => 'security-dept/keys', 'icon' => 'bi-list-ul', 'count' => $keysOut . ' out'],
            ]],
            ['title' => 'Vehicle Management', 'icon' => 'bi-truck', 'url' => 'vehicles/dashboard', 'links' => [
                ['label' => 'Vehicle List', 'url' => 'vehicles', 'icon' => 'bi-truck', 'count' => $fleetStats['total'] . ' vehicles'],
                ['label' => 'GPS Tracker', 'url' => 'gps', 'icon' => 'bi-geo-alt-fill', 'count' => $gpsOnline . ' online'],
                ['label' => 'Trip Ticket', 'url' => 'travel', 'icon' => 'bi-ticket-perforated', 'count' => $tripsOpen . ' open'],
            ]],
            ['title' => 'Personnel Management', 'icon' => 'bi-people', 'url' => 'personnel', 'links' => [
                ['label' => 'All Personnel', 'url' => 'personnel', 'icon' => 'bi-people-fill', 'count' => ''],
                ['label' => 'Drivers', 'url' => 'personnel/drivers', 'icon' => 'bi-person-vcard-fill', 'count' => ''],
                ['label' => 'Janitors', 'url' => 'personnel/janitors', 'icon' => 'bi-brush', 'count' => ''],
                ['label' => 'Carpentries Shop', 'url' => 'personnel/carpentries', 'icon' => 'bi-hammer', 'count' => ''],
                ['label' => 'Maintenance', 'url' => 'personnel/maintenance', 'icon' => 'bi-wrench', 'count' => ''],
                ['label' => 'Construction Workers', 'url' => 'personnel/construction-workers', 'icon' => 'bi-cone-striped', 'count' => ''],
            ]],
            ['title' => 'Tools Management', 'icon' => 'bi-tools', 'url' => 'tools', 'links' => [
                ['label' => 'All Tools', 'url' => 'tools', 'icon' => 'bi-boxes', 'count' => $borrowedTools . ' borrowed'],
                ['label' => 'Power Tools', 'url' => 'tools/power-tools', 'icon' => 'bi-lightning-fill', 'count' => (new ToolsModel())->where('category', 'Power Tools')->where('is_archived', 0)->countAllResults() . ' items'],
                ['label' => 'Supplies & Materials', 'url' => 'tools/consumable', 'icon' => 'bi-box-seam-fill', 'count' => (new ToolsModel())->where('category', 'Consumable')->where('is_archived', 0)->countAllResults() . ' items'],
                ['label' => 'Sports Equipment', 'url' => 'tools/sports-equipment', 'icon' => 'bi-trophy-fill', 'count' => (new ToolsModel())->where('category', 'Sports Equipment')->where('is_archived', 0)->countAllResults() . ' items'],
                ['label' => 'Borrowing', 'url' => 'tools/borrowing', 'icon' => 'bi-hand-index-thumb-fill', 'count' => $dueBackToday . ' due today'],
            ]],
        ];

        $data = [
            'dept_sections' => $deptSections,
            'title' => 'UBRA Monitoring Dashboard',
            'pageCss' => 'dashboard.css',
            'showTopbar' => true,
            // Time only — the date directly above it in the topbar already
            // shows the full date, so repeating it here was redundant.
            'last_updated' => date('g:i A'),
            'kpis' => [
                [
                    'label' => 'Pending Requests',
                    'value' => (string) $pendingRequests,
                    'meta' => "{$borrowedTools} tools · {$openWorkOrders} work orders",
                    'sub' => 'Waiting on approval',
                    'tone' => 'tone-gold',
                    'icon' => 'bi-clipboard2-check',
                    'expand' => 'pending',
                ],
                [
                    'label' => 'Active Borrowings',
                    'value' => (string) $activeBorrowings,
                    'meta' => "{$dueBackToday} due back today",
                    'sub' => 'Tracked across campus',
                    'tone' => 'tone-neutral',
                    'icon' => 'bi-hand-index-thumb-fill',
                    'url' => 'tools?filter=borrowed',
                    'listKey' => 'activeBorrowingsList',
                ],
                [
                    'label' => 'Vehicles in Use',
                    'value' => "{$fleetStats['in_use']}/{$fleetStats['total']}",
                    'meta' => "{$fleetStats['available']} available",
                    'sub' => 'Dispatch coverage',
                    'tone' => 'tone-neutral',
                    'icon' => 'bi-truck',
                    'url' => 'vehicles?filter=inuse',
                    'listKey' => 'vehiclesInUseList',
                ],
                [
                    'label' => 'Maintenance Due',
                    'value' => (string) $maintenanceDue,
                    'meta' => "{$overdueFe} overdue",
                    'sub' => 'Immediate attention',
                    'tone' => 'tone-red',
                    'icon' => 'bi-wrench-adjustable',
                    'url' => 'safety?filter=duework',
                    'listKey' => 'maintenanceDueList',
                ],
                [
                    'label' => 'Cleaning Completion',
                    'value' => "{$cleanedZones}/{$totalZones} areas",
                    'meta' => ($totalZones - $cleanedZones) . ' areas remaining',
                    'sub' => 'As of ' . date('g:i A'),
                    'tone' => 'tone-green',
                    'icon' => 'bi-brush',
                    'url' => 'janitorial?filter=pending',
                    'listKey' => 'cleaningIncompleteList',
                ],
                [
                    'label' => 'Building Coverage',
                    'value' => "{$buildingCoverage['covered']}/{$buildingCoverage['total']} buildings",
                    'meta' => 'Fire extinguisher installed',
                    'sub' => 'Campus-wide',
                    'tone' => 'tone-green',
                    'icon' => 'bi-building-check',
                    'url' => 'safety',
                    'listKey' => 'buildingsNotCoveredList',
                ],
                [
                    'label' => 'Floor Coverage',
                    'value' => "{$floorCoverage['covered']}/{$floorCoverage['total']} floors",
                    'meta' => ($floorCoverage['total'] - $floorCoverage['covered']) . ' floors need one',
                    'sub' => 'Per-floor, campus-wide',
                    'tone' => $floorCoverage['covered'] === $floorCoverage['total'] ? 'tone-green' : 'tone-gold',
                    'icon' => 'bi-layers-half',
                    'url' => 'safety',
                    'listKey' => 'floorsNotCoveredList',
                ],
            ],
            'pending_tools_json' => $this->jsonForScript($borrowedToolsList),
            'pending_workorders_json' => $this->jsonForScript(array_map(fn($w) => [
                'id'       => $w['wo_number'],
                'issue'    => $w['issue'],
                'loc'      => $w['location'],
                'priority' => $w['priority'],
            ], $openWorkOrdersList)),
            'active_borrowings_json'   => $this->jsonForScript($activeBorrowingsList),
            'vehicles_inuse_json'      => $this->jsonForScript($vehiclesInUseList),
            'maintenance_due_json'     => $this->jsonForScript($maintenanceDueList),
            'cleaning_incomplete_json' => $this->jsonForScript($cleaningIncompleteList),
            'buildings_not_covered_json' => $this->jsonForScript(array_map(fn($b) => [
                'title'    => $b,
                'subtitle' => 'No fire extinguisher recorded yet',
            ], array_values(array_diff(FireExtinguisherModel::BUILDINGS, array_unique(array_column($fireModel->getBuildingCounts(), 'location')))))),
            'floors_not_covered_json' => $this->jsonForScript(array_map(fn($m) => [
                'title'    => $m['building'],
                'subtitle' => $m['floor'] . ' — no fire extinguisher recorded yet',
            ], $floorCoverage['missing'])),
            'alerts_json' => $this->jsonForScript(array_map(fn($a) => array_merge($a, ['url' => site_url($a['url'])]), $alerts)),
            'activity' => $activity,
            'activity_json' => $this->jsonForScript($activity),
            'travel_history' => $travelHistory,
            'travel_history_json' => $this->jsonForScript($travelHistory),
        ];

        return $data;
    }
}
