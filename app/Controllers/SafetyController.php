<?php

namespace App\Controllers;

use App\Models\FireExtinguisherModel;
use App\Models\KeyBorrowLogModel;
use App\Models\TravelModel;

class SafetyController extends BaseController
{
    protected $session;
    protected $fireExtinguisherModel;
    protected $keyBorrowLogModel;
    protected $travelModel;

    // Maps each real fire-extinguisher location onto the fixed slug used by the campus map SVG.
    private const ZONE_SLUGS = [
        'Admin Building'    => 'admin',
        'Library'           => 'library',
        'Science Building'  => 'science',
        'Gymnasium'         => 'gym',
        'Canteen'           => 'canteen',
        'Engineering'       => 'engr',
        'CCS Building'      => 'ccs',
        'Clinic'            => 'clinic',
        'Guard House'       => 'guard-post',
    ];

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->fireExtinguisherModel = new FireExtinguisherModel();
        $this->keyBorrowLogModel = new KeyBorrowLogModel();
        $this->travelModel = new TravelModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $units = $this->fireExtinguisherModel->findAll();
        $today = date('Y-m-d');

        $needsAttention = count(array_filter($units, fn($u) => in_array($u['status'], ['Defective', 'Missing'], true)));
        $dueForRefill   = count(array_filter($units, fn($u) => $u['status'] === 'Refillable'));
        $overdue        = count(array_filter($units, fn($u) => !empty($u['next_due']) && $u['next_due'] < $today));
        $readiness      = count($units) > 0 ? round((count($units) - $overdue) / count($units) * 100) : 100;

        $feRegistry = array_map(fn($u) => [
            'id'        => $u['unit_id'],
            'type'      => $u['type'],
            'loc'       => $u['location'],
            'kg'        => (float) $u['weight_kg'],
            'lastInsp'  => $u['last_inspection'],
            'nextDue'   => $u['next_due'],
            'status'    => $u['status'],
            'year'      => (int) $u['year_acquired'],
            'inspector' => $u['inspector'] ?? '—',
            'assigned'  => $u['assigned_guard'] ?? '—',
        ], $units);

        $areas = [];
        foreach (self::ZONE_SLUGS as $locationName => $slug) {
            $areas[$slug] = ['name' => $locationName];
        }

        return view('safety/index', [
            'title' => 'Safety Maintenance',
            'openModule' => 'safety',
            'fe_registry_json' => $this->jsonForScript($feRegistry),
            'areas_json'       => $this->jsonForScript($areas),
            'coverage_total'      => count($units),
            'coverage_attention'  => $needsAttention,
            'coverage_refill'     => $dueForRefill,
            'inspection_readiness' => $readiness,
        ]);
    }

    public function guardDashboard()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        // Only Approved/Completed trips have actually been released — a Pending
        // trip has no business showing up here or being linked to a key issuance.
        $allTrips = $this->travelModel->getAllWithDetails();
        $releasedTrips = array_values(array_filter(
            $allTrips,
            fn($t) => in_array($t['status'], ['Approved', 'Completed'], true)
        ));

        $keyLogs = $this->keyBorrowLogModel->getAllWithTrip();
        $activeBorrows = array_values(array_filter($keyLogs, fn($k) => $k['status'] === 'Active'));

        // Derive the activity feed from the real scan_in/scan_out timestamps —
        // not a fabricated log.
        $activity = [];
        foreach ($keyLogs as $k) {
            $activity[] = ['time' => $k['scan_in'], 'action' => "Key {$k['log_number']} issued to {$k['full_name']} ({$k['department']})"];
            if (!empty($k['scan_out'])) {
                $activity[] = ['time' => $k['scan_out'], 'action' => "Key {$k['log_number']} returned by {$k['full_name']}"];
            }
        }
        usort($activity, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));

        return view('safety/guard_dashboard', [
            'title' => 'Guard Dashboard',
            'openModule' => 'safety',
            'trip_tickets_json' => $this->jsonForScript(array_map(fn($t) => [
                'no'         => $t['trip_id'],
                'requester'  => $t['requester_name'],
                'vehicle'    => trim(($t['vehicle_model'] ?? '') . ' ' . ($t['vehicle_plate'] ?? '')) ?: 'Unassigned',
                'dest'       => $t['destination'],
                'dep'        => $t['departure_time'] ? date('h:i A', strtotime($t['departure_time'])) : '—',
                'approvedBy' => '—',
                'status'     => $t['status'],
            ], $releasedTrips)),
            'key_logs_json' => $this->jsonForScript(array_map(fn($k) => [
                'id'       => $k['log_number'],
                'name'     => $k['full_name'],
                'dept'     => $k['department'],
                'key'      => $k['key_item'],
                'inTime'   => date('h:i A', strtotime($k['scan_in'])),
                'status'   => $k['status'],
                'tripId'   => $k['trip_id'] ?? null,
            ], $keyLogs)),
            'activity_json' => $this->jsonForScript(array_map(fn($a) => [
                'time'   => date('h:i A', strtotime($a['time'])),
                'action' => $a['action'],
            ], array_slice($activity, 0, 10))),
        ]);
    }

    public function keylogs()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $keyLogs = $this->keyBorrowLogModel->getAllWithTrip();

        return view('safety/keylogs', [
            'title' => 'Keylogs',
            'openModule' => 'safety',
            'key_logs_json' => $this->jsonForScript(array_map(fn($k) => [
                'id'       => $k['log_number'],
                'name'     => $k['full_name'],
                'dept'     => $k['department'],
                'key'      => $k['key_item'],
                'issued'   => date('h:i A', strtotime($k['scan_in'])),
                'returned' => $k['scan_out'] ? date('h:i A', strtotime($k['scan_out'])) : '—',
                'status'   => $k['status'],
                'guard'    => $k['guard_on_duty'] ?? '—',
            ], $keyLogs)),
        ]);
    }
}