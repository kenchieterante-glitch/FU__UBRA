<?php

namespace App\Controllers;

use App\Models\MechanicalEquipmentModel;
use App\Models\MotorpoolWorkOrderModel;
use App\Models\PersonnelModel;
use App\Models\TravelModel;
use App\Models\VehicleMaintenanceModel;
use App\Models\VehicleModel;

// Asset Acquisition and Monitoring Department: Vehicle & Motor Pool, Motor Pool Work Orders, Trip Tickets.
class AssetDeptController extends BaseController
{
    private const SECTIONS = [
        'vehicles'     => 'Vehicle and Motor Pool',
        'work-orders'  => 'Motor Pool Work Orders',
        'trip-tickets' => 'Trip Tickets',
    ];

    private function fmtDate($d): string
    {
        return !empty($d) ? date('M d, Y', strtotime($d)) : '—';
    }

    // 'OK' | 'Service Due Soon' | 'Service Overdue' for a next-service date.
    private function serviceState(?string $next): string
    {
        if (empty($next)) return 'OK';
        if ($next < date('Y-m-d')) return 'Service Overdue';
        if ($next <= date('Y-m-d', strtotime('+14 days'))) return 'Service Due Soon';
        return 'OK';
    }

    private function vehicleLabel(array $v): string
    {
        return $v['vehicle_name'] . ' (' . $v['plate_no'] . ')';
    }

    private function raw(string $html): array
    {
        return ['raw' => $html];
    }

    // Active personnel whose position is a driver (raw rows).
    private function drivers(): array
    {
        return (new PersonnelModel())->where('is_archived', 0)->like('position', 'Driver')->orderBy('full_name', 'ASC')->findAll();
    }

    // A notification for the Asset Acquisition and Monitoring account (shown in its Notification Center).
    private function notify(string $category, string $text, string $priority = 'ROUTINE'): void
    {
        (new \App\Models\NotificationModel())->insert([
            'category'    => $category,
            'description' => $text,
            'recipient'   => 'Motor Pool',
            'priority'    => $priority,
            'status'      => 'Pending',
            'is_read'     => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    private function me(): string
    {
        return (string) (session()->get('full_name') ?? 'Motor Pool');
    }

    // ---------------------------------------------------------------- pages

    public function overview()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $v = $this->vehiclesData();
        $w = $this->workOrdersData();
        $t = $this->tripsData();
        $pick = [
            'ad_veh'   => ['Vehicles', 'v_total', 'bi-truck', 'blue', $v],
            'ad_avail' => ['Available Vehicles', 'v_avail', 'bi-check-circle-fill', 'green', $v],
            'ad_alert' => ['Vehicles Needing Attention', 'v_alert', 'bi-exclamation-triangle-fill', 'red', $v],
            'ad_wo'    => ['Open Work Orders', 'w_open', 'bi-wrench-adjustable', 'gold', $w],
            'ad_trips' => ['Trips Today', 't_today', 'bi-signpost-2-fill', 'maroon', $t],
            'ad_equip' => ['Equipment Needing Repair', 'e_attn', 'bi-gear-wide-connected', 'red', $v],
        ];
        $stats = [];
        $details = [];
        foreach ($pick as $key => [$label, $src, $icon, $tone, $data]) {
            $d = $data['stat_detail'][$src];
            $stats[] = ['key' => $key, 'label' => $label, 'value' => count($d['rows']), 'icon' => $icon, 'tone' => $tone];
            $details[$key] = $d;
        }

        return view('asset_dept/dashboard', [
            'title'    => 'Asset Acquisition and Monitoring Department',
            'pageCss'  => 'safety.css',
            'stats'    => $stats,
            'details'  => $details,
            'sections' => [
                ['label' => 'Vehicle and Motor Pool', 'url' => 'assets-dept/vehicles', 'icon' => 'bi-truck', 'desc' => 'Vehicle records, driver information, vehicle maintenance, and mechanical equipment.'],
                ['label' => 'Motor Pool Work Orders', 'url' => 'assets-dept/work-orders', 'icon' => 'bi-wrench-adjustable', 'desc' => 'Vehicle repair requests, mechanical equipment work orders, status, and history.'],
                ['label' => 'Trip Tickets', 'url' => 'assets-dept/trip-tickets', 'icon' => 'bi-signpost-2', 'desc' => 'Trip ticket records with driver, vehicle, destination, and schedule.'],
            ],
        ]);
    }

    // Combined status page — the sidebar label opens this.
    public function status()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $cards = [];
        foreach ([$this->vehiclesData(), $this->workOrdersData(), $this->tripsData()] as $d) {
            foreach ($d['status'] as $card) {
                $detail = $d['stat_detail'][$card['key']];
                $cards[] = $card + ['title' => $detail['title'], 'columns' => $detail['columns'], 'rows' => $detail['rows']];
            }
        }

        return view('facilities/status', [
            'title'      => 'Asset Acquisition and Monitoring Status',
            'page_title' => 'Asset Acquisition and Monitoring Status',
            'pageCss'    => 'safety.css',
            'cards'      => $cards,
        ]);
    }

    public function index(string $section = 'vehicles')
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');
        if (!isset(self::SECTIONS[$section])) return redirect()->to('/assets-dept');

        $data = match ($section) {
            'work-orders'  => $this->workOrdersData(),
            'trip-tickets' => $this->tripsData(),
            default        => $this->vehiclesData(),
        };
        $stat = (string) $this->request->getGet('stat');

        return view('asset_dept/index', $data + [
            'title'       => self::SECTIONS[$section],
            'section'     => $section,
            'pageCss'     => 'safety.css',
            'active_stat' => isset($data['stat_detail'][$stat]) ? $stat : null,
            'stat_rows'   => $data['stat_detail'][$stat] ?? null,
        ]);
    }

    // ------------------------------------------------------------- data

    private function vehiclesData(): array
    {
        $vehicles = (new VehicleModel())->getAllWithDetails();
        $maint = (new VehicleMaintenanceModel())->orderBy('serviced_on', 'DESC')->findAll();
        $byId = [];
        foreach ($vehicles as $v) $byId[$v['id']] = $v;

        // Next service per vehicle = soonest "next due" among the latest record of each service type.
        $latestType = [];
        foreach ($maint as $m) {
            $latestType[$m['vehicle_id']][$m['service_type']] ??= $m['next_due'];
        }
        $nextService = [];
        foreach ($latestType as $vid => $types) {
            $dates = array_filter($types);
            $nextService[$vid] = $dates ? min($dates) : null;
        }

        $equipment = (new MechanicalEquipmentModel())->orderBy('code', 'ASC')->findAll();
        $drivers = $this->drivers();
        $vehicleOfDriver = [];
        foreach ($vehicles as $v) {
            if ($v['driver_id']) $vehicleOfDriver[$v['driver_id']] = $this->vehicleLabel($v);
        }

        $vehicleRow = fn($v) => [
            $this->vehicleLabel($v), $v['type'] ?: '—', $v['driver_name'] ?: '—', $v['availability'], $v['inspection_status'],
            $this->fmtDate($nextService[$v['id']] ?? null), $this->serviceState($nextService[$v['id']] ?? null),
        ];
        $vehicleCols = ['Vehicle', 'Type', 'Driver', 'Availability', 'Inspection', 'Next Service', 'Service Status'];

        $needsAttention = fn($v) => in_array($v['inspection_status'], ['Due Soon', 'Expired'], true)
            || $this->serviceState($nextService[$v['id']] ?? null) !== 'OK'
            || $v['availability'] === 'Maintenance';
        $alertVehicles = array_values(array_filter($vehicles, $needsAttention));
        $availVehicles = array_values(array_filter($vehicles, fn($v) => $v['availability'] === 'Available'));

        $eqRow = fn($e) => [$e['code'], $e['name'], $e['equipment_type'], $e['location'] ?: '—', $e['status'], $this->fmtDate($e['last_service']), $this->fmtDate($e['next_service']), $this->serviceState($e['next_service'])];
        $eqCols = ['Code', 'Name', 'Type', 'Location', 'Status', 'Last Service', 'Next Service', 'Service Status'];
        $eqAttn = array_values(array_filter($equipment, fn($e) => in_array($e['status'], ['Needs Repair', 'Out of Service'], true) || $this->serviceState($e['next_service']) === 'Service Overdue'));

        $driverRow = fn($p) => [$p['full_name'], $p['emp_id'], $p['position'] ?: '—', $p['contact_number'] ?: '—', $vehicleOfDriver[$p['id']] ?? 'Not assigned', $p['status'] ?: 'Active'];
        $driverCols = ['Name', 'Employee ID', 'Position', 'Contact', 'Assigned Vehicle', 'Status'];

        $maintCols = ['Vehicle', 'Service', 'Date', 'Odometer', 'Done By', 'Next Due', 'Service Status'];
        $maintRows = [];
        foreach ($maint as $m) {
            $v = $byId[$m['vehicle_id']] ?? null;
            if (!$v) continue;
            $isLatest = ($latestType[$m['vehicle_id']][$m['service_type']] ?? null) === $m['next_due'];
            $maintRows[] = [
                $this->vehicleLabel($v), $m['service_type'], $this->fmtDate($m['serviced_on']),
                $m['odometer_km'] !== null ? number_format((float) $m['odometer_km']) . ' km' : '—',
                $m['performed_by'] ?: '—', $this->fmtDate($m['next_due']), $isLatest ? $this->serviceState($m['next_due']) : 'OK',
            ];
        }

        // Alerts: red = urgent, yellow = coming up
        $alerts = [];
        foreach ($vehicles as $v) {
            $label = $this->vehicleLabel($v);
            $svc = $this->serviceState($nextService[$v['id']] ?? null);
            if ($v['inspection_status'] === 'Expired') $alerts[] = ['cols' => ['Vehicle', $label, 'Inspection', 'Expired'], 'level' => 'red'];
            elseif ($v['inspection_status'] === 'Due Soon') $alerts[] = ['cols' => ['Vehicle', $label, 'Inspection', 'Due Soon'], 'level' => 'yellow'];
            if ($svc === 'Service Overdue') $alerts[] = ['cols' => ['Vehicle', $label, 'Service', 'Service Overdue'], 'level' => 'red'];
            elseif ($svc === 'Service Due Soon') $alerts[] = ['cols' => ['Vehicle', $label, 'Service', 'Service Due Soon'], 'level' => 'yellow'];
        }
        foreach ($equipment as $e) {
            $svc = $this->serviceState($e['next_service']);
            if (in_array($e['status'], ['Needs Repair', 'Out of Service'], true)) $alerts[] = ['cols' => ['Equipment', $e['code'] . ' — ' . $e['name'], 'Condition', $e['status']], 'level' => 'red'];
            elseif ($svc === 'Service Overdue') $alerts[] = ['cols' => ['Equipment', $e['code'] . ' — ' . $e['name'], 'Service', 'Service Overdue'], 'level' => 'red'];
            elseif ($svc === 'Service Due Soon') $alerts[] = ['cols' => ['Equipment', $e['code'] . ' — ' . $e['name'], 'Service', 'Service Due Soon'], 'level' => 'yellow'];
        }

        return [
            'status' => [
                ['key' => 'v_total', 'label' => 'Total Vehicles', 'value' => count($vehicles), 'icon' => 'bi-truck', 'tone' => 'blue'],
                ['key' => 'v_avail', 'label' => 'Available', 'value' => count($availVehicles), 'icon' => 'bi-check-circle-fill', 'tone' => 'green'],
                ['key' => 'v_alert', 'label' => 'Vehicles Needing Attention', 'value' => count($alertVehicles), 'icon' => 'bi-exclamation-triangle-fill', 'tone' => 'red'],
                ['key' => 'd_total', 'label' => 'Drivers', 'value' => count($drivers), 'icon' => 'bi-person-vcard-fill', 'tone' => 'maroon'],
                ['key' => 'e_attn', 'label' => 'Equipment Needing Repair', 'value' => count($eqAttn), 'icon' => 'bi-gear-wide-connected', 'tone' => 'gold'],
            ],
            'stat_detail' => [
                'v_total' => ['title' => 'All Vehicles', 'columns' => $vehicleCols, 'rows' => array_map($vehicleRow, $vehicles)],
                'v_avail' => ['title' => 'Available Vehicles', 'columns' => $vehicleCols, 'rows' => array_map($vehicleRow, $availVehicles)],
                'v_alert' => ['title' => 'Vehicles Needing Attention', 'columns' => $vehicleCols, 'rows' => array_map($vehicleRow, $alertVehicles)],
                'd_total' => ['title' => 'Drivers', 'columns' => $driverCols, 'rows' => array_map($driverRow, $drivers)],
                'e_attn'  => ['title' => 'Mechanical Equipment Needing Repair or Service', 'columns' => $eqCols, 'rows' => array_map($eqRow, $eqAttn)],
            ],
            'tabs' => [
                ['key' => 'records', 'label' => 'Vehicle Records', 'columns' => $vehicleCols, 'rows' => array_map($vehicleRow, $vehicles), 'empty' => 'No vehicles recorded yet.'],
                ['key' => 'drivers', 'label' => 'Driver Information', 'columns' => $driverCols, 'rows' => array_map($driverRow, $drivers), 'empty' => 'No drivers on file.'],
                ['key' => 'maintenance', 'label' => 'Vehicle Maintenance', 'columns' => $maintCols, 'rows' => $maintRows, 'empty' => 'No maintenance recorded yet.'],
                ['key' => 'equipment', 'label' => 'Mechanical Equipment', 'columns' => $eqCols, 'rows' => array_map($eqRow, $equipment), 'empty' => 'No mechanical equipment recorded yet.'],
            ],
            'alerts_json'  => $this->jsonForScript($alerts),
            'alert_cols'   => ['Kind', 'Item', 'What', 'Status'],
            'alert_titles' => ['red' => 'Needs attention now', 'yellow' => 'Coming up soon'],
            'vehicle_options' => array_map(fn($v) => ['id' => $v['id'], 'label' => $this->vehicleLabel($v)], $vehicles),
            'equipment_options' => array_map(fn($e) => ['id' => $e['id'], 'label' => $e['code'] . ' — ' . $e['name']], $equipment),
            'driver_options' => array_map(fn($p) => ['id' => $p['id'], 'label' => $p['full_name']], $drivers),
            'equipment_statuses' => MechanicalEquipmentModel::STATUSES,
        ];
    }

    private function workOrdersData(): array
    {
        $wos = (new MotorpoolWorkOrderModel())->orderBy('id', 'DESC')->findAll();
        $vehicles = [];
        foreach ((new VehicleModel())->findAll() as $v) $vehicles[$v['id']] = $this->vehicleLabel($v);
        $equipment = [];
        foreach ((new MechanicalEquipmentModel())->findAll() as $e) $equipment[$e['id']] = $e['code'] . ' — ' . $e['name'];
        $subject = fn($w) => $w['wo_type'] === 'Vehicle Repair' ? ($vehicles[$w['vehicle_id']] ?? '—') : ($equipment[$w['equipment_id']] ?? '—');
        $stamp = fn($d) => !empty($d) ? date('M d, Y g:i A', strtotime($d)) : '—';

        $form = function ($w, string $to, string $label, string $cls) {
            $url = base_url('assets-dept/work-orders/' . $w['id'] . '/status');
            return '<form method="post" action="' . $url . '" style="display:inline">' . csrf_field()
                . '<input type="hidden" name="status" value="' . $to . '"><button type="submit" class="fp-btn ' . $cls . '" style="height:30px;width:86px;padding:0;font-size:12px;">' . $label . '</button></form>';
        };
        $actions = function ($w) use ($form) {
            $h = '';
            if ($w['status'] === 'Pending') $h .= $form($w, 'In Progress', 'Start', '') . ' ';
            if ($w['status'] === 'In Progress') $h .= $form($w, 'Completed', 'Complete', '') . ' ';
            if (in_array($w['status'], ['Pending', 'In Progress'], true)) $h .= $form($w, 'Cancelled', 'Cancel', 'secondary');
            return $this->raw($h);
        };

        $openCols = ['WO No.', 'Item', 'Problem', 'Priority', 'Status', 'Requested By', 'Opened', 'Action'];
        $openRow = fn($w) => [$w['wo_number'], $subject($w), $w['issue'], $w['priority'], $w['status'], $w['requested_by'], $stamp($w['created_at']), $actions($w)];
        $plainCols = ['WO No.', 'Type', 'Item', 'Problem', 'Priority', 'Status', 'Requested By', 'Opened', 'Closed'];
        $plainRow = fn($w) => [$w['wo_number'], $w['wo_type'], $subject($w), $w['issue'], $w['priority'], $w['status'], $w['requested_by'], $stamp($w['created_at']), $stamp($w['completed_at'] ?: ($w['status'] === 'Cancelled' ? $w['updated_at'] : null))];

        $open = array_values(array_filter($wos, fn($w) => in_array($w['status'], ['Pending', 'In Progress'], true)));
        $vehOpen = array_values(array_filter($open, fn($w) => $w['wo_type'] === 'Vehicle Repair'));
        $mechOpen = array_values(array_filter($open, fn($w) => $w['wo_type'] === 'Mechanical Equipment'));
        $closed = array_values(array_filter($wos, fn($w) => in_array($w['status'], ['Completed', 'Cancelled'], true)));
        $pending = array_values(array_filter($wos, fn($w) => $w['status'] === 'Pending'));
        $progress = array_values(array_filter($wos, fn($w) => $w['status'] === 'In Progress'));
        $done = array_values(array_filter($wos, fn($w) => $w['status'] === 'Completed'));
        $urgent = array_values(array_filter($open, fn($w) => $w['priority'] === 'Urgent'));

        $byNo = [];
        foreach ($wos as $w) $byNo[$w['id']] = $w;
        $logCols = ['WO No.', 'Type', 'Item', 'Status', 'Changed By', 'When', 'Notes'];
        $logRows = [];
        $db = \Config\Database::connect();
        foreach ($db->table('motorpool_wo_history')->orderBy('changed_at', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray() as $h) {
            $w = $byNo[$h['work_order_id']] ?? null;
            if (!$w) continue;
            $logRows[] = [$w['wo_number'], $w['wo_type'], $subject($w), $h['status'], $h['changed_by'], $stamp($h['changed_at']), $h['notes'] ?: '—'];
        }

        $alerts = [];
        foreach ($urgent as $w) $alerts[] = ['cols' => [$w['wo_number'], $w['wo_type'], $subject($w), $w['status']], 'level' => 'red'];
        foreach ($pending as $w) {
            if ($w['priority'] !== 'Urgent') $alerts[] = ['cols' => [$w['wo_number'], $w['wo_type'], $subject($w), 'Pending'], 'level' => 'yellow'];
        }

        return [
            'status' => [
                ['key' => 'w_open', 'label' => 'Open Work Orders', 'value' => count($open), 'icon' => 'bi-wrench-adjustable', 'tone' => 'gold'],
                ['key' => 'w_pending', 'label' => 'Pending', 'value' => count($pending), 'icon' => 'bi-hourglass-split', 'tone' => 'gold'],
                ['key' => 'w_progress', 'label' => 'In Progress', 'value' => count($progress), 'icon' => 'bi-tools', 'tone' => 'blue'],
                ['key' => 'w_done', 'label' => 'Completed', 'value' => count($done), 'icon' => 'bi-check-circle-fill', 'tone' => 'green'],
                ['key' => 'w_urgent', 'label' => 'Urgent', 'value' => count($urgent), 'icon' => 'bi-exclamation-triangle-fill', 'tone' => 'red'],
            ],
            'stat_detail' => [
                'w_open'     => ['title' => 'Open Work Orders', 'columns' => $plainCols, 'rows' => array_map($plainRow, $open)],
                'w_pending'  => ['title' => 'Pending Work Orders', 'columns' => $plainCols, 'rows' => array_map($plainRow, $pending)],
                'w_progress' => ['title' => 'Work Orders In Progress', 'columns' => $plainCols, 'rows' => array_map($plainRow, $progress)],
                'w_done'     => ['title' => 'Completed Work Orders', 'columns' => $plainCols, 'rows' => array_map($plainRow, $done)],
                'w_urgent'   => ['title' => 'Urgent Work Orders', 'columns' => $plainCols, 'rows' => array_map($plainRow, $urgent)],
            ],
            'tabs' => [
                ['key' => 'vehicle', 'label' => 'Vehicle Repair Requests', 'columns' => $openCols, 'rows' => array_map($openRow, $vehOpen), 'empty' => 'No open vehicle repair requests.'],
                ['key' => 'mechanical', 'label' => 'Mechanical Equipment Work Orders', 'columns' => $openCols, 'rows' => array_map($openRow, $mechOpen), 'empty' => 'No open equipment work orders.'],
                ['key' => 'history', 'label' => 'Work Order History', 'columns' => $plainCols, 'rows' => array_map($plainRow, $closed), 'empty' => 'No closed work orders yet.'],
                ['key' => 'log', 'label' => 'Status Log', 'columns' => $logCols, 'rows' => $logRows, 'empty' => 'No status changes yet.'],
            ],
            'alerts_json'  => $this->jsonForScript($alerts),
            'alert_cols'   => ['WO No.', 'Type', 'Item', 'Status'],
            'alert_titles' => ['red' => 'Urgent work orders', 'yellow' => 'Waiting to be started'],
            'vehicle_options' => array_map(fn($v) => ['id' => $v['id'], 'label' => $v['vehicle_name'] . ' (' . $v['plate_no'] . ')'], (new VehicleModel())->where('is_archived', 0)->findAll()),
            'equipment_options' => array_map(fn($e) => ['id' => $e['id'], 'label' => $e['code'] . ' — ' . $e['name']], (new MechanicalEquipmentModel())->orderBy('code')->findAll()),
        ];
    }

    private function tripsData(): array
    {
        $trips = array_values(array_filter((new TravelModel())->getAllWithDetails(), fn($t) => true));
        $today = date('Y-m-d');
        $sched = fn($t) => $this->fmtDate($t['travel_date']) . ' · ' . date('g:i A', strtotime($t['departure_time'])) . ' – ' . date('g:i A', strtotime($t['return_time']));
        $cols = ['Trip No.', 'Driver', 'Vehicle', 'Destination', 'Schedule', 'Status', 'Requested By'];
        $row = fn($t) => [
            $t['trip_id'], $t['driver_name'] ?: 'Unassigned', $t['vehicle_name'] ? $t['vehicle_name'] . ' (' . $t['plate_no'] . ')' : 'Unassigned',
            $t['destination'], $sched($t), $t['status'], $t['requester_name'] ?: '—',
        ];

        $active = fn($t) => !in_array($t['status'], ['Rejected', 'Cancelled'], true);
        $todayTrips = array_values(array_filter($trips, fn($t) => $t['travel_date'] === $today && $active($t)));
        $scheduled = array_values(array_filter($trips, fn($t) => $t['status'] === 'Approved' && $t['travel_date'] >= $today));
        $transit = array_values(array_filter($trips, fn($t) => $t['status'] === 'In Transit'));
        $done = array_values(array_filter($trips, fn($t) => $t['status'] === 'Completed'));
        $upcoming = array_values(array_filter($trips, fn($t) => $t['travel_date'] >= $today && $active($t) && $t['status'] !== 'Completed'));

        $alerts = [];
        foreach ($transit as $t) $alerts[] = ['cols' => [$t['trip_id'], $t['driver_name'] ?: 'Unassigned', $t['destination'], 'In Transit'], 'level' => 'yellow'];
        foreach ($trips as $t) {
            if ($active($t) && $t['status'] !== 'Completed' && $t['status'] !== 'In Transit' && $t['travel_date'] < $today) {
                $alerts[] = ['cols' => [$t['trip_id'], $t['driver_name'] ?: 'Unassigned', $t['destination'], 'Overdue'], 'level' => 'red'];
            }
        }

        $options = fn($rows) => array_map(fn($r) => $r, $rows);
        $vehicles = (new VehicleModel())->where('is_archived', 0)->findAll();
        $people = (new PersonnelModel())->where('is_archived', 0)->orderBy('full_name')->findAll();

        return [
            'status' => [
                ['key' => 't_today', 'label' => 'Trips Today', 'value' => count($todayTrips), 'icon' => 'bi-calendar-day', 'tone' => 'maroon'],
                ['key' => 't_sched', 'label' => 'Scheduled', 'value' => count($scheduled), 'icon' => 'bi-calendar-check', 'tone' => 'blue'],
                ['key' => 't_transit', 'label' => 'In Transit', 'value' => count($transit), 'icon' => 'bi-truck', 'tone' => 'gold'],
            ],
            'stat_detail' => [
                't_today'   => ['title' => 'Trips Today', 'columns' => $cols, 'rows' => array_map($row, $todayTrips)],
                't_sched'   => ['title' => 'Scheduled Trips', 'columns' => $cols, 'rows' => array_map($row, $scheduled)],
                't_transit' => ['title' => 'Trips In Transit', 'columns' => $cols, 'rows' => array_map($row, $transit)],
                                't_done'    => ['title' => 'Completed Trips', 'columns' => $cols, 'rows' => array_map($row, $done)],
            ],
            'tabs' => [
                ['key' => 'all', 'label' => 'Trip Ticket Records', 'columns' => $cols, 'rows' => array_map($row, $trips), 'empty' => 'No trip tickets yet.'],
                ['key' => 'upcoming', 'label' => 'Today & Upcoming', 'columns' => $cols, 'rows' => array_map($row, $upcoming), 'empty' => 'No trips scheduled.'],
                ['key' => 'completed', 'label' => 'Completed', 'columns' => $cols, 'rows' => array_map($row, $done), 'empty' => 'No completed trips yet.'],
            ],
            'alerts_json'  => $this->jsonForScript($alerts),
            'alert_cols'   => ['Trip No.', 'Driver', 'Destination', 'Status'],
            'alert_titles' => ['red' => 'Overdue trips', 'yellow' => 'Trips on the road now'],
            'vehicle_options' => array_map(fn($v) => ['id' => $v['id'], 'label' => $this->vehicleLabel($v)], $vehicles),
            'driver_options' => array_map(fn($p) => ['id' => $p['id'], 'label' => $p['full_name']], $this->drivers()),
            'people_options' => array_map(fn($p) => ['id' => $p['id'], 'label' => $p['full_name'], 'emp' => $p['emp_id']], $people),
            'me_emp_id' => (string) (session()->get('emp_id') ?? ''),
        ];
    }

    // --------------------------------------------------------- saving

    private function back(string $path, string $key, string $msg)
    {
        return redirect()->to('/assets-dept/' . $path)->with($key, $msg);
    }

    private function dateOrNull($v): ?string
    {
        $v = trim((string) $v);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) ? $v : null;
    }

    public function storeVehicle()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $name = trim((string) $this->request->getPost('vehicle_name'));
        $plate = trim((string) $this->request->getPost('plate_no'));
        if ($name === '' || $plate === '') return $this->back('vehicles', 'error', 'Vehicle name and plate number are required.');

        $model = new VehicleModel();
        if ($model->where('plate_no', $plate)->countAllResults() > 0) return $this->back('vehicles', 'error', "Plate number {$plate} is already recorded.");

        $driver = (int) $this->request->getPost('driver_id');
        $avail = (string) $this->request->getPost('availability');
        $insp = (string) $this->request->getPost('inspection_status');
        $model->insert([
            'vehicle_name'      => $name,
            'plate_no'          => $plate,
            'type'              => trim((string) $this->request->getPost('type')) ?: null,
            'driver_id'         => $driver ?: null,
            'availability'      => in_array($avail, ['Available', 'In Use', 'Maintenance', 'Reserved', 'Inactive'], true) ? $avail : 'Available',
            'inspection_status' => in_array($insp, ['Completed', 'Due Soon', 'Expired'], true) ? $insp : 'Due Soon',
        ]);

        return $this->back('vehicles', 'success', "Vehicle {$name} added.");
    }

    public function storeMaintenance()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $vehicleId = (int) $this->request->getPost('vehicle_id');
        $service = trim((string) $this->request->getPost('service_type'));
        $date = $this->dateOrNull($this->request->getPost('serviced_on'));
        if (!(new VehicleModel())->find($vehicleId) || $service === '' || !$date) {
            return $this->back('vehicles', 'error', 'Choose a vehicle, a service, and the date it was done.');
        }

        (new VehicleMaintenanceModel())->insert([
            'vehicle_id'   => $vehicleId,
            'service_type' => $service,
            'serviced_on'  => $date,
            'odometer_km'  => $this->request->getPost('odometer_km') !== '' ? (float) $this->request->getPost('odometer_km') : null,
            'performed_by' => trim((string) $this->request->getPost('performed_by')) ?: null,
            'next_due'     => $this->dateOrNull($this->request->getPost('next_due')),
            'notes'        => trim((string) $this->request->getPost('notes')) ?: null,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        return $this->back('vehicles', 'success', 'Maintenance record saved.');
    }

    public function storeEquipment()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $code = trim((string) $this->request->getPost('code'));
        $name = trim((string) $this->request->getPost('name'));
        $status = (string) $this->request->getPost('status');
        if ($code === '' || $name === '') return $this->back('vehicles', 'error', 'Code and name are required.');

        $model = new MechanicalEquipmentModel();
        if ($model->where('code', $code)->countAllResults() > 0) return $this->back('vehicles', 'error', "Code {$code} is already in use.");

        $model->insert([
            'code'           => $code,
            'name'           => $name,
            'equipment_type' => trim((string) $this->request->getPost('equipment_type')) ?: 'General',
            'location'       => trim((string) $this->request->getPost('location')) ?: null,
            'status'         => in_array($status, MechanicalEquipmentModel::STATUSES, true) ? $status : 'Operational',
            'last_service'   => $this->dateOrNull($this->request->getPost('last_service')),
            'next_service'   => $this->dateOrNull($this->request->getPost('next_service')),
            'remarks'        => trim((string) $this->request->getPost('remarks')) ?: null,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        return $this->back('vehicles', 'success', "Equipment {$code} added.");
    }

    public function storeWorkOrder()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $type = (string) $this->request->getPost('wo_type');
        $issue = trim((string) $this->request->getPost('issue'));
        $vehicleId = (int) $this->request->getPost('vehicle_id');
        $equipmentId = (int) $this->request->getPost('equipment_id');
        if (!in_array($type, MotorpoolWorkOrderModel::TYPES, true) || $issue === '') {
            return $this->back('work-orders', 'error', 'Choose the kind of work order and describe the problem.');
        }
        if ($type === 'Vehicle Repair' && !(new VehicleModel())->find($vehicleId)) return $this->back('work-orders', 'error', 'Choose a vehicle.');
        if ($type === 'Mechanical Equipment' && !(new MechanicalEquipmentModel())->find($equipmentId)) return $this->back('work-orders', 'error', 'Choose the equipment.');

        $model = new MotorpoolWorkOrderModel();
        $n = $model->countAllResults() + 1;
        while ($model->where('wo_number', sprintf('MP-%03d', $n))->countAllResults() > 0) $n++;
        $no = sprintf('MP-%03d', $n);
        $now = date('Y-m-d H:i:s');
        $priority = $this->request->getPost('priority') === 'Urgent' ? 'Urgent' : 'Routine';

        $id = $model->insert([
            'wo_number'    => $no,
            'wo_type'      => $type,
            'vehicle_id'   => $type === 'Vehicle Repair' ? $vehicleId : null,
            'equipment_id' => $type === 'Mechanical Equipment' ? $equipmentId : null,
            'issue'        => $issue,
            'priority'     => $priority,
            'status'       => 'Pending',
            'requested_by' => $this->me(),
            'created_at'   => $now,
        ], true);
        \Config\Database::connect()->table('motorpool_wo_history')->insert([
            'work_order_id' => $id, 'status' => 'Pending', 'changed_by' => $this->me(), 'changed_at' => $now, 'notes' => 'Work order created',
        ]);

        $this->notify('Motor Pool Work Order', "{$no} ({$type}) opened by " . $this->me() . ': ' . $issue, $priority === 'Urgent' ? 'CRITICAL' : 'ROUTINE');

        return $this->back('work-orders', 'success', "Work order {$no} created.");
    }

    public function updateWorkOrderStatus(int $id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $model = new MotorpoolWorkOrderModel();
        $wo = $model->find($id);
        $to = (string) $this->request->getPost('status');
        $allowed = ['Pending' => ['In Progress', 'Cancelled'], 'In Progress' => ['Completed', 'Cancelled']];
        if (!$wo || !in_array($to, $allowed[$wo['status']] ?? [], true)) {
            return $this->back('work-orders', 'error', 'That status change is not allowed.');
        }

        $now = date('Y-m-d H:i:s');
        $update = ['status' => $to, 'updated_at' => $now];
        if ($to === 'In Progress') $update['assigned_to'] = $this->me();
        if ($to === 'Completed') $update['completed_at'] = $now;
        $model->update($id, $update);
        \Config\Database::connect()->table('motorpool_wo_history')->insert([
            'work_order_id' => $id, 'status' => $to, 'changed_by' => $this->me(), 'changed_at' => $now,
            'notes' => ['In Progress' => 'Work started', 'Completed' => 'Work finished', 'Cancelled' => 'Request cancelled'][$to],
        ]);

        $this->notify('Motor Pool Work Order', "{$wo['wo_number']} is now {$to}.", 'ROUTINE');

        return $this->back('work-orders', 'success', "{$wo['wo_number']} is now {$to}.");
    }

    public function storeTrip()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $dest = trim((string) $this->request->getPost('destination'));
        $purpose = trim((string) $this->request->getPost('purpose'));
        $date = $this->dateOrNull($this->request->getPost('travel_date'));
        $dep = (string) $this->request->getPost('departure_time');
        $ret = (string) $this->request->getPost('return_time');
        $requester = (int) $this->request->getPost('requester_id');
        if ($dest === '' || $purpose === '' || !$date || $dep === '' || $ret === '' || !(new PersonnelModel())->find($requester)) {
            return $this->back('trip-tickets', 'error', 'Requester, destination, purpose, date and times are required.');
        }
        if ($ret <= $dep) return $this->back('trip-tickets', 'error', 'Return time must be after the departure time.');

        $driver = (int) $this->request->getPost('driver_id');
        $vehicle = (int) $this->request->getPost('vehicle_id');
        $model = new TravelModel();
        $now = date('Y-m-d H:i:s');
        $status = $driver && $vehicle ? 'Approved' : 'Submitted';
        $id = $model->insert([
            'trip_id'             => $model->generateTripId(),
            'requester_id'        => $requester,
            'destination'         => $dest,
            'purpose'             => $purpose,
            'travel_date'         => $date,
            'departure_time'      => $dep,
            'return_time'         => $ret,
            'assigned_driver_id'  => $driver ?: null,
            'assigned_vehicle_id' => $vehicle ?: null,
            'status'              => $status,
            'last_activity_at'    => $now,
        ], true);
        \Config\Database::connect()->table('trip_status_log')->insert([
            'travel_request_id' => $id, 'status' => $status, 'changed_by' => $this->me(), 'changed_at' => $now, 'notes' => 'Created by Asset Acquisition and Monitoring',
        ]);

        $this->notify('Trip Ticket Request', "Trip ticket created for {$dest} on " . date('M j, Y', strtotime($date)) . ' (' . $status . ').');

        return $this->back('trip-tickets', 'success', 'Trip ticket created (' . $status . ').');
    }
}
