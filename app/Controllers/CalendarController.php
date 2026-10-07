<?php

namespace App\Controllers;

use App\Models\AirconUnitModel;
use App\Models\BorrowModel;
use App\Models\FireExtinguisherModel;
use App\Models\SafetyInspectionModel;
use App\Models\ToolsModel;
use App\Models\WorkOrderModel;
use App\Models\MechanicalEquipmentModel;
use App\Models\MotorpoolWorkOrderModel;
use App\Models\VehicleMaintenanceModel;
use App\Models\FloorPlanMarkerModel;
use App\Models\SafetyEquipmentModel;
use App\Models\VehicleModel;
use App\Models\UserModel;
use App\Models\JanitorialAssignmentModel;
use App\Models\JanitorialTaskModel;
use App\Models\NotificationModel;
use App\Models\PersonnelModel;
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
        'Installed'       => '#0d9488',
        'Check Due'       => '#f59e0b',
        'Expires'         => '#be123c',
        'Borrowed'        => '#0ea5e9',
        'Due Back'        => '#e11d48',
    ];

    private function roleKey(): string
    {
        return strtolower((string) $this->session->get('role'));
    }

    // Each department's calendar only carries its own schedule. Administrators (and any other role) see everything.
    private function sources(): array
    {
        return match ($this->roleKey()) {
            'facilities' => ['cleaning', 'maintenance', 'facilities'],
            'janitorial' => ['cleaning'],
            'security'   => ['safety'],
            'assets'     => ['travel', 'asset'],
            'sports'     => ['sports'],
            default      => ['cleaning', 'maintenance', 'travel', 'safety', 'asset', 'sports', 'facilities'],
        };
    }

    private function has(string $source): bool
    {
        return in_array($source, $this->sources(), true);
    }

    private function isSecurity(): bool
    {
        return $this->roleKey() === 'security';
    }

    private function isAssets(): bool
    {
        return $this->roleKey() === 'assets';
    }

    private function hideCleaning(): bool
    {
        return !$this->has('cleaning');
    }

    private function showSafety(): bool
    {
        return $this->has('safety');
    }

    // Legend + "Type" choices for this department's calendar.
    private function legendFor(): array
    {
        $all = [
            'Inspection' => '#f59e0b', 'Maintenance' => '#7c3aed', 'Compliance' => '#2563eb', 'Cleaning' => '#16a34a', 'Urgent Cleaning' => '#dc2626',
            'Travel' => '#0891b2', 'Check Due' => '#f59e0b', 'Installed' => '#0d9488', 'Expires' => '#be123c', 'Borrowed' => '#0ea5e9', 'Due Back' => '#e11d48',
        ];
        $names = match ($this->roleKey()) {
            'facilities' => ['Maintenance', 'Cleaning', 'Urgent Cleaning', 'Check Due'],
            'janitorial' => ['Cleaning', 'Urgent Cleaning'],
            'security'   => ['Inspection', 'Installed', 'Check Due', 'Expires'],
            'assets'     => ['Maintenance', 'Travel', 'Check Due'],
            'sports'     => ['Borrowed', 'Due Back'],
            default      => array_keys($all),
        };

        return array_map(fn($n) => ['label' => $n, 'color' => $all[$n]], $names);
    }

    private function typesFor(): array
    {
        return match ($this->roleKey()) {
            'facilities' => ['Inspection', 'Maintenance', 'Compliance', 'Cleaning', 'Urgent Cleaning'],
            'janitorial' => ['Cleaning', 'Urgent Cleaning'],
            'security', 'assets', 'sports' => ['Inspection', 'Compliance'],
            default      => ['Inspection', 'Maintenance', 'Compliance', 'Cleaning', 'Urgent Cleaning'],
        };
    }

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

        $personnel = new PersonnelModel();

        $data = [
            'title'            => 'Operations Calendar',
            'events_json'      => $this->jsonForScript($this->persistedEvents()),
            'flash_success'    => $this->session->getFlashdata('success'),
            'pending_renewals' => $this->pendingVehicleRenewals(),
            'is_security'      => $this->isSecurity(),
            'hide_cleaning'    => $this->hideCleaning(),
            'is_assets'        => $this->isAssets(),
            'show_safety'      => $this->showSafety(),
            'legend'           => $this->legendFor(),
            'summary_modules'  => \App\Libraries\DepartmentScope::options((string) $this->session->get('role')),
            'event_types'      => $this->typesFor(),
            'renewals'         => $this->roleKey() === 'administrator' || !in_array($this->roleKey(), ['security', 'assets', 'sports', 'facilities', 'janitorial'], true) ? null : $this->renewalsFor(),
            'upcoming_events'  => $this->upcomingEvents(),
            'can_driver'       => $this->has('travel'),
            'can_cleaning'     => $this->has('cleaning'),
            'can_maintenance'  => $this->has('maintenance'),
            // Real people, with contact numbers when on file — power the
            // "Notify Driver" / "Notify Cleaning Personnel" suggested-action
            // pickers so they actually target someone instead of a hardcoded
            // "Van-03 driver" placeholder.
            'drivers_json'     => $this->jsonForScript($personnel->getActiveByPositionLike('Driver')),
            'janitors_json'    => $this->jsonForScript($personnel->getActiveByPositionLike(['Janitor', 'Cleaning'])),
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

        if ($this->hideCleaning()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Cleaning schedules are not part of this department.']);
        }

        $data      = $this->request->getJSON(true) ?? [];
        $zone      = trim((string) ($data['zone'] ?? ''));
        // 'date' is kept as a fallback so nothing else calling this endpoint
        // with the old single-date shape breaks.
        $startDate = trim((string) ($data['startDate'] ?? $data['date'] ?? ''));
        $endDate   = trim((string) ($data['endDate'] ?? $startDate));
        $urgent    = !empty($data['urgent']);
        $notes     = trim((string) ($data['notes'] ?? ''));

        if ($zone === '' || $startDate === '' || $endDate === '') {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Building/zone, start date, and end date are required.']);
        }

        $startTs = strtotime($startDate);
        $endTs   = strtotime($endDate);
        if ($startTs === false || $endTs === false) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid date.']);
        }
        if ($endTs < $startTs) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'End date can\'t be before the start date.']);
        }
        // A cleaning schedule spanning more than 2 months is almost certainly
        // a mistyped date, not a real multi-day job — refuse rather than
        // silently creating dozens of assignments.
        $spanDays = (int) round(($endTs - $startTs) / 86400) + 1;
        if ($spanDays > 60) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Date range is too long (max 60 days).']);
        }

        $janitor   = (new UserModel())->getByEmployeeId(self::JANITORIAL_EMP_ID);
        $staffName = $janitor['full_name'] ?? 'Janitorial Staff';

        $assignmentModel = new JanitorialAssignmentModel();
        $taskModel       = new JanitorialTaskModel();
        $events          = [];

        // One real Janitorial Monitoring assignment per day in the range —
        // each day stays independently trackable/completable there, same as
        // if the admin had added them one at a time.
        for ($ts = $startTs; $ts <= $endTs; $ts += 86400) {
            $day = date('Y-m-d', $ts);

            $assignmentId = $assignmentModel->insert([
                'staff_name'    => $staffName,
                'assigned_zone' => $zone,
                'shift_start'   => $urgent ? date('H:i:s') : '08:00:00',
                'shift_end'     => '17:00:00',
                'date_assigned' => $day,
                'status'        => 'Active',
                'priority'      => $urgent ? 'Urgent' : 'Routine',
            ]);

            $taskModel->insert([
                'assignment_id' => $assignmentId,
                'task_name'     => ($urgent ? 'Urgent Cleaning: ' : 'Scheduled Cleaning: ') . $zone,
                'is_done'       => 0,
            ]);

            $events[] = $this->toEvent($assignmentId, $zone, $day, $urgent, $staffName);
        }

        $niceStart = date('M j, Y', $startTs);
        $niceEnd   = date('M j, Y', $endTs);
        $dateRangeText = $spanDays > 1 ? "from {$niceStart} to {$niceEnd}" : "on {$niceStart}";

        (new NotificationModel())->insert([
            'category'    => $urgent ? 'Urgent Cleaning Scheduled' : 'Cleaning Scheduled',
            'description' => ($urgent ? "Urgent cleaning" : 'Cleaning') . " scheduled for {$zone} {$dateRangeText}." . ($notes !== '' ? " Notes: {$notes}" : ''),
            'recipient'   => $staffName,
            'priority'    => $urgent ? 'CRITICAL' : 'ROUTINE',
            'status'      => 'Pending',
            'is_read'     => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity('Janitorial', ($urgent ? 'Urgent cleaning' : 'Cleaning') . " scheduled for {$zone} {$dateRangeText}");

        return $this->response->setJSON([
            'success' => true,
            'message' => ($urgent ? 'Urgent cleaning' : 'Cleaning') . " scheduled for {$zone} {$dateRangeText} — {$staffName} notified.",
            'events'  => $events,
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

        $this->logActivity('Safety', "Logged work order {$woNumber} — {$issue} at {$location}");

        return $this->response->setJSON([
            'success' => true,
            'message' => "Maintenance work order {$woNumber} logged for {$location} — Maintenance Team notified.",
            'event'   => $this->toMaintenanceEvent($id, $issue, $location, $date),
        ]);
    }

    private function persistedEvents(): array
    {
        $cleaning = $this->hideCleaning() ? [] : array_map(fn($a) => $this->toEvent(
            $a['id'],
            $a['assigned_zone'],
            $a['date_assigned'],
            ($a['priority'] ?? null) === 'Urgent',
            $a['staff_name']
        ), (new JanitorialAssignmentModel())->findAll());

        $maintenance = !$this->has('maintenance') ? [] : array_map(fn($w) => $this->toMaintenanceEvent(
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
        $travel = !$this->has('travel') ? [] : array_map(fn($t) => $this->toTravelEvent($t), $trips);

        return array_merge($cleaning, $maintenance, $travel, $this->has('safety') ? $this->safetyEvents() : [], $this->has('asset') ? $this->assetEvents() : [], $this->has('sports') ? $this->sportsEvents() : [], $this->has('facilities') ? $this->facilitiesEvents() : []);
    }

    private function upcomingEvents(): array
    {
        $today = date('Y-m-d');
        $list = array_values(array_filter($this->persistedEvents(), fn($e) => substr((string) $e['start'], 0, 10) >= $today && ($e['extendedProps']['type'] ?? '') !== 'Installed'));
        usort($list, fn($a, $b) => strcmp((string) $a['start'], (string) $b['start']));

        return array_slice($list, 0, 5);
    }

    // The "Pending Renewals" card, per department: what has run out or is about to.
    private function renewalsFor(): array
    {
        $role = $this->roleKey();
        $today = date('Y-m-d');
        $soon = date('Y-m-d', strtotime('+7 days'));

        if ($role === 'security') {
            return ['title' => 'Pending Renewals', 'empty' => 'No fire safety renewals pending.', 'url' => 'security-dept/fire-safety',
                'items' => array_map(fn($r) => ['title' => $r['title'], 'sub' => $r['place'] . ' · ' . $r['label']], $this->safetyRenewals())];
        }
        if ($role === 'assets') {
            return ['title' => 'Pending Renewals', 'empty' => 'No vehicle renewals pending.', 'url' => 'assets-dept/vehicles',
                'items' => array_map(fn($v) => ['title' => $v['vehicle_name'] . ' (' . $v['plate_no'] . ')', 'sub' => 'Inspection: ' . $v['inspection_status']], $this->pendingVehicleRenewals())];
        }
        if ($role === 'sports') {
            $tools = [];
            foreach ((new ToolsModel())->where('category', 'Sports Equipment')->findAll() as $t) $tools[$t['id']] = $t['asset_name'];
            $items = [];
            if ($tools) {
                foreach ((new BorrowModel())->whereIn('tool_id', array_keys($tools))->where('status', 'Borrowed')->where('expected_return <=', $soon)->orderBy('expected_return', 'ASC')->findAll() as $b) {
                    $items[] = ['title' => $tools[$b['tool_id']], 'sub' => ($b['borrower'] ?: '—') . ' · ' . ($b['expected_return'] < $today ? 'Overdue since ' : 'Due back ') . date('M j, Y', strtotime($b['expected_return']))];
                }
            }
            return ['title' => 'Returns Due', 'empty' => 'No returns overdue or due soon.', 'url' => 'sports-dept/borrowing', 'items' => $items];
        }
        if ($role === 'facilities') {
            $items = [];
            foreach ((new AirconUnitModel())->where('next_schedule <=', $soon)->where('next_schedule IS NOT NULL', null, false)->orderBy('next_schedule', 'ASC')->limit(8)->findAll() as $a) {
                $items[] = ['title' => $a['unit_name'], 'sub' => $a['location'] . ' · ' . ($a['next_schedule'] < $today ? 'Cleaning overdue since ' : 'Cleaning due ') . date('M j, Y', strtotime($a['next_schedule']))];
            }
            return ['title' => 'Aircon Cleaning Due', 'empty' => 'No aircon cleaning overdue or due soon.', 'url' => 'facilities/aircon', 'items' => $items];
        }

        return ['title' => 'Pending Renewals', 'empty' => 'Nothing pending.', 'url' => '', 'items' => []];
    }

    // Aircon cleaning dates and open repair requests — Facilities Administration and General Services.
    private function facilitiesEvents(): array
    {
        $events = [];
        foreach ((new AirconUnitModel())->where('next_schedule IS NOT NULL', null, false)->findAll() as $a) {
            $color = self::CATEGORY_COLORS['Check Due'];
            $events[] = [
                'id' => 'fa-ac-' . $a['id'], 'title' => '❄️ Check Due — ' . $a['unit_name'], 'start' => $a['next_schedule'],
                'backgroundColor' => $color, 'borderColor' => $color,
                'extendedProps' => ['type' => 'Check Due', 'zone' => $a['location'] . ' — ' . $a['floor'], 'purpose' => 'Aircon cleaning: ' . $a['unit_name'], 'status' => $a['condition_status']],
            ];
        }
        foreach ((new WorkOrderModel())->whereIn('status', ['Pending', 'In Progress'])->findAll() as $w) {
            $color = self::CATEGORY_COLORS['Maintenance'];
            $events[] = [
                'id' => 'fa-wo-' . $w['id'], 'title' => '🔧 Maintenance — ' . $w['title'], 'start' => substr($w['created_at'], 0, 10),
                'backgroundColor' => $color, 'borderColor' => $color,
                'extendedProps' => ['type' => 'Maintenance', 'zone' => $w['building'] . ($w['floor'] ? ' — ' . $w['floor'] : ''), 'purpose' => $w['details'] ?: $w['title'], 'status' => $w['status'], 'assignedTo' => 'Facilities'],
            ];
        }

        return $events;
    }

    // Sports equipment going out and due back.
    private function sportsEvents(): array
    {
        $events = [];
        $tools = [];
        foreach ((new ToolsModel())->where('category', 'Sports Equipment')->findAll() as $t) $tools[$t['id']] = $t['asset_name'];
        if (!$tools) return [];
        foreach ((new BorrowModel())->whereIn('tool_id', array_keys($tools))->where('is_archived', 0)->findAll() as $b) {
            $item = $tools[$b['tool_id']];
            $color = self::CATEGORY_COLORS['Borrowed'];
            $events[] = [
                'id' => 'sp-b-' . $b['id'], 'title' => '🏀 Borrowed — ' . $item, 'start' => $b['borrowed_date'],
                'backgroundColor' => $color, 'borderColor' => $color,
                'extendedProps' => ['type' => 'Borrowed', 'zone' => $b['department'] ?: 'Sports', 'purpose' => $item . ' borrowed by ' . ($b['borrower'] ?: '—'), 'status' => $b['status']],
            ];
            if ($b['status'] === 'Borrowed' && $b['expected_return']) {
                $due = self::CATEGORY_COLORS['Due Back'];
                $events[] = [
                    'id' => 'sp-d-' . $b['id'], 'title' => '⏰ Due Back — ' . $item, 'start' => $b['expected_return'],
                    'backgroundColor' => $due, 'borderColor' => $due,
                    'extendedProps' => ['type' => 'Due Back', 'zone' => $b['department'] ?: 'Sports', 'purpose' => $item . ' due back from ' . ($b['borrower'] ?: '—'), 'status' => 'Borrowed'],
                ];
            }
        }

        return $events;
    }

    // Safety & Security's own "renewals": fire safety items that have expired or expire within 30 days.
    private function safetyRenewals(): array
    {
        $limit = date('Y-m-d', strtotime('+30 days'));
        $out = [];
        foreach ((new FireExtinguisherModel())->where('expires_on <=', $limit)->findAll() as $e) {
            $out[] = ['title' => 'Fire Extinguisher ' . $e['unit_id'], 'place' => $e['location'] . ' — ' . $e['floor'], 'date' => $e['expires_on']];
        }
        foreach ((new SafetyEquipmentModel())->where('expires_on <=', $limit)->findAll() as $e) {
            $out[] = ['title' => $e['equipment_type'] . ' ' . $e['code'], 'place' => $e['building'] . ($e['floor'] ? ' — ' . $e['floor'] : ''), 'date' => $e['expires_on']];
        }
        usort($out, fn($a, $b) => strcmp($a['date'], $b['date']));
        $today = date('Y-m-d');
        foreach ($out as &$o) {
            $o['expired'] = $o['date'] < $today;
            $o['label'] = ($o['expired'] ? 'Expired ' : 'Expires ') . date('M j, Y', strtotime($o['date']));
        }

        return array_slice($out, 0, 8);
    }

    // Next few fire safety checks that are coming up.
    private function safetyUpcoming(): array
    {
        $today = date('Y-m-d');
        $out = [];
        foreach ($this->safetyEvents() as $e) {
            if ($e['start'] >= $today && $e['extendedProps']['type'] !== 'Installed') $out[] = $e;
        }
        usort($out, fn($a, $b) => strcmp($a['start'], $b['start']));

        return array_slice($out, 0, 5);
    }

    // Asset Acquisition and Monitoring: open motor pool work orders and upcoming vehicle / equipment service dates.
    private function assetEvents(): array
    {
        $events = [];
        $push = function (string $kind, string $title, string $date, string $zone, string $purpose, ?string $status = null) use (&$events) {
            if ($date === '') return;
            $color = self::CATEGORY_COLORS[$kind];
            $events[] = [
                'id' => 'as-' . count($events), 'title' => $title, 'start' => $date,
                'backgroundColor' => $color, 'borderColor' => $color,
                'extendedProps' => ['type' => $kind, 'zone' => $zone, 'purpose' => $purpose, 'status' => $status],
            ];
        };

        $vehicles = [];
        foreach ((new VehicleModel())->findAll() as $v) $vehicles[$v['id']] = $v['vehicle_name'] . ' (' . $v['plate_no'] . ')';
        $equipment = [];
        foreach ((new MechanicalEquipmentModel())->findAll() as $e) $equipment[$e['id']] = $e['code'] . ' — ' . $e['name'];

        foreach ((new MotorpoolWorkOrderModel())->whereIn('status', ['Pending', 'In Progress'])->findAll() as $w) {
            $item = $w['wo_type'] === 'Vehicle Repair' ? ($vehicles[$w['vehicle_id']] ?? '—') : ($equipment[$w['equipment_id']] ?? '—');
            $push('Maintenance', '🔧 ' . $w['wo_number'] . ' — ' . $item, substr($w['created_at'], 0, 10), $item, $w['issue'], $w['status']);
        }

        $latest = [];
        foreach ((new VehicleMaintenanceModel())->orderBy('serviced_on', 'DESC')->findAll() as $m) {
            $latest[$m['vehicle_id']][$m['service_type']] ??= $m['next_due'];
        }
        foreach ($latest as $vid => $types) {
            foreach ($types as $service => $due) {
                if ($due) $push('Check Due', '🚐 Check Due — ' . ($vehicles[$vid] ?? 'Vehicle'), $due, $vehicles[$vid] ?? 'Vehicle', $service);
            }
        }
        foreach ((new MechanicalEquipmentModel())->findAll() as $e) {
            if ($e['next_service']) $push('Check Due', '⚙️ Check Due — ' . $e['code'], $e['next_service'], $e['location'] ?: 'Motor Pool', $e['name'] . ' service');
        }

        return $events;
    }

    // Fire Safety dates: when each item was installed, when its next check is due, and when it expires.
    private function safetyEvents(): array
    {
        $events = [];
        $add = function (string $kind, string $icon, string $date, string $code, string $equipment, string $building, ?string $floor) use (&$events) {
            if ($date === '') return;
            $color = self::CATEGORY_COLORS[$kind];
            $events[] = [
                'id'              => 'fs-' . count($events),
                'title'           => "{$icon} {$kind} — {$code}",
                'start'           => $date,
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'extendedProps'   => [
                    'type'    => $kind,
                    'zone'    => $building . ($floor ? " — {$floor}" : ''),
                    'purpose' => "{$equipment} {$code}",
                ],
            ];
        };

        foreach ((new FireExtinguisherModel())->findAll() as $e) {
            $add('Installed', '🧯', (string) $e['installed_on'], $e['unit_id'], 'Fire Extinguisher', $e['location'], $e['floor']);
            $add('Check Due', '🧯', (string) $e['next_due'], $e['unit_id'], 'Fire Extinguisher', $e['location'], $e['floor']);
            $add('Expires', '🧯', (string) $e['expires_on'], $e['unit_id'], 'Fire Extinguisher', $e['location'], $e['floor']);
        }
        foreach ((new SafetyEquipmentModel())->findAll() as $e) {
            $icon = $e['equipment_type'] === 'Smoke Detector' ? '💨' : ($e['equipment_type'] === 'Fire Alarm' ? '🚨' : '🚪');
            $add('Installed', $icon, (string) $e['installed_on'], $e['code'], $e['equipment_type'], $e['building'], $e['floor']);
            $add('Check Due', $icon, (string) $e['next_check'], $e['code'], $e['equipment_type'], $e['building'], $e['floor']);
            $add('Expires', $icon, (string) $e['expires_on'], $e['code'], $e['equipment_type'], $e['building'], $e['floor']);
        }
        foreach ((new SafetyInspectionModel())->findAll() as $i) {
            $color = self::CATEGORY_COLORS['Inspection'];
            $events[] = [
                'id' => 'fs-in-' . $i['id'], 'title' => '📋 Inspection — ' . $i['building'], 'start' => substr((string) $i['inspected_at'], 0, 10),
                'backgroundColor' => $color, 'borderColor' => $color,
                'extendedProps' => ['type' => 'Inspection', 'zone' => $i['building'], 'purpose' => 'Safety inspection: ' . $i['safety_status'], 'status' => $i['safety_status']],
            ];
        }
        foreach ((new FloorPlanMarkerModel())->findAll() as $m) {
            if (!in_array($m['equipment_type'], ['Fire Extinguisher', 'Smoke Detector'], true)) continue;
            $add('Expires', $m['equipment_type'] === 'Smoke Detector' ? '💨' : '🧯', (string) $m['expires_on'], $m['label'] ?: 'Plan marker #' . $m['id'], $m['equipment_type'], 'Floor plan', null);
        }

        return $events;
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