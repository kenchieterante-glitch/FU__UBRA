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

class Dashboard extends BaseController
{
    public function index()
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
        // covering the same building) — count it once, and only as cleaned
        // once every assignment mapped to it is done. Same aggregation as
        // JanitorialController::index(), so this box and the Janitorial
        // page's own "Janitorial Completion" stat never disagree.
        $zoneShifts = [];
        foreach ($assignments as $a) {
            $tasks = $tasksByAssignment[$a['id']] ?? [];
            $done  = count(array_filter($tasks, fn($t) => (int) $t['is_done'] === 1));
            $zoneShifts[$a['assigned_zone']][] = ['done' => $done, 'total' => count($tasks)];
        }
        $totalZones   = count($zoneShifts);
        $cleanedZones = count(array_filter($zoneShifts, function ($shifts) {
            foreach ($shifts as $s) {
                if ($s['total'] === 0 || $s['done'] !== $s['total']) return false;
            }
            return true;
        }));

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
        // Driver's Trip Ticket page and the Guard Dashboard, so all three
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

        $data = [
            'title' => 'UBRA Monitoring Dashboard',
            'pageCss' => 'dashboard.css',
            'showTopbar' => true,
            'last_updated' => date('M j, Y g:i A'),
            'kpis' => [
                [
                    'label' => 'Pending Requests',
                    'value' => (string) $pendingRequests,
                    'meta' => "{$borrowedTools} tools · {$openWorkOrders} work orders",
                    'sub' => 'Waiting on approval',
                    'tone' => 'tone-gold',
                    'icon' => 'fa-clipboard-list',
                    'expand' => 'pending',
                ],
                [
                    'label' => 'Active Borrowings',
                    'value' => (string) $activeBorrowings,
                    'meta' => "{$dueBackToday} due back today",
                    'sub' => 'Tracked across campus',
                    'tone' => 'tone-neutral',
                    'icon' => 'fa-hand-holding',
                    'url' => 'tools?filter=borrowed',
                ],
                [
                    'label' => 'Vehicles in Use',
                    'value' => "{$fleetStats['in_use']}/{$fleetStats['total']}",
                    'meta' => "{$fleetStats['available']} available",
                    'sub' => 'Dispatch coverage',
                    'tone' => 'tone-neutral',
                    'icon' => 'fa-truck',
                    'url' => 'vehicles?filter=inuse',
                ],
                [
                    'label' => 'Maintenance Due',
                    'value' => (string) $maintenanceDue,
                    'meta' => "{$overdueFe} overdue",
                    'sub' => 'Immediate attention',
                    'tone' => 'tone-red',
                    'icon' => 'fa-screwdriver-wrench',
                    'url' => 'safety?filter=duework',
                ],
                [
                    'label' => 'Cleaning Completion',
                    'value' => "{$cleanedZones}/{$totalZones} areas",
                    'meta' => ($totalZones - $cleanedZones) . ' areas remaining',
                    'sub' => 'As of ' . date('g:i A'),
                    'tone' => 'tone-green',
                    'icon' => 'fa-broom',
                    'url' => 'janitorial?filter=pending',
                ],
            ],
            'pending_tools_json' => $this->jsonForScript($borrowedToolsList),
            'pending_workorders_json' => $this->jsonForScript(array_map(fn($w) => [
                'id'       => $w['wo_number'],
                'issue'    => $w['issue'],
                'loc'      => $w['location'],
                'priority' => $w['priority'],
            ], $openWorkOrdersList)),
            'alerts' => [
                [
                    'icon' => 'fa-circle-exclamation',
                    'tone' => 'urgent',
                    'title' => "{$overdueFe} overdue maintenance tasks",
                    'subtitle' => 'Fire extinguisher inspections past due',
                    'time' => 'Today',
                    'url' => 'safety',
                ],
                [
                    'icon' => 'fa-hourglass-half',
                    'tone' => 'pending',
                    'title' => "{$borrowedTools} tools currently borrowed",
                    'subtitle' => 'Tracked in Tools Management',
                    'time' => 'Today',
                    'url' => 'tools',
                ],
                [
                    'icon' => 'fa-circle-exclamation',
                    'tone' => 'urgent',
                    'title' => ($totalZones - $cleanedZones) . ' janitorial zones not yet complete',
                    'subtitle' => 'Janitorial Monitoring',
                    'time' => 'Today',
                    'url' => 'janitorial',
                ],
                [
                    'icon' => 'fa-hourglass-half',
                    'tone' => 'pending',
                    'title' => "{$openWorkOrders} maintenance work orders open",
                    'subtitle' => 'Maintenance',
                    'time' => 'This week',
                    'url' => 'safety',
                ],
            ],
            'activity' => $activity,
            'travel_history' => $travelHistory,
        ];

        return view('dashboard/index', $data);
    }
}
