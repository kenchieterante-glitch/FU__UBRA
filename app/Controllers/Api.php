<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ToolsModel;
use App\Models\BorrowModel;
use App\Models\ReturnModel;
use App\Models\VehicleModel;
use App\Models\GPSModel;
use App\Models\PersonnelModel;
use App\Models\DepartmentModel;
use App\Models\TravelRequestModel;
use App\Models\FireExtinguisherModel;
use App\Models\AirconUnitModel;
use App\Models\AirconChecklistItemModel;
use App\Models\JanitorialAssignmentModel;
use App\Models\JanitorialTaskModel;
use App\Models\JanitorialTaskHistoryModel;
use App\Models\ConsumableInventoryModel;
use App\Models\ConsumableStockReportModel;
use App\Models\KeyBorrowLogModel;
use App\Models\NotificationModel;
use App\Models\ReportModel;
use App\Models\FacilityChecklistModel;
use App\Models\FacilityChecklistItemModel;
use App\Models\EquipmentMaintenanceLogModel;
use App\Models\EquipmentMaintenanceEntryModel;
use App\Models\AirconInspectionLogModel;
use App\Models\AirconInspectionEntryModel;
use App\Models\VehicleInspectionChecklistModel;
use App\Models\VehicleInspectionItemModel;
use App\Models\RestroomChecklistModel;
use App\Models\RestroomChecklistEntryModel;

/**
 * JSON API layer for the FU-UBRA Expo mobile app — wired to the same tables/Models
 * the web dashboard uses, so data added from mobile shows up on the web dashboard
 * and vice versa.
 */
class Api extends BaseController
{
    // Real campus building name <-> slug, matching the actual evacuation-map
    // layout rendered on the web dashboard's Safety Maintenance page (same
    // 28 buildings, same names) so mobile and web always agree on zones.
    private const ZONE_SLUGS = [
        'Main entrance gate'                                    => 'main-entrance-gate',
        'University cafeteria / bookstore / sewing'              => 'cafeteria',
        'College of Law building'                                => 'law',
        'College of Agriculture and SIE'                         => 'agriculture',
        'Museo de Vicente'                                       => 'museo-de-vicente',
        'Bunk house'                                             => 'bunk-house',
        'Service / exit gate'                                    => 'service-gate',
        'University library'                                     => 'library',
        'Electric pump house'                                    => 'electric-pump',
        'Executive house'                                        => 'executive-house',
        'Water pump'                                             => 'water-pump',
        'Guest house'                                            => 'guest-house',
        'HRM kitchen'                                            => 'hrm-kitchen',
        'College of Education building'                          => 'education',
        'Animation Lab / ROTC office'                            => 'animation-rotc',
        'LG Sinco Computer Center building'                      => 'lg-sinco-computer-center',
        'Sofia Soller Sinco Hall'                                => 'sofia-soller-sinco-hall',
        'College of Art & Sciences building'                     => 'art-sciences',
        'Art & Science laboratories / audio visual rooms'        => 'art-science-labs',
        'College of Business Economics and Accountancy'          => 'business-economics',
        'College of Nursing'                                     => 'nursing',
        'Administration building'                                => 'admin',
        'Rizal monument / social garden'                         => 'rizal-monument',
        "Registrar's office"                                     => 'registrar',
        'Business and Finance office'                            => 'business-finance',
        'Old College of Industrial Engineering and Technology'   => 'old-cie',
        'Overhead water supply tank'                             => 'water-tank',
        'Flag pole'                                              => 'flag-pole',
    ];

    // ---------- AUTH ----------

    public function login()
    {
        $body       = $this->request->getJSON();
        $employeeId = trim((string) ($body->employee_id ?? ''));
        $password   = (string) ($body->password ?? '');

        if ($employeeId === '' || $password === '') {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'Invalid credentials']);
        }

        $userModel = new UserModel();
        $user      = $userModel->getByEmployeeId($employeeId);

        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'Invalid credentials']);
        }

        $storedHash = $user['password_hash'] ?? $user['password'] ?? null;

        if (empty($storedHash) || !password_verify($password, $storedHash)) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'Invalid credentials']);
        }

        return $this->response->setJSON($this->buildAuthPayload($user, $employeeId));
    }

    // Login by scanning the campus ID card's barcode instead of typing a
    // password — the scanned code is looked up the same way as the manual
    // employee-ID field (emp_id / employee_id / username).
    public function scanLogin()
    {
        $body = $this->request->getJSON(true) ?? [];
        $code = trim((string) ($body['code'] ?? ''));

        if ($code === '') {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'No code scanned.']);
        }

        $userModel = new UserModel();
        $user      = $userModel->getByEmployeeId($code);

        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'ID not recognized. Please sign in manually.']);
        }

        return $this->response->setJSON($this->buildAuthPayload($user, $code));
    }

    private function buildAuthPayload(array $user, string $fallbackId): array
    {
        // TODO: generate a real signed token (e.g. firebase/php-jwt) instead of this placeholder
        $token      = bin2hex(random_bytes(24));
        $employeeId = $user['emp_id'] ?? $fallbackId;

        // Persist token -> employee_id so later requests (e.g. saveExtinguisher)
        // can identify who's logged in from the Authorization header alone,
        // instead of trusting whatever the client happens to put in the body.
        cache()->save('api_token_' . $token, $employeeId, 60 * 60 * 24 * 30);

        return [
            'token' => $token,
            'user' => [
                'name'        => $user['full_name'] ?? $user['emp_id'] ?? 'User',
                'employee_id' => $employeeId,
                'department'  => $user['department'] ?? '',
                'role'        => $user['role'] ?? null,
                'is_guard'    => strtolower((string) ($user['role'] ?? '')) === 'guard',
            ],
        ];
    }

    // Resolves the currently-authenticated employee from the Authorization:
    // Bearer <token> header issued by login()/scanLogin(). This is the
    // authoritative source for "who is doing this action" on write endpoints
    // — falls back to null if no valid/known token was sent.
    private function currentApiUser(): ?array
    {
        $header = $this->request->getHeaderLine('Authorization');
        if (empty($header) || stripos($header, 'Bearer ') !== 0) {
            return null;
        }

        $token      = trim(substr($header, 7));
        $employeeId = $token !== '' ? cache()->get('api_token_' . $token) : null;
        if (empty($employeeId)) {
            return null;
        }

        $userModel = new UserModel();
        $user      = $userModel->getByEmployeeId($employeeId);
        if (!$user) {
            return null;
        }

        return [
            'name'        => $user['full_name'] ?? $employeeId,
            'employee_id' => $user['emp_id'] ?? $employeeId,
        ];
    }

    // Auto-logs a completed maintenance action (aircon cleaning, fire
    // extinguisher renewal, etc.) as a report so it shows up in the
    // Records/Information Hub without staff having to fill out a separate
    // report by hand — the mobile action itself is the report.
    private function logMaintenanceReport(string $reportName, string $module, ?string $empId): void
    {
        $generatedById = null;
        if (!empty($empId)) {
            $person = (new PersonnelModel())->getByEmpId($empId);
            $generatedById = $person['id'] ?? null;
        }

        (new ReportModel())->insert([
            'report_name'     => $reportName,
            'generated_by_id' => $generatedById,
            'type_module'     => $module,
            'status'          => 'Completed',
        ]);
    }

    // ---------- TOOLS ----------

    public function tools()
    {
        $category = $this->request->getGet('category');
        $model    = new ToolsModel();

        $builder = $model->where('is_archived', 0);
        if (!empty($category)) {
            $builder = $builder->where('category', $category);
        }
        $rows = $builder->orderBy('id', 'DESC')->findAll();

        // A stock-tracked item stays "Available" as long as any units remain,
        // even after a partial borrow — so surface how much is actively out
        // on loan per tool, otherwise a successful borrow on a consumable
        // looks like nothing happened (status doesn't flip, only qty drops).
        $borrowedByTool = [];
        $borrowRows = (new BorrowModel())
            ->select('tool_id, SUM(quantity) as total')
            ->where('status', 'Borrowed')
            ->groupBy('tool_id')
            ->findAll();
        foreach ($borrowRows as $b) {
            $borrowedByTool[$b['tool_id']] = (float) $b['total'];
        }

        return $this->response->setJSON([
            'tools' => array_map(fn($t) => [
                'asset_id'      => $t['asset_code'],
                'tool_name'     => $t['asset_name'],
                'category'      => $t['category'],
                'condition'     => $t['condition_status'],
                'status'        => $t['availability'],
                // Consumables (current_stock not null) can be borrowed in a
                // quantity; everything else is one physical unit.
                'qty'           => $t['current_stock'] !== null ? (float) $t['current_stock'] : 1,
                'stock_tracked' => $t['current_stock'] !== null,
                // Only meaningful for stock-tracked items — a single-unit
                // tool's true borrow state is its `status`, not this sum
                // (which some legacy rows still carry as stale "Borrowed"
                // borrow_records left over from before status was reset).
                'borrowed_qty'  => $t['current_stock'] !== null ? ($borrowedByTool[$t['id']] ?? 0) : 0,
            ], $rows),
            'category' => $category,
        ]);
    }

    public function toolCategories()
    {
        $model = new ToolsModel();
        $rows  = $model->getCategoryDistribution();

        return $this->response->setJSON([
            'categories' => array_values(array_filter(array_map(fn($r) => $r['category'], $rows))),
        ]);
    }

    // Scan-to-lookup: shows the tool's info and current availability without
    // committing to a borrow/return yet, so the app can display a "Borrow"
    // button or "Unavailable" label and let the user confirm the action.
    public function toolLookup($code = null)
    {
        $code = trim((string) ($code ?? $this->request->getGet('code') ?? ''));
        if ($code === '') {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'No code scanned.']);
        }

        $tool = (new ToolsModel())->where('asset_code', $code)->where('is_archived', 0)->first();
        if (!$tool) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'No tool found for that code.']);
        }

        $stockTracked = $tool['current_stock'] !== null;
        $borrowedQty = 0;
        if ($stockTracked) {
            $sum = (new BorrowModel())->where('tool_id', $tool['id'])->where('status', 'Borrowed')
                ->selectSum('quantity')->first();
            $borrowedQty = (float) ($sum['quantity'] ?? 0);
        }

        return $this->response->setJSON([
            'asset_id'      => $tool['asset_code'],
            'tool_name'     => $tool['asset_name'],
            'category'      => $tool['category'],
            'condition'     => $tool['condition_status'],
            'status'        => $tool['availability'],
            'available'     => $tool['availability'] === 'Available',
            'qty'           => $tool['current_stock'] !== null ? (float) $tool['current_stock'] : 1,
            'stock_tracked' => $stockTracked,
            'borrowed_qty'  => $borrowedQty,
        ]);
    }

    // Full borrow/return log for one tool — who has (or had) it, and who
    // brought it back. Pulls from the same borrow_records/return_records
    // tables the web Tools Management "Records" page reads, so mobile and
    // web always show the same history for a given asset.
    public function toolHistory($code = null)
    {
        $code = trim((string) ($code ?? ''));
        if ($code === '') {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'No code provided.']);
        }

        $tool = (new ToolsModel())->where('asset_code', $code)->where('is_archived', 0)->first();
        if (!$tool) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'No tool found for that code.']);
        }

        $borrows = (new BorrowModel())->where('tool_id', $tool['id'])->orderBy('id', 'DESC')->findAll();

        $returnModel = new ReturnModel();
        $history = array_map(function ($b) use ($returnModel) {
            $return = $returnModel->where('borrow_id', $b['id'])->orderBy('id', 'DESC')->first();
            return [
                'borrower'         => $b['borrower'],
                'department'       => $b['department'] ?: null,
                'borrowed_date'    => $b['borrowed_date'],
                'expected_return'  => $b['expected_return'],
                'status'           => $b['status'],
                'returned_date'    => $return['return_date'] ?? null,
                'returned_by'      => $return['returned_by'] ?? null,
                'condition_status' => $return['condition_status'] ?? null,
                'remarks'          => $return['remarks'] ?? null,
            ];
        }, $borrows);

        return $this->response->setJSON([
            'asset_id'  => $tool['asset_code'],
            'tool_name' => $tool['asset_name'],
            'history'   => $history,
        ]);
    }

    public function toolsScanBorrow()
    {
        $body = $this->request->getJSON(true) ?? [];
        $code = trim((string) ($body['code'] ?? ''));

        $toolsModel  = new ToolsModel();
        $borrowModel = new BorrowModel();

        $tool = $toolsModel->where('asset_code', $code)->where('is_archived', 0)->first();
        if (!$tool) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'No tool found for that code.']);
        }
        if ($tool['availability'] !== 'Available') {
            return $this->response->setStatusCode(409)->setJSON(['message' => "{$tool['asset_name']} is not available to borrow right now."]);
        }

        // Consumables (current_stock not null) can be borrowed in a chosen
        // quantity, capped at what's left; everything else is one physical
        // unit and always borrows exactly 1.
        $stockTracked = $tool['current_stock'] !== null;
        $quantity = $stockTracked && isset($body['quantity']) ? (float) $body['quantity'] : 1;

        if ($quantity <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'Quantity must be greater than zero.']);
        }
        if ($stockTracked && $quantity > (float) $tool['current_stock']) {
            return $this->response->setStatusCode(409)->setJSON([
                'message' => "Only {$tool['current_stock']} {$tool['asset_name']} left in stock.",
            ]);
        }

        $borrowerName = $body['borrower_name'] ?? ($body['employee_id'] ?? 'Unknown');
        $department   = $body['department'] ?? '';

        $borrowModel->insert([
            'tool_id'         => $tool['id'],
            'quantity'        => $quantity,
            'borrower'        => $borrowerName,
            'department'      => $department,
            'borrowed_date'   => date('Y-m-d'),
            'expected_return' => date('Y-m-d', strtotime('+7 days')),
            'status'          => 'Borrowed',
            'created_at'      => date('Y-m-d H:i:s'),
            'last_activity_at'=> date('Y-m-d H:i:s'),
        ]);

        $remainingStock = $stockTracked ? (float) $tool['current_stock'] - $quantity : null;

        $toolsModel->update($tool['id'], array_filter([
            'availability'      => (!$stockTracked || $remainingStock <= 0) ? 'Borrowed' : 'Available',
            'current_stock'     => $stockTracked ? $remainingStock : null,
            'last_activity_at'  => date('Y-m-d H:i:s'),
        ], fn($v) => $v !== null));

        return $this->response->setJSON([
            'action'        => 'borrow confirmed',
            'borrower_name' => $borrowerName,
            'tool_name'     => $tool['asset_name'],
            'asset_id'      => $tool['asset_code'],
            'quantity'      => $quantity,
            'timestamp'     => date('M j, Y — h:i A'),
        ]);
    }

    public function toolsScanReturn()
    {
        $body = $this->request->getJSON(true) ?? [];
        $code = trim((string) ($body['code'] ?? ''));

        $toolsModel  = new ToolsModel();
        $borrowModel = new BorrowModel();
        $returnModel = new ReturnModel();

        $tool = $toolsModel->where('asset_code', $code)->where('is_archived', 0)->first();
        if (!$tool) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'No tool found for that code.']);
        }

        $borrowRecord = $borrowModel->where('tool_id', $tool['id'])->where('status', 'Borrowed')
                                     ->orderBy('id', 'DESC')->first();
        if (!$borrowRecord) {
            return $this->response->setStatusCode(409)->setJSON(['message' => "{$tool['asset_name']} isn't currently marked as borrowed."]);
        }

        $condition = $body['condition_status'] ?? 'Good';
        $remarks   = $body['remarks'] ?? null;

        $borrowModel->update($borrowRecord['id'], [
            'status'            => 'Returned',
            'last_activity_at'  => date('Y-m-d H:i:s'),
        ]);

        $returnModel->insert([
            'borrow_id'        => $borrowRecord['id'],
            'tool_id'          => $tool['id'],
            'returned_by'      => $borrowRecord['borrower'],
            'return_date'      => date('Y-m-d'),
            'condition_status' => $condition,
            'remarks'          => $remarks,
        ]);

        $stockTracked = $tool['current_stock'] !== null;
        $returnedQty  = (float) ($borrowRecord['quantity'] ?? 1);

        $toolsModel->update($tool['id'], [
            'availability'      => 'Available',
            'condition_status'  => $condition,
            'current_stock'     => $stockTracked ? (float) $tool['current_stock'] + $returnedQty : $tool['current_stock'],
            'last_activity_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'action'        => 'return confirmed',
            'borrower_name' => $borrowRecord['borrower'],
            'tool_name'     => $tool['asset_name'],
            'asset_id'      => $tool['asset_code'],
            'quantity'      => $returnedQty,
            'timestamp'     => date('M j, Y — h:i A'),
        ]);
    }

    // ---------- VEHICLES ----------

    public function vehicles()
    {
        $model = new VehicleModel();
        $rows  = $model->getAllWithDetails();

        return $this->response->setJSON([
            'vehicles' => array_map(fn($v) => [
                'plate'             => $v['plate_no'],
                'name'              => $v['vehicle_name'],
                'type'              => $v['type'],
                // `type` is free text ("Motorcycle", "4 Wheels", "Automatic Car ",
                // "V2-4Wheels", ...) with no dedicated wheel-count column, so
                // classify it: anything naming a motorcycle/scooter is 2-wheel,
                // everything else (car/van/truck/bus/etc.) defaults to 4-wheel.
                'wheels'            => $this->classifyVehicleWheels($v['type']),
                'driver'            => $v['driver_name'] ?? null,
                'department'        => $v['department_name'] ?? null,
                'availability'      => $v['availability'],
                'gps_status'        => $v['gps_status'],
                'inspection_status' => $v['inspection_status'],
            ], $rows),
        ]);
    }

    private function classifyVehicleWheels(?string $type): int
    {
        $t = strtolower((string) $type);
        $isTwoWheeled = str_contains($t, 'motor') || str_contains($t, 'scooter')
            || str_contains($t, 'tricycle') || str_contains($t, 'bike');
        return $isTwoWheeled ? 2 : 4;
    }

    public function vehiclesMeta()
    {
        $personnelModel  = new PersonnelModel();
        $departmentModel = new DepartmentModel();

        return $this->response->setJSON([
            'drivers'     => array_map(fn($p) => ['id' => $p['id'], 'name' => $p['full_name']], $personnelModel->getDrivers()),
            'departments' => array_map(fn($d) => ['id' => $d['id'], 'name' => $d['name']], $departmentModel->findAll()),
        ]);
    }

    public function addVehicle()
    {
        $data  = $this->request->getJSON(true) ?? [];
        $model = new VehicleModel();

        if (empty($data['vehicle_name']) || empty($data['plate_no'])) {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'Vehicle name and plate number are required.']);
        }

        $id = $model->insert([
            'vehicle_name'      => $data['vehicle_name'],
            'plate_no'          => $data['plate_no'],
            'type'              => $data['type'] ?? null,
            'driver_id'         => $data['driver_id'] ?: null,
            'department_id'     => $data['department_id'] ?: null,
            'gps_status'        => 'Offline',
            'inspection_status' => 'Due Soon',
            'availability'      => 'Available',
        ]);

        return $this->response->setJSON(['message' => 'Vehicle saved', 'id' => $id]);
    }

    // Live location for the mobile Track vehicle map. Only returns fields the
    // system actually records (gps_logs has lat/lng/signal/status/logged_at) —
    // there's no speed, ignition, or battery telemetry anywhere in this app,
    // including the web GPS Tracker, which hardcodes speed to 0.
    // Sinotrack ST-901L trackers report to our Traccar server (credentials in
    // .env, gitignored — see TRACCAR_URL/USER/PASS); a vehicle opts into live
    // tracking by having vehicles.gps_device_id set to that tracker's Traccar
    // identifier. Vehicles without one fall back to whatever was last logged
    // locally in gps_logs (legacy/manual entries), so this stays backward
    // compatible with vehicles that don't have a physical tracker yet.
    public function vehicleLocation($plate)
    {
        $vehicle = (new VehicleModel())->where('plate_no', $plate)->first();
        if (!$vehicle) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'No vehicle found for that plate number.']);
        }

        // Static/manually-tracked vehicle info, not something any tracker
        // reports — merged into both the live and fallback responses so the
        // tracking screen always has the full picture in one call.
        $vehicleStatus = [
            'tire_pressure_psi' => $vehicle['tire_pressure_psi'] !== null ? (float) $vehicle['tire_pressure_psi'] : null,
            'inspection_status' => $vehicle['inspection_status'],
            'availability'      => $vehicle['availability'],
        ];

        if (!empty($vehicle['gps_device_id'])) {
            $live = $this->traccarLatestPosition($vehicle['gps_device_id']);
            if ($live) {
                return $this->response->setJSON(array_merge($live, $vehicleStatus));
            }
            // Tracker assigned but Traccar has no fix yet (just installed,
            // out of signal, etc.) — fall through to any local log instead
            // of a hard failure.
        }

        $log = (new GPSModel())->where('vehicle_id', $vehicle['id'])->orderBy('id', 'DESC')->first();
        if (!$log || $log['latitude'] === null || $log['longitude'] === null) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'No GPS location recorded for this vehicle yet.']);
        }

        return $this->response->setJSON(array_merge([
            'lat'             => (float) $log['latitude'],
            'lng'             => (float) $log['longitude'],
            'gps_status'      => $log['status'] ?? $vehicle['gps_status'],
            'signal_strength' => $log['signal_strength'],
            'device_id'       => $log['device_id'],
            'last_update'     => $log['logged_at'],
        ], $vehicleStatus));
    }

    /** Fetch a Traccar device's latest position by its identifier (uniqueId). Returns null on any failure. */
    private function traccarLatestPosition(string $identifier): ?array
    {
        $baseUrl = env('TRACCAR_URL');
        $user    = env('TRACCAR_USER');
        $pass    = env('TRACCAR_PASS');
        if (!$baseUrl || !$user || !$pass) {
            return null;
        }

        $auth = base64_encode("{$user}:{$pass}");
        $opts = [
            'http' => [
                'method'        => 'GET',
                'header'        => "Authorization: Basic {$auth}\r\nAccept: application/json\r\n",
                'timeout'       => 5,
                'ignore_errors' => true,
            ],
        ];
        $context = stream_context_create($opts);

        $devicesJson = @file_get_contents(
            $baseUrl . '/api/devices?uniqueId=' . urlencode($identifier),
            false,
            $context
        );
        $devices = json_decode((string) $devicesJson, true);
        if (empty($devices[0]['id'])) {
            return null;
        }

        $positionsJson = @file_get_contents(
            $baseUrl . '/api/positions?deviceId=' . (int) $devices[0]['id'],
            false,
            $context
        );
        $positions = json_decode((string) $positionsJson, true);
        if (empty($positions[0])) {
            return null;
        }
        $p = $positions[0];
        $attrs = $p['attributes'] ?? [];

        return [
            'lat'             => (float) $p['latitude'],
            'lng'             => (float) $p['longitude'],
            'gps_status'      => $devices[0]['status'] === 'online' ? 'Online' : 'Offline',
            'signal_strength' => null,
            'device_id'       => $identifier,
            'last_update'     => $p['fixTime'],
            'speed_kmh'       => round(((float) $p['speed']) * 1.852, 1), // knots -> km/h
            'course'          => $p['course'] ?? null,
            // Decoded straight from the tracker's own protocol — real
            // telemetry, not guessed from the device's undocumented raw I/O
            // fields (those vary per firmware and aren't safe to assume).
            'ignition'        => array_key_exists('ignition', $attrs) ? (bool) $attrs['ignition'] : null,
            'motion'          => array_key_exists('motion', $attrs) ? (bool) $attrs['motion'] : null,
            'odometer_km'     => isset($attrs['totalDistance']) ? round(((float) $attrs['totalDistance']) / 1000, 1) : null,
        ];
    }

    // ---------- TRIP TICKETS ----------

    public function nextTripTicket()
    {
        $empId = $this->request->getGet('employee_id');
        if (empty($empId)) {
            return $this->response->setJSON(['ticket' => null]);
        }

        $personnelModel = new PersonnelModel();
        $driver = $personnelModel->getByEmpId($empId);
        if (!$driver) {
            return $this->response->setJSON(['ticket' => null]);
        }

        $tripModel = new TravelRequestModel();
        $ticket = $tripModel->select('travel_requests.*, v.vehicle_name, v.plate_no')
                             ->join('vehicles v', 'v.id = travel_requests.assigned_vehicle_id', 'left')
                             ->where('assigned_driver_id', $driver['id'])
                             ->where('status', 'Approved')
                             ->where('check_out_time IS NULL')
                             ->orderBy('travel_date', 'ASC')
                             ->orderBy('departure_time', 'ASC')
                             ->first();

        if (!$ticket) {
            return $this->response->setJSON(['ticket' => null]);
        }

        $vehicleName = $ticket['vehicle_name'] ?? '';
        $plateNo     = $ticket['plate_no'] ?? '';
        $vehicleLabel = ($vehicleName && $plateNo) ? "{$vehicleName} ({$plateNo})" : ($vehicleName ?: $plateNo ?: '—');

        return $this->response->setJSON([
            'ticket' => [
                'id'          => $ticket['id'],
                'ticket_no'   => $ticket['trip_id'],
                'driver'      => $driver['full_name'],
                'vehicle'     => $vehicleLabel,
                'destination' => $ticket['destination'],
                'departure'   => date('h:i A', strtotime($ticket['departure_time'])),
                'return_time' => date('h:i A', strtotime($ticket['return_time'])),
            ],
        ]);
    }

    public function tripScanIn($id)
    {
        $body = $this->request->getJSON(true) ?? [];
        $model = new TravelRequestModel();

        $model->update($id, [
            'check_in_time' => date('Y-m-d H:i:s'),
            'scanned_id'    => $body['code'] ?? null,
        ]);

        return $this->response->setJSON(['message' => "Trip ticket {$id} scanned in"]);
    }

    public function tripScanOut($id)
    {
        $body = $this->request->getJSON(true) ?? [];
        $model = new TravelRequestModel();

        $model->update($id, [
            'check_out_time' => date('Y-m-d H:i:s'),
            'scanned_id'     => $body['code'] ?? null,
            'status'         => 'Completed',
        ]);

        return $this->response->setJSON(['message' => "Trip ticket {$id} scanned out"]);
    }

    // ---------- SAFETY ----------

    public function safetyBuildings()
    {
        $today = date('Y-m-d');
        $units = (new FireExtinguisherModel())->findAll();

        // Per-building breakdown, matching the same status logic safetySummary()
        // uses for the dashboard totals — so the "Needs attention"/"Due for
        // refill"/readiness stat cards can filter this same building list down
        // to exactly the buildings contributing to each number.
        $total = [];
        $attention = [];
        $refill = [];
        $overdue = [];
        foreach ($units as $u) {
            $loc = $u['location'];
            $total[$loc] = ($total[$loc] ?? 0) + 1;
            if (in_array($u['status'], ['Defective', 'Missing'], true)) {
                $attention[$loc] = ($attention[$loc] ?? 0) + 1;
            }
            if ($u['status'] === 'Refillable') {
                $refill[$loc] = ($refill[$loc] ?? 0) + 1;
            }
            if (!empty($u['next_due']) && $u['next_due'] < $today) {
                $overdue[$loc] = ($overdue[$loc] ?? 0) + 1;
            }
        }

        $buildings = [];
        foreach (self::ZONE_SLUGS as $name => $slug) {
            $buildings[] = [
                'key'                => $slug,
                'name'               => $name,
                'extinguisher_count' => $total[$name] ?? 0,
                'needs_attention'    => $attention[$name] ?? 0,
                'due_for_refill'     => $refill[$name] ?? 0,
                'overdue_inspection' => $overdue[$name] ?? 0,
            ];
        }

        return $this->response->setJSON(['buildings' => $buildings]);
    }

    // Mirrors the same status logic as Api\SafetyController::index() (used by
    // the web dashboard) and SafetyController::index() (web view), so the
    // mobile overview always agrees with the web Maintenance page.
    public function safetySummary()
    {
        $feModel = new FireExtinguisherModel();
        $units   = $feModel->findAll();
        $today   = date('Y-m-d');

        $needsAttention = array_filter($units, fn($u) => in_array($u['status'], ['Defective', 'Missing'], true));
        $dueForRefill   = array_filter($units, fn($u) => $u['status'] === 'Refillable');
        $overdue        = array_filter($units, fn($u) => !empty($u['next_due']) && $u['next_due'] < $today);

        $total     = count($units);
        $readiness = $total > 0 ? round(($total - count($overdue)) / $total * 100) : 100;

        $airconModel = new AirconUnitModel();
        $airconUnits = $airconModel->findAll();
        $airconNeedsAttention = array_filter($airconUnits, fn($u) =>
            $u['condition_status'] !== 'Operational'
            || (!empty($u['next_schedule']) && $u['next_schedule'] < $today)
        );

        return $this->response->setJSON([
            'fire_extinguishers' => [
                'total'                => $total,
                'inspection_readiness' => $readiness,
                'needs_attention'      => count($needsAttention),
                'due_for_refill'       => count($dueForRefill),
            ],
            'aircon' => [
                'total'           => count($airconUnits),
                'needs_attention' => count($airconNeedsAttention),
            ],
        ]);
    }

    public function safetyExtinguishers($slug)
    {
        $locationName = array_search($slug, self::ZONE_SLUGS, true);
        if (!$locationName) {
            return $this->response->setJSON(['units' => []]);
        }

        $model = new FireExtinguisherModel();
        $units = $model->where('location', $locationName)->findAll();

        return $this->response->setJSON([
            'units' => array_map(fn($u) => [
                'id'        => (int) $u['id'],
                'unit_id'   => $u['unit_id'],
                'type'      => $u['type'],
                'kg'        => (float) $u['weight_kg'],
                'inspector' => $u['inspector'],
                'next_due'  => $u['next_due'],
                'status'    => $u['status'],
            ], $units),
        ]);
    }

    public function safetyAircon()
    {
        $slug = $this->request->getGet('building');
        $locationName = array_search($slug, self::ZONE_SLUGS, true);
        if (!$locationName) {
            return $this->response->setJSON(['unit' => null]);
        }

        $model = new AirconUnitModel();
        $unit  = $model->getByLocation($locationName);

        if (!$unit) {
            return $this->response->setJSON(['unit' => null]);
        }

        $checklistModel = new AirconChecklistItemModel();
        $tasks = $checklistModel->getForUnit((int) $unit['id']);

        return $this->response->setJSON([
            'unit' => [
                'id'             => (int) $unit['id'],
                'unit'           => $unit['unit_name'],
                'location'       => $unit['location'],
                'last_cleaning'  => $unit['last_cleaning'],
                'next_schedule'  => $unit['next_schedule'],
                'condition'      => $unit['condition_status'],
                'assigned_tech'  => $unit['assigned_tech'],
                'checklist'      => array_map(fn($t) => [
                    'id'           => $t['id'],
                    'task'         => $t['task_name'],
                    'done'         => (bool) $t['is_done'],
                    'completed_at' => $this->toIso($t['completed_at']),
                ], $tasks),
            ],
        ]);
    }

    // Registers a new aircon unit for a building (there was previously no way
    // to add one — only to read one that already existed) and seeds it with
    // a standard maintenance checklist.
    public function saveAirconUnit()
    {
        $data = $this->request->getJSON(true) ?? [];
        $slug = trim((string) ($data['building'] ?? ''));
        $locationName = array_search($slug, self::ZONE_SLUGS, true);

        if (!$locationName) {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'Unknown building.']);
        }

        $unitName = trim((string) ($data['unit_name'] ?? ''));
        if ($unitName === '') {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'Unit name/model is required.']);
        }

        $model = new AirconUnitModel();
        $id = $model->insert([
            'location'          => $locationName,
            'unit_name'         => $unitName,
            'last_cleaning'     => $data['last_cleaning'] ?: null,
            'next_schedule'     => $data['next_schedule'] ?: null,
            'condition_status'  => $data['condition'] ?: 'Operational',
            'assigned_tech'     => $data['assigned_tech'] ?: null,
        ]);

        (new AirconChecklistItemModel())->seedDefaultTasks((int) $id);

        (new NotificationModel())->insert([
            'category'    => 'Aircon Unit Registered',
            'description' => "New aircon unit ({$unitName}) registered at {$locationName}.",
            'recipient'   => 'Maintenance Team',
            'priority'    => 'ROUTINE',
            'status'      => 'Pending',
            'is_read'     => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['message' => 'Aircon unit saved', 'id' => $id]);
    }

    public function saveAirconChecklist($unitId)
    {
        $data = $this->request->getJSON(true) ?? [];
        $checklistModel = new AirconChecklistItemModel();

        foreach (($data['tasks'] ?? []) as $t) {
            if (empty($t['id'])) continue;

            $current = $checklistModel->find($t['id']);
            if (!$current) continue;

            $nowDone = !empty($t['done']);
            $wasDone = (int) $current['is_done'] === 1;

            $update = ['is_done' => $nowDone ? 1 : 0];
            // Only stamp/clear completed_at when the checkbox state actually
            // changes — the app resends the whole checklist on every single
            // toggle, so unconditionally re-stamping here would reset every
            // already-completed task's time to "now" each time.
            if ($nowDone !== $wasDone) {
                $update['completed_at'] = $nowDone ? date('Y-m-d H:i:s') : null;
            }

            $checklistModel->update($t['id'], $update);
        }

        $tasks = $checklistModel->getForUnit((int) $unitId);

        // The whole checklist just got completed (not merely one task ticked
        // mid-cleaning) — that's the "cleaned the aircon" event worth a
        // report, not every individual checkbox save.
        if (!empty($tasks) && count(array_filter($tasks, fn($t) => (int) $t['is_done'] === 1)) === count($tasks)) {
            $unit = (new AirconUnitModel())->find((int) $unitId);
            $apiUser = $this->currentApiUser();
            $empId = $apiUser['employee_id'] ?? ($data['employee_id'] ?? null);
            $this->logMaintenanceReport(
                'Aircon Cleaning Completed — ' . ($unit['unit_name'] ?? "Unit {$unitId}") . ' (' . ($unit['location'] ?? 'Unknown location') . ')',
                'Maintenance Compliance',
                $empId
            );
        }

        return $this->response->setJSON([
            'message'   => "Aircon checklist for unit {$unitId} saved",
            'checklist' => array_map(fn($t) => [
                'id' => $t['id'], 'task' => $t['task_name'], 'done' => (bool) $t['is_done'],
                'completed_at' => $this->toIso($t['completed_at']),
            ], $tasks),
        ]);
    }

    public function saveExtinguisher()
    {
        $data = $this->request->getJSON(true) ?? [];
        $model = new FireExtinguisherModel();

        $locationName = array_search($data['building'] ?? '', self::ZONE_SLUGS, true) ?: ($data['building'] ?? '');

        // unit_code comes from scanning the extinguisher's own QR tag — falls back to an
        // auto-generated code if the tag couldn't be scanned (unit_id is UNIQUE).
        $unitCode = trim((string) ($data['unit_code'] ?? ''));
        if ($unitCode === '' || $model->where('unit_id', $unitCode)->countAllResults() > 0) {
            $unitCode = 'FE-' . strtoupper(bin2hex(random_bytes(3)));
        }

        // "Installed by" reflects whoever is logged in when this unit is saved.
        // Priority: the Authorization: Bearer token issued at login (authoritative —
        // can't be spoofed by just editing the request body), then whatever the
        // client explicitly sent in the body, then the web session as a last resort
        // for browser-based testing.
        $apiUser = $this->currentApiUser();
        $installedByName = trim((string) ($apiUser['name'] ?? $data['installed_by'] ?? $data['employee_name'] ?? session()->get('full_name') ?? ''));
        $installedById    = trim((string) ($apiUser['employee_id'] ?? $data['employee_id'] ?? session()->get('emp_id') ?? ''));
        $inspector = $installedByName !== '' ? $installedByName : ($installedById !== '' ? $installedById : null);

        $type = in_array($data['type'] ?? '', ['CO2', 'Dry Chemical', 'Wet Chemical', 'Foam'], true) ? $data['type'] : 'Dry Chemical';

        $id = $model->insert([
            'unit_id'         => $unitCode,
            'type'            => $type,
            'location'        => $locationName,
            'weight_kg'       => (float) ($data['kg'] ?? 6.0),
            'last_inspection' => $data['inspected'] ?? null,
            'next_due'        => $data['expiry'] ?? null,
            'status'          => 'New',
            'year_acquired'   => !empty($data['installed']) ? date('Y', strtotime($data['installed'])) : date('Y'),
            'inspector'       => $inspector,
            'notes'           => $data['notes'] ?? null,
        ]);

        // Surface every newly-installed unit on the Notification Center, same
        // as any other operational alert — so Safety/Ops sees it without
        // having to go check the map themselves.
        $locationText = $locationName !== '' ? $locationName : 'an unspecified building';
        $byText       = $inspector !== null ? " by {$inspector}" : '';
        (new NotificationModel())->insert([
            'category'    => 'Fire Extinguisher Installed',
            'description' => "New {$type} fire extinguisher ({$unitCode}) installed at {$locationText}{$byText}.",
            'recipient'   => 'Safety Team',
            'priority'    => 'ROUTINE',
            'status'      => 'Pending',
            'is_read'     => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->logMaintenanceReport(
            'Fire Extinguisher Replaced/Renewed — ' . $unitCode . ' (' . $locationText . ')',
            'Maintenance Compliance',
            $apiUser['employee_id'] ?? $installedById
        );

        return $this->response->setJSON(['message' => 'Fire extinguisher record saved', 'id' => $id, 'unit_id' => $unitCode]);
    }

    // ---------- JANITORIAL ----------

    public function janitorialZones()
    {
        $this->resetStaleCompletedJanitorialTasks();

        $assignmentModel = new JanitorialAssignmentModel();
        $taskModel       = new JanitorialTaskModel();

        $assignments = $assignmentModel->findAll();
        $allTasks    = $taskModel->findAll();

        $tasksByAssignment = [];
        foreach ($allTasks as $task) {
            $tasksByAssignment[$task['assignment_id']][] = $task;
        }

        $zones = [];
        foreach ($assignments as $a) {
            $tasks = $tasksByAssignment[$a['id']] ?? [];
            $total = count($tasks);
            $done  = count(array_filter($tasks, fn($t) => (int) $t['is_done'] === 1));

            if ($total === 0) {
                $status = 'pending';
            } elseif ($done === $total) {
                $status = 'done';
            } elseif ($done === 0 && strtotime($a['date_assigned'] . ' ' . $a['shift_end']) < time()) {
                $status = 'missed';
            } else {
                $status = 'pending';
            }

            $zones[] = ['id' => $a['id'], 'name' => $a['assigned_zone'], 'status' => $status];
        }

        return $this->response->setJSON(['zones' => $zones]);
    }

    public function janitorialChecklist($assignmentId)
    {
        $this->resetStaleCompletedJanitorialTasks();

        $taskModel = new JanitorialTaskModel();
        $tasks = $taskModel->getForAssignment((int) $assignmentId);

        return $this->response->setJSON([
            'tasks' => array_map(fn($t) => [
                'id'           => $t['id'],
                'task'         => $t['task_name'],
                'done'         => (bool) $t['is_done'],
                'completed_at' => $this->toIso($t['completed_at']),
            ], $tasks),
        ]);
    }

    public function saveJanitorialChecklist($assignmentId)
    {
        $data = $this->request->getJSON(true) ?? [];
        $taskModel = new JanitorialTaskModel();

        foreach (($data['tasks'] ?? []) as $t) {
            if (empty($t['id'])) continue;

            $current = $taskModel->find($t['id']);
            if (!$current) continue;

            $nowDone = !empty($t['done']);
            $wasDone = (int) $current['is_done'] === 1;

            $update = ['is_done' => $nowDone ? 1 : 0];
            // Only stamp/clear completed_at when the checkbox state actually
            // changes — the app resends the whole checklist on every save,
            // so unconditionally re-stamping here would reset every
            // already-completed task's time to "now" each time.
            if ($nowDone !== $wasDone) {
                $update['completed_at'] = $nowDone ? date('Y-m-d H:i:s') : null;
            }

            $taskModel->update($t['id'], $update);
        }

        $tasks = $taskModel->getForAssignment((int) $assignmentId);

        return $this->response->setJSON([
            'message' => "Checklist for zone {$assignmentId} saved",
            'tasks'   => array_map(fn($t) => [
                'id' => $t['id'], 'task' => $t['task_name'], 'done' => (bool) $t['is_done'],
                'completed_at' => $this->toIso($t['completed_at']),
            ], $tasks),
        ]);
    }

    public function janitorialHistory()
    {
        $this->resetStaleCompletedJanitorialTasks();

        $assignmentModel = new JanitorialAssignmentModel();
        $taskModel       = new JanitorialTaskModel();
        $historyModel    = new JanitorialTaskHistoryModel();

        $assignmentsById = [];
        foreach ($assignmentModel->findAll() as $a) {
            $assignmentsById[$a['id']] = $a;
        }

        $history = [];

        // Completions from earlier days are archived here before the daily
        // reset clears them off the live checklist — pull those in too so
        // the activity trail isn't lost once a task resets.
        foreach ($historyModel->findAll() as $h) {
            $history[] = [
                'zone'         => $h['zone'],
                'task'         => $h['task_name'],
                'status'       => $h['status'],
                'completed_at' => $this->toIso($h['completed_at']),
                'performed_by' => $h['performed_by'],
            ];
        }

        // Today's completions, plus anything still pending/missed on the live checklist.
        foreach ($taskModel->findAll() as $t) {
            $a = $assignmentsById[$t['assignment_id']] ?? null;
            if (!$a) continue;

            $isDone = (int) $t['is_done'] === 1;
            if ($isDone) {
                $status = 'done';
            } elseif (strtotime($a['date_assigned'] . ' ' . $a['shift_end']) < time()) {
                $status = 'missed';
            } else {
                $status = 'pending';
            }

            $history[] = [
                'zone'         => $a['assigned_zone'],
                'task'         => $t['task_name'],
                'status'       => $status,
                'completed_at' => $this->toIso($t['completed_at']),
                'performed_by' => $isDone ? ($a['staff_name'] ?? null) : null,
            ];
        }

        usort($history, fn($x, $y) => strcmp($y['completed_at'] ?? '', $x['completed_at'] ?? ''));

        return $this->response->setJSON(['history' => $history]);
    }

    /**
     * Daily maintenance tasks should reset once finished so staff see a
     * clean checklist next shift — but pending/missed tasks must NOT reset;
     * they stay open until someone actually completes them. So this only
     * touches tasks marked done on a previous day, archiving each one into
     * janitorial_task_history first so "activity history" keeps the record.
     */
    private function resetStaleCompletedJanitorialTasks(): void
    {
        $taskModel = new JanitorialTaskModel();
        $today = date('Y-m-d');

        $staleTasks = array_filter(
            $taskModel->where('is_done', 1)->findAll(),
            fn($t) => !empty($t['completed_at']) && substr($t['completed_at'], 0, 10) !== $today
        );

        if (empty($staleTasks)) return;

        $assignmentModel = new JanitorialAssignmentModel();
        $historyModel    = new JanitorialTaskHistoryModel();
        $assignmentsById = [];
        foreach ($assignmentModel->findAll() as $a) {
            $assignmentsById[$a['id']] = $a;
        }

        foreach ($staleTasks as $t) {
            $a = $assignmentsById[$t['assignment_id']] ?? null;

            $historyModel->insert([
                'assignment_id' => $t['assignment_id'],
                'zone'          => $a['assigned_zone'] ?? 'Unknown',
                'task_name'     => $t['task_name'],
                'status'        => 'done',
                'completed_at'  => $t['completed_at'],
                'performed_by'  => $a['staff_name'] ?? null,
                'archived_at'   => date('Y-m-d H:i:s'),
            ]);

            $taskModel->update($t['id'], ['is_done' => 0, 'completed_at' => null]);
        }
    }

    public function janitorialConsumables()
    {
        $inventoryModel = new ConsumableInventoryModel();
        $items = $inventoryModel->orderBy('item_name', 'ASC')->findAll();

        return $this->response->setJSON([
            'items' => array_map(fn($i) => [
                'id'                => $i['id'],
                'item_name'         => $i['item_name'],
                'category'          => $i['category'],
                'unit'              => $i['unit'],
                'current_stock'     => (float) $i['current_stock'],
                'reorder_threshold' => (float) $i['reorder_threshold'],
                'low_stock'         => (float) $i['current_stock'] <= (float) $i['reorder_threshold'],
            ], $items),
        ]);
    }

    // POST /api/janitorial/consumables/:id/report — a janitor reports how much
    // of a consumable is left after cleaning a zone (an absolute stock reading,
    // not a delta like refillInventory()). Notifies Facilities via the
    // Notification Center whenever the reported level is at/below the
    // item's reorder threshold.
    public function reportConsumableStock($id)
    {
        $data = $this->request->getJSON(true) ?? [];
        $qty  = array_key_exists('quantity_left', $data) ? (float) $data['quantity_left'] : null;

        if ($qty === null || $qty < 0) {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'Enter a valid remaining quantity (0 or more).']);
        }

        $inventoryModel = new ConsumableInventoryModel();
        $item = $inventoryModel->find($id);
        if (!$item) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Consumable item not found.']);
        }

        $inventoryModel->update($id, ['current_stock' => $qty]);

        $apiUser     = $this->currentApiUser();
        $performedBy = trim((string) ($apiUser['name'] ?? $data['performed_by'] ?? '')) ?: 'Janitorial Staff';
        $zone        = trim((string) ($data['zone'] ?? ''));

        (new ConsumableStockReportModel())->insert([
            'inventory_item_id' => $id,
            'item_name'         => $item['item_name'],
            'zone'              => $zone !== '' ? $zone : null,
            'quantity_left'     => $qty,
            'unit'              => $item['unit'],
            'reported_by'       => $performedBy,
            'reported_at'       => date('Y-m-d H:i:s'),
        ]);

        $threshold = (float) $item['reorder_threshold'];
        $isLow = $qty <= $threshold;

        if ($isLow) {
            $zoneText = $zone !== '' ? " (reported from {$zone})" : '';
            (new NotificationModel())->insert([
                'category'    => 'Consumable Low Stock',
                'description' => "{$item['item_name']} is running low{$zoneText} — {$qty} {$item['unit']} left, reorder threshold is {$threshold} {$item['unit']}. Reported by {$performedBy}.",
                'recipient'   => 'Facilities Team',
                'priority'    => $qty <= 0 ? 'CRITICAL' : 'MODERATE',
                'status'      => 'Unread',
                'channel'     => 'system',
                'is_read'     => 0,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->response->setJSON([
            'message'   => 'Stock level updated.',
            'item'      => ['id' => (int) $id, 'current_stock' => $qty, 'reorder_threshold' => $threshold],
            'low_stock' => $isLow,
        ]);
    }

    /** Normalize a 'Y-m-d H:i:s' MySQL datetime to ISO 8601 so JS can parse it reliably. */
    private function toIso(?string $datetime): ?string
    {
        return $datetime ? str_replace(' ', 'T', $datetime) : null;
    }

    // ---------- GUARD ----------

    public function guardKeylog()
    {
        $model = new KeyBorrowLogModel();
        $logs  = $model->getAllWithTrip();

        return $this->response->setJSON([
            'logs' => array_map(fn($k) => [
                'log_no'       => $k['log_number'],
                'name'         => $k['full_name'],
                'department'   => $k['department'],
                'key_borrowed' => $k['key_item'],
                'scan_in'      => $k['scan_in'] ? date('h:i A', strtotime($k['scan_in'])) : null,
                'scan_out'     => $k['scan_out'] ? date('h:i A', strtotime($k['scan_out'])) : null,
                'status'       => $k['status'],
            ], $logs),
        ]);
    }

    public function guardScanBorrow()
    {
        $body  = $this->request->getJSON(true) ?? [];
        $empId = trim((string) ($body['code'] ?? ''));
        $keyItem = trim((string) ($body['key_item'] ?? ''));

        if ($empId === '' || $keyItem === '') {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'Scan an ID and specify the key/item being borrowed.']);
        }

        $personnelModel = new PersonnelModel();
        $person = $personnelModel->getByEmpId($empId);
        if (!$person) {
            return $this->response->setStatusCode(403)->setJSON(['message' => 'No staff/faculty record found for that ID — students are not authorized to borrow keys.']);
        }

        $model = new KeyBorrowLogModel();
        $nextNo = $model->countAllResults() + 1;

        $model->insert([
            'log_number'    => 'KL-' . str_pad((string) $nextNo, 3, '0', STR_PAD_LEFT),
            'borrower_id'   => $empId,
            'full_name'     => $person['full_name'],
            'department'    => $person['department_name'] ?? '',
            'key_item'      => $keyItem,
            'scan_in'       => date('Y-m-d H:i:s'),
            'status'        => 'Active',
            'guard_on_duty' => $body['guard_name'] ?? null,
        ]);

        return $this->response->setJSON(['message' => 'Key borrow logged']);
    }

    public function guardScanReturn()
    {
        $body  = $this->request->getJSON(true) ?? [];
        $empId = trim((string) ($body['code'] ?? ''));

        $model = new KeyBorrowLogModel();
        $log = $model->where('borrower_id', $empId)->where('status', 'Active')->orderBy('id', 'DESC')->first();

        if (!$log) {
            return $this->response->setStatusCode(409)->setJSON(['message' => 'No active key borrow found for that ID.']);
        }

        $model->update($log['id'], [
            'scan_out' => date('Y-m-d H:i:s'),
            'status'   => 'Returned',
        ]);

        return $this->response->setJSON(['message' => 'Key return logged']);
    }

    public function guardTripTicketsToday()
    {
        $model = new TravelRequestModel();
        $rows  = $model->getTodayWithDetails();

        return $this->response->setJSON([
            'tickets' => array_map(function ($t) {
                $status = 'Pending';
                if (!empty($t['check_in_time']) && empty($t['check_out_time'])) $status = 'In transit';
                if (!empty($t['check_out_time'])) $status = 'Completed';

                return [
                    'trip_id'     => $t['trip_id'],
                    'driver'      => $t['driver_name'] ?? '—',
                    'vehicle'     => $t['vehicle_name'] ?? '—',
                    'plate'       => $t['plate_no'] ?? '—',
                    'destination' => $t['destination'],
                    'departure'   => date('h:i A', strtotime($t['departure_time'])),
                    'return_time' => !empty($t['check_out_time']) ? date('h:i A', strtotime($t['check_out_time'])) : null,
                    'status'      => $status,
                ];
            }, $rows),
        ]);
    }

    // ---------- NOTIFICATIONS ----------

    public function notifications()
    {
        $model = new NotificationModel();
        $rows  = $model->getAllSorted();

        return $this->response->setJSON([
            'notifications' => array_map(fn($n) => [
                'id'          => (int) $n['id'],
                'category'    => $n['category'],
                'description' => $n['description'],
                'priority'    => $n['priority'],
                'status'      => $n['status'],
                'is_read'     => (bool) $n['is_read'],
                'created_at'  => $n['created_at'],
            ], $rows),
            'unread_count' => $model->getUnreadCount(),
        ]);
    }

    public function notificationsUnreadCount()
    {
        return $this->response->setJSON(['count' => (new NotificationModel())->getUnreadCount()]);
    }

    public function markNotificationRead($id)
    {
        (new NotificationModel())->update($id, ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['message' => 'Notification marked as read']);
    }

    // ---------- FACILITIES MAINTENANCE PROGRAM FORMS ----------
    // Mobile equivalents of the 5 web admin screens under MaintenanceFormsController
    // — same tables, so a submission from the field shows up on the web
    // screens (and the Information Hub, via logMaintenanceReport()) exactly
    // like an admin-entered one would.

    // Facility Maintenance Checklist: inspector fills the whole fixed
    // 21-item form in one submission. items keyed by item_code ("1.1" etc.)
    // since that's the one stable identifier that exists before the row is
    // created — {"1.1": {"rating": "C", "corrective_action": "..."}, ...}.
    public function saveFacilityChecklist()
    {
        $data = $this->request->getJSON(true) ?? [];

        $model = new FacilityChecklistModel();
        $id = $model->createWithItems([
            'inspector'       => $data['inspector'] ?? null,
            'building_area'   => $data['building_area'] ?? null,
            'inspection_date' => ($data['inspection_date'] ?? null) ?: date('Y-m-d'),
            'inspection_type' => $data['inspection_type'] ?? null,
        ]);

        $itemModel = new FacilityChecklistItemModel();
        $seeded = $itemModel->where('checklist_id', $id)->findAll();
        foreach ($seeded as $item) {
            $submitted = $data['items'][$item['item_code']] ?? null;
            if ($submitted === null) continue;
            $itemModel->update($item['id'], [
                'rating'            => $submitted['rating'] ?: null,
                'corrective_action' => $submitted['corrective_action'] ?? null,
            ]);
        }

        $apiUser = $this->currentApiUser();
        $this->logMaintenanceReport(
            'Facility Inspection Completed — ' . ($data['building_area'] ?? 'Unspecified area'),
            'Facilities Management',
            $apiUser['employee_id'] ?? ($data['employee_id'] ?? null)
        );

        return $this->response->setJSON(['message' => 'Facility checklist saved', 'id' => $id]);
    }

    // Equipment Maintenance Log: one submission = one log sheet + one entry,
    // created together — the mobile app isn't expected to track "is there
    // already an open sheet for this department today", it just submits.
    public function saveEquipmentLogEntry()
    {
        $data = $this->request->getJSON(true) ?? [];

        $logId = (new EquipmentMaintenanceLogModel())->insert([
            'department'     => $data['department'] ?? null,
            'date_submitted' => ($data['date_submitted'] ?? null) ?: date('Y-m-d'),
        ]);

        (new EquipmentMaintenanceEntryModel())->insert([
            'log_id'                => $logId,
            'entry_date'            => ($data['entry_date'] ?? null) ?: date('Y-m-d'),
            'asset_name'            => $data['asset_name'] ?? '',
            'serial_number'         => $data['serial_number'] ?? null,
            'maintenance_frequency' => $data['maintenance_frequency'] ?? null,
            'work_description'      => $data['work_description'] ?? null,
            'status'                => $data['status'] ?? null,
            'next_due_date'         => $data['next_due_date'] ?? null,
            'performed_by'          => $data['performed_by'] ?? null,
            'signature'             => $data['signature'] ?? null,
        ]);

        $apiUser = $this->currentApiUser();
        $this->logMaintenanceReport(
            'Equipment Maintenance Logged — ' . ($data['asset_name'] ?? 'Asset') . ' (' . ($data['department'] ?? 'Unspecified dept') . ')',
            'Facilities Management',
            $apiUser['employee_id'] ?? ($data['employee_id'] ?? null)
        );

        return $this->response->setJSON(['message' => 'Equipment log entry saved', 'log_id' => $logId]);
    }

    // Aircon Inspection Log (F-FAC-PMP-AIL-003) — distinct from the
    // aircon_units/aircon_checklist_items table saveAirconChecklist() above
    // uses; that one tracks per-unit cleaning checklists, this one is the
    // separate department log-sheet form. Same one-submission-does-both
    // pattern as the equipment log.
    public function saveAirconInspectionEntry()
    {
        $data = $this->request->getJSON(true) ?? [];

        $logId = (new AirconInspectionLogModel())->insert([
            'performed_by'   => $data['performed_by'] ?? null,
            'date_submitted' => ($data['date_submitted'] ?? null) ?: date('Y-m-d'),
        ]);

        (new AirconInspectionEntryModel())->insert([
            'log_id'      => $logId,
            'entry_date'  => ($data['entry_date'] ?? null) ?: date('Y-m-d'),
            'department'  => $data['department'] ?? null,
            'qty'         => $data['qty'] ?? null,
            'room_no'     => $data['room_no'] ?? null,
            'aircon_type' => $data['aircon_type'] ?? null,
            'work_done'   => $data['work_done'] ?? null,
            'remarks'     => $data['remarks'] ?? null,
        ]);

        $apiUser = $this->currentApiUser();
        $this->logMaintenanceReport(
            'Aircon Inspection Logged — Room ' . ($data['room_no'] ?? '—') . ' (' . ($data['department'] ?? 'Unspecified dept') . ')',
            'Facilities Management',
            $apiUser['employee_id'] ?? ($data['employee_id'] ?? null)
        );

        return $this->response->setJSON(['message' => 'Aircon inspection entry saved', 'log_id' => $logId]);
    }

    // Vehicle Maintenance Inspection Checklist: same whole-form-at-once
    // pattern as the facility checklist. Vehicle items have no stable code
    // (only their label text), so items are matched by [section, item_label]
    // pair instead — {"section": "...", "item_label": "...", "response":
    // "Yes", "remarks": "..."}.
    public function saveVehicleChecklist()
    {
        $data = $this->request->getJSON(true) ?? [];

        $model = new VehicleInspectionChecklistModel();
        $id = $model->createWithItems([
            'vehicle_type'       => $data['vehicle_type'] ?? null,
            'plate_no'           => $data['plate_no'] ?? null,
            'odometer_reading'   => $data['odometer_reading'] ?? null,
            'mechanic_inspector' => $data['mechanic_inspector'] ?? null,
            'next_pm_schedule'   => $data['next_pm_schedule'] ?? null,
            'inspection_date'    => ($data['inspection_date'] ?? null) ?: date('Y-m-d'),
        ]);

        $itemModel = new VehicleInspectionItemModel();
        $seeded = $itemModel->where('checklist_id', $id)->findAll();
        $submittedItems = (array) ($data['items'] ?? []);
        foreach ($seeded as $item) {
            foreach ($submittedItems as $s) {
                if (($s['section'] ?? '') === $item['section'] && ($s['item_label'] ?? '') === $item['item_label']) {
                    $itemModel->update($item['id'], [
                        'response' => $s['response'] ?: null,
                        'remarks'  => $s['remarks'] ?? null,
                    ]);
                    break;
                }
            }
        }

        $apiUser = $this->currentApiUser();
        $this->logMaintenanceReport(
            'Vehicle Inspection Completed — ' . ($data['plate_no'] ?? 'Unspecified vehicle'),
            'Vehicle Fleet',
            $apiUser['employee_id'] ?? ($data['employee_id'] ?? null)
        );

        return $this->response->setJSON(['message' => 'Vehicle checklist saved', 'id' => $id]);
    }

    // Restroom Checklist: one submission = one checklist (by location) + one
    // cleaning-round entry, same header+entry-together pattern.
    public function saveRestroomChecklistEntry()
    {
        $data = $this->request->getJSON(true) ?? [];

        $checklistId = (new RestroomChecklistModel())->insert([
            'location' => $data['location'] ?? '',
        ]);

        (new RestroomChecklistEntryModel())->insert([
            'checklist_id' => $checklistId,
            'entry_date'   => ($data['entry_date'] ?? null) ?: date('Y-m-d'),
            'entry_time'   => ($data['entry_time'] ?? null) ?: date('H:i:s'),
            'empty_trash'  => !empty($data['empty_trash']) ? 1 : 0,
            'refill_paper' => !empty($data['refill_paper']) ? 1 : 0,
            'refill_soap'  => !empty($data['refill_soap']) ? 1 : 0,
            'clean_floor'  => !empty($data['clean_floor']) ? 1 : 0,
            'clean_sink'   => !empty($data['clean_sink']) ? 1 : 0,
            'clean_toilet' => !empty($data['clean_toilet']) ? 1 : 0,
            'cleaned_by'   => $data['cleaned_by'] ?? null,
            'signature'    => $data['signature'] ?? null,
        ]);

        $apiUser = $this->currentApiUser();
        $this->logMaintenanceReport(
            'Restroom Cleaned — ' . ($data['location'] ?? 'Unspecified location'),
            'Janitorial Performance',
            $apiUser['employee_id'] ?? ($data['employee_id'] ?? null)
        );

        return $this->response->setJSON(['message' => 'Restroom checklist entry saved', 'checklist_id' => $checklistId]);
    }
}
