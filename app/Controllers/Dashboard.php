<?php

namespace App\Controllers;

use App\Models\VehicleModel;
use App\Models\BorrowModel;
use App\Models\FireExtinguisherModel;
use App\Models\SafetyWorkOrderModel;
use App\Models\JanitorialAssignmentModel;
use App\Models\JanitorialTaskModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $vehicleModel     = new VehicleModel();
        $borrowModel      = new BorrowModel();
        $fireModel        = new FireExtinguisherModel();
        $workOrderModel   = new SafetyWorkOrderModel();
        $assignmentModel  = new JanitorialAssignmentModel();
        $taskModel        = new JanitorialTaskModel();

        $today = date('Y-m-d');

        // ── Pending Requests: real open items across Tools and Maintenance ──
        $borrowedTools   = $borrowModel->where('status', 'Borrowed')->countAllResults();
        $openWorkOrders  = $workOrderModel->where('stage !=', 'Completed/Verified')->countAllResults();
        $pendingRequests = $borrowedTools + $openWorkOrders;

        // ── Active Borrowings ──
        $activeBorrowings = $borrowModel->where('status', 'Borrowed')->countAllResults();
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
        $totalZones   = count($assignments);
        $cleanedZones = 0;
        foreach ($assignments as $a) {
            $tasks = $tasksByAssignment[$a['id']] ?? [];
            $done  = count(array_filter($tasks, fn($t) => (int) $t['is_done'] === 1));
            if (count($tasks) > 0 && $done === count($tasks)) {
                $cleanedZones++;
            }
        }

        // ── Recent Activity: merged from real timestamped events (no activity_logs data exists yet) ──
        $activityFeed = [];
        foreach ($borrowModel->getAllWithDetails() as $b) {
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

        $data = [
            'title' => 'GroundWorks Monitoring Dashboard',
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
                    'url' => 'tools',
                ],
                [
                    'label' => 'Active Borrowings',
                    'value' => (string) $activeBorrowings,
                    'meta' => "{$dueBackToday} due back today",
                    'sub' => 'Tracked across campus',
                    'tone' => 'tone-neutral',
                    'icon' => 'fa-hand-holding',
                    'url' => 'tools',
                ],
                [
                    'label' => 'Vehicles in Use',
                    'value' => "{$fleetStats['in_use']}/{$fleetStats['total']}",
                    'meta' => "{$fleetStats['available']} available",
                    'sub' => 'Dispatch coverage',
                    'tone' => 'tone-neutral',
                    'icon' => 'fa-truck',
                    'url' => 'vehicles',
                ],
                [
                    'label' => 'Maintenance Due',
                    'value' => (string) $maintenanceDue,
                    'meta' => "{$overdueFe} overdue",
                    'sub' => 'Immediate attention',
                    'tone' => 'tone-red',
                    'icon' => 'fa-screwdriver-wrench',
                    'url' => 'safety',
                ],
                [
                    'label' => 'Cleaning Completion',
                    'value' => "{$cleanedZones}/{$totalZones} areas",
                    'meta' => ($totalZones - $cleanedZones) . ' areas remaining',
                    'sub' => 'As of ' . date('g:i A'),
                    'tone' => 'tone-green',
                    'icon' => 'fa-broom',
                    'url' => 'janitorial',
                ],
            ],
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
                    'subtitle' => 'Safety Maintenance',
                    'time' => 'This week',
                    'url' => 'safety',
                ],
            ],
            'activity' => $activity,
            'quickAccess' => [
                ['label' => 'Tools', 'icon' => 'fa-boxes-stacked', 'url' => 'tools'],
                ['label' => 'Vehicles', 'icon' => 'fa-truck', 'url' => 'vehicles'],
                ['label' => 'Maintenance', 'icon' => 'fa-screwdriver-wrench', 'url' => 'safety'],
                ['label' => 'Janitorial', 'icon' => 'fa-broom', 'url' => 'janitorial'],
                ['label' => 'Records & Reports', 'icon' => 'fa-folder-closed', 'url' => 'reports'],
            ],
        ];

        return view('dashboard/index', $data);
    }
}
