<?php

namespace App\Controllers;

use App\Models\VehicleModel;
use App\Models\UserModel;
use App\Models\JanitorialAssignmentModel;
use App\Models\JanitorialTaskModel;
use App\Models\NotificationModel;
use App\Models\SafetyWorkOrderModel;
use App\Models\TravelModel;

class CalendarController extends BaseController
{
    protected $session;

    // Must match the legend rendered in calendar/index.php exactly, 1:1 by category.
    private const CATEGORY_COLORS = [
        'Inspection'      => '#f59e0b',
        'Maintenance'     => '#7c3aed',
        'Compliance'      => '#2563eb',
        'Cleaning'        => '#16a34a',
        'Urgent Cleaning' => '#dc2626',
        'Travel'          => '#0891b2',
    ];

    // The only Janitorial-linked account today — cleaning schedules created
    // from the Calendar are assigned to, and notify, this account.
    private const JANITORIAL_EMP_ID = '10001';

    public function __construct()
    {
        $this->session = \Config\Services::session();
    }

    public function index()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $data = [
            'title'            => 'Operations Calendar',
            'events_json'      => $this->jsonForScript($this->persistedEvents()),
            'flash_success'    => $this->session->getFlashdata('success'),
            'pending_renewals' => $this->pendingVehicleRenewals(),
        ];

        return view('calendar/index', $data);
    }

    // Real renewals due for review — vehicles whose inspection is expired or
    // due soon. Replaces the old hardcoded "Vehicle Insurance Renewal"
    // placeholder (there's no insurance-expiry field tracked in this system,
    // inspection_status is the real equivalent already recorded per vehicle).
    private function pendingVehicleRenewals(): array
    {
        return (new VehicleModel())
            ->whereIn('inspection_status', ['Expired', 'Due Soon'])
            ->where('is_archived', 0)
            ->orderBy('inspection_status', 'ASC')
            ->findAll();
    }

    public function add()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');
        // Local calendar events — stored in session for now
        $this->session->setFlashdata('success', 'Event added to calendar.');
        return redirect()->to('/calendar');
    }

    // Manual "Notify" button on the event detail panel — sends a real
    // notification to whoever is actually assigned to that event (the
    // janitorial staff on a cleaning event, the Maintenance Team on a work
    // order, the driver on a trip), instead of a hardcoded "Notify Driver"
    // that didn't match the event and didn't do anything.
    public function notify()
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $data      = $this->request->getJSON(true) ?? [];
        $recipient = trim((string) ($data['recipient'] ?? ''));
        $title     = trim((string) ($data['title'] ?? ''));
        $category  = trim((string) ($data['category'] ?? 'Calendar'));

        if ($recipient === '' || $title === '') {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Missing recipient or event details.']);
        }

        (new NotificationModel())->insert([
            'category'    => $category . ' Reminder',
            'description' => "Reminder: {$title}",
            'recipient'   => $recipient,
            'priority'    => 'ROUTINE',
            'status'      => 'Pending',
            'is_read'     => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['success' => true, 'message' => "Notification sent to {$recipient}."]);
    }

    public function events()
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON([]);
        }

        return $this->response->setJSON($this->persistedEvents());
    }

    // Cleaning / Urgent Cleaning scheduled from the Calendar — creates a real
    // Janitorial Monitoring assignment (with a starter task) for the chosen
    // zone/date, and notifies the Janitorial account, so the schedule is
    // actually connected instead of being a calendar-only note.
    public function scheduleCleaning()
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $data   = $this->request->getJSON(true) ?? [];
        $zone   = trim((string) ($data['zone'] ?? ''));
        $date   = trim((string) ($data['date'] ?? ''));
        $urgent = !empty($data['urgent']);
        $notes  = trim((string) ($data['notes'] ?? ''));

        if ($zone === '' || $date === '') {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Building/zone and date are required.']);
        }

        $janitor   = (new UserModel())->getByEmployeeId(self::JANITORIAL_EMP_ID);
        $staffName = $janitor['full_name'] ?? 'Janitorial Staff';

        $assignmentModel = new JanitorialAssignmentModel();
        $assignmentId = $assignmentModel->insert([
            'staff_name'    => $staffName,
            'assigned_zone' => $zone,
            'shift_start'   => $urgent ? date('H:i:s') : '08:00:00',
            'shift_end'     => '17:00:00',
            'date_assigned' => $date,
            'status'        => 'Active',
            'priority'      => $urgent ? 'Urgent' : 'Routine',
        ]);

        (new JanitorialTaskModel())->insert([
            'assignment_id' => $assignmentId,
            'task_name'     => ($urgent ? 'Urgent Cleaning: ' : 'Scheduled Cleaning: ') . $zone,
            'is_done'       => 0,
        ]);

        $niceDate = date('M j, Y', strtotime($date));
        (new NotificationModel())->insert([
            'category'    => $urgent ? 'Urgent Cleaning Scheduled' : 'Cleaning Scheduled',
            'description' => ($urgent ? "Urgent cleaning" : 'Cleaning') . " scheduled for {$zone} on {$niceDate}." . ($notes !== '' ? " Notes: {$notes}" : ''),
            'recipient'   => $staffName,
            'priority'    => $urgent ? 'CRITICAL' : 'ROUTINE',
            'status'      => 'Pending',
            'is_read'     => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => ($urgent ? 'Urgent cleaning' : 'Cleaning') . " scheduled for {$zone} — {$staffName} notified.",
            'event'   => $this->toEvent($assignmentId, $zone, $date, $urgent, $staffName),
        ]);
    }

    // Maintenance scheduled from the Calendar — logs a real Safety work order
    // (same table the Safety Maintenance dashboard's stats already read from)
    // and notifies the Maintenance Team, so the schedule is actually
    // connected instead of being a calendar-only note.
    public function scheduleMaintenance()
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $data     = $this->request->getJSON(true) ?? [];
        $location = trim((string) ($data['location'] ?? ''));
        $date     = trim((string) ($data['date'] ?? ''));
        $issue    = trim((string) ($data['issue'] ?? ''));
        $notes    = trim((string) ($data['notes'] ?? ''));

        if ($location === '' || $date === '' || $issue === '') {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Building, date, and a short description are required.']);
        }

        $woModel  = new SafetyWorkOrderModel();
        $woNumber = 'WO-' . str_pad((string) ($woModel->countAllResults() + 1), 3, '0', STR_PAD_LEFT);
        $reportedBy = $this->session->get('full_name') ?: 'Facilities';

        $id = $woModel->insert([
            'wo_number'   => $woNumber,
            'issue'       => $issue,
            'location'    => $location,
            'reported_by' => $reportedBy,
            'assigned_to' => 'Maintenance Team',
            'priority'    => 'Medium',
            'stage'       => 'Issue Logged',
            'date_logged' => $date,
            'notes'       => $notes ?: null,
        ]);

        $niceDate = date('M j, Y', strtotime($date));
        (new NotificationModel())->insert([
            'category'    => 'Maintenance Scheduled',
            'description' => "{$issue} — {$woNumber} logged for {$location} on {$niceDate}." . ($notes !== '' ? " Notes: {$notes}" : ''),
            'recipient'   => 'Maintenance Team',
            'priority'    => 'ROUTINE',
            'status'      => 'Pending',
            'is_read'     => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => "Maintenance work order {$woNumber} logged for {$location} — Maintenance Team notified.",
            'event'   => $this->toMaintenanceEvent($id, $issue, $location, $date),
        ]);
    }

    private function persistedEvents(): array
    {
        $cleaning = array_map(fn($a) => $this->toEvent(
            $a['id'],
            $a['assigned_zone'],
            $a['date_assigned'],
            ($a['priority'] ?? null) === 'Urgent',
            $a['staff_name']
        ), (new JanitorialAssignmentModel())->findAll());

        $maintenance = array_map(fn($w) => $this->toMaintenanceEvent(
            $w['id'],
            $w['issue'],
            $w['location'],
            $w['date_logged']
        ), (new SafetyWorkOrderModel())->findAll());

        // Trip tickets — every non-archived request that hasn't been turned
        // down, so the calendar shows what's actually still scheduled to
        // happen (Submitted through Completed), not dead requests.
        $trips = array_filter(
            (new TravelModel())->getAllWithDetails(),
            fn($t) => !in_array($t['status'], ['Rejected', 'Cancelled'], true)
        );
        $travel = array_map(fn($t) => $this->toTravelEvent($t), $trips);

        return array_merge($cleaning, $maintenance, $travel);
    }

    private function toTravelEvent(array $trip): array
    {
        $color = self::CATEGORY_COLORS['Travel'];

        return [
            'id'              => 'trip-' . $trip['id'],
            'title'           => '🚐 Travel — ' . $trip['destination'],
            'start'           => $trip['travel_date'],
            'backgroundColor' => $color,
            'borderColor'     => $color,
            'extendedProps'   => [
                'type'       => 'Travel',
                'zone'       => $trip['destination'],
                'purpose'    => $trip['purpose'],
                'requester'  => $trip['requester_name'] ?? null,
                'assignedTo' => $trip['driver_name'] ?? 'Unassigned driver',
                'status'     => $trip['status'],
            ],
        ];
    }

    private function toMaintenanceEvent(int $id, string $issue, string $location, string $date): array
    {
        $color = self::CATEGORY_COLORS['Maintenance'];

        return [
            'id'              => 'wo-' . $id,
            'title'           => '🔧 Maintenance — ' . $location,
            'start'           => $date,
            'backgroundColor' => $color,
            'borderColor'     => $color,
            'extendedProps'   => [
                'type'       => 'Maintenance',
                'zone'       => $location,
                'purpose'    => $issue,
                'assignedTo' => 'Maintenance Team',
            ],
        ];
    }

    private function toEvent(int $id, string $zone, string $date, bool $urgent, string $staffName): array
    {
        $type  = $urgent ? 'Urgent Cleaning' : 'Cleaning';
        $color = self::CATEGORY_COLORS[$type];

        return [
            'id'              => 'jan-' . $id,
            'title'           => ($urgent ? '🧹 Urgent Cleaning — ' : '🧹 Cleaning — ') . $zone,
            'start'           => $date,
            'backgroundColor' => $color,
            'borderColor'     => $color,
            'extendedProps'   => [
                'type'       => $type,
                'zone'       => $zone,
                'assignedTo' => $staffName,
            ],
        ];
    }
}
?>