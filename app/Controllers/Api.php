<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ToolsModel;
use App\Models\BorrowModel;
use App\Models\ReturnModel;
use App\Models\VehicleModel;
use App\Models\PersonnelModel;
use App\Models\DepartmentModel;
use App\Models\TravelRequestModel;
use App\Models\FireExtinguisherModel;
use App\Models\AirconUnitModel;
use App\Models\AirconChecklistItemModel;
use App\Models\JanitorialAssignmentModel;
use App\Models\JanitorialTaskModel;
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

        return $this->response->setJSON([
            'tools' => array_map(fn($t) => [
                'asset_id'  => $t['asset_code'],
                'tool_name' => $t['asset_name'],
                'condition' => $t['condition_status'],
                'status'    => $t['availability'],
                'qty'       => 1,
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

        return $this->response->setJSON([
            'asset_id'  => $tool['asset_code'],
            'tool_name' => $tool['asset_name'],
            'category'  => $tool['category'],
            'condition' => $tool['condition_status'],
            'status'    => $tool['availability'],
            'available' => $tool['availability'] === 'Available',
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

        $borrowerName = $body['borrower_name'] ?? ($body['employee_id'] ?? 'Unknown');
        $department   = $body['department'] ?? '';

        $borrowModel->insert([
            'tool_id'         => $tool['id'],
            'borrower'        => $borrowerName,
            'department'      => $department,
            'borrowed_date'   => date('Y-m-d'),
            'expected_return' => date('Y-m-d', strtotime('+7 days')),
            'status'          => 'Borrowed',
            'created_at'      => date('Y-m-d H:i:s'),
            'last_activity_at'=> date('Y-m-d H:i:s'),
        ]);

        $toolsModel->update($tool['id'], [
            'availability'      => 'Borrowed',
            'last_activity_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'action'        => 'borrow confirmed',
            'borrower_name' => $borrowerName,
            'tool_name'     => $tool['asset_name'],
            'asset_id'      => $tool['asset_code'],
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

        $toolsModel->update($tool['id'], [
            'availability'      => 'Available',
            'condition_status'  => $condition,
            'last_activity_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'action'        => 'return confirmed',
            'borrower_name' => $borrowRecord['borrower'],
            'tool_name'     => $tool['asset_name'],
            'asset_id'      => $tool['asset_code'],
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
                'driver'            => $v['driver_name'] ?? null,
                'department'        => $v['department_name'] ?? null,
                'availability'      => $v['availability'],
                'gps_status'        => $v['gps_status'],
                'inspection_status' => $v['inspection_status'],
            ], $rows),
        ]);
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
        $model  = new FireExtinguisherModel();
        $counts = [];
        foreach ($model->getBuildingCounts() as $row) {
            $counts[$row['location']] = (int) $row['count'];
        }

        $buildings = [];
        foreach (self::ZONE_SLUGS as $name => $slug) {
            $buildings[] = [
                'key'                => $slug,
                'name'               => $name,
                'extinguisher_count' => $counts[$name] ?? 0,
            ];
        }

        return $this->response->setJSON(['buildings' => $buildings]);
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
                    'id'   => $t['id'],
                    'task' => $t['task_name'],
                    'done' => (bool) $t['is_done'],
                    'time' => $t['completed_at'] ? date('h:i A', strtotime($t['completed_at'])) : null,
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
            $checklistModel->update($t['id'], [
                'is_done'      => !empty($t['done']) ? 1 : 0,
                'completed_at' => !empty($t['done']) ? date('Y-m-d H:i:s') : null,
            ]);
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
                'time' => $t['completed_at'] ? date('h:i A', strtotime($t['completed_at'])) : null,
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
        $taskModel = new JanitorialTaskModel();
        $tasks = $taskModel->getForAssignment((int) $assignmentId);

        return $this->response->setJSON([
            'tasks' => array_map(fn($t) => [
                'id'   => $t['id'],
                'task' => $t['task_name'],
                'done' => (bool) $t['is_done'],
                'time' => $t['completed_at'] ? date('h:i A', strtotime($t['completed_at'])) : null,
            ], $tasks),
        ]);
    }

    public function saveJanitorialChecklist($assignmentId)
    {
        $data = $this->request->getJSON(true) ?? [];
        $taskModel = new JanitorialTaskModel();

        foreach (($data['tasks'] ?? []) as $t) {
            if (empty($t['id'])) continue;
            $taskModel->update($t['id'], [
                'is_done'      => !empty($t['done']) ? 1 : 0,
                'completed_at' => !empty($t['done']) ? date('Y-m-d H:i:s') : null,
            ]);
        }

        $tasks = $taskModel->getForAssignment((int) $assignmentId);

        return $this->response->setJSON([
            'message' => "Checklist for zone {$assignmentId} saved",
            'tasks'   => array_map(fn($t) => [
                'id' => $t['id'], 'task' => $t['task_name'], 'done' => (bool) $t['is_done'],
                'time' => $t['completed_at'] ? date('h:i A', strtotime($t['completed_at'])) : null,
            ], $tasks),
        ]);
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
