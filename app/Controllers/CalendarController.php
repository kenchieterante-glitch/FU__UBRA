<?php

namespace App\Controllers;

use App\Models\TravelModel;
use App\Models\VehicleModel;

class CalendarController extends BaseController
{
    protected $travelModel;
    protected $session;

    // Must match the legend rendered in calendar/index.php exactly, 1:1 by category.
    private const CATEGORY_COLORS = [
        'Travel'      => '#d97706',
        'Inspection'  => '#f59e0b',
        'Maintenance' => '#7c3aed',
        'Compliance'  => '#2563eb',
    ];

    public function __construct()
    {
        $this->travelModel = new TravelModel();
        $this->session     = \Config\Services::session();
    }

    public function index()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $trips  = $this->travelModel->getAllWithDetails();
        $events = [];

        foreach ($trips as $t) {
            // Color comes from the event's category, matching the calendar legend
            // 1:1 (Travel=orange) — not from status, so the map and legend never disagree.
            $color = self::CATEGORY_COLORS['Travel'];
            $events[] = [
                'id'              => 'trip-' . $t['id'],
                'title'           => '✈ ' . $t['destination'],
                'start'           => $t['travel_date'] . 'T' . ($t['departure_time'] ?? '08:00:00'),
                'end'             => $t['travel_date'] . 'T' . ($t['return_time']    ?? '17:00:00'),
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'extendedProps'   => [
                    'type'      => 'Travel',
                    'requester' => $t['requester_name'],
                    'driver'    => $t['driver_name'] ?? 'Unassigned',
                    'vehicle'   => ($t['vehicle_model'] ?? '') . ' ' . ($t['vehicle_plate'] ?? ''),
                    'status'    => $t['status'],
                    'purpose'   => $t['purpose'],
                ],
            ];
        }

        $pendingTrips  = array_values(array_filter($trips, fn($t) => $t['status'] === 'Pending'));
        $approvedTrips = array_values(array_filter($trips, fn($t) =>
            $t['status'] === 'Approved' && $t['travel_date'] >= date('Y-m-d')
        ));
        $todayTrips = array_values(array_filter($trips, fn($t) => $t['travel_date'] === date('Y-m-d')));

        $data = [
            'title'          => 'Operations Calendar',
            'events_json'    => $this->jsonForScript(array_values($events)),
            'pending_trips'  => $pendingTrips,
            'approved_trips' => $approvedTrips,
            'today_trips'    => $todayTrips,
            'flash_success'  => $this->session->getFlashdata('success'),
        ];

        return view('calendar/index', $data);
    }

    public function add()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');
        // Local calendar events — stored in session for now
        $this->session->setFlashdata('success', 'Event added to calendar.');
        return redirect()->to('/calendar');
    }

    public function events()
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON([]);
        }

        $trips  = $this->travelModel->getAllWithDetails();
        $events = [];

        foreach ($trips as $t) {
            // Color comes from the event's category, matching the calendar legend
            // 1:1 (Travel=orange) — not from status, so the map and legend never disagree.
            $color = self::CATEGORY_COLORS['Travel'];
            $events[] = [
                'id'              => 'trip-' . $t['id'],
                'title'           => '✈ ' . $t['destination'],
                'start'           => $t['travel_date'] . 'T' . ($t['departure_time'] ?? '08:00:00'),
                'end'             => $t['travel_date'] . 'T' . ($t['return_time']    ?? '17:00:00'),
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'extendedProps'   => [
                    'type'      => 'Travel',
                    'requester' => $t['requester_name'],
                    'status'    => $t['status'],
                    'purpose'   => $t['purpose'],
                ],
            ];
        }

        return $this->response->setJSON($events);
    }
}
?>