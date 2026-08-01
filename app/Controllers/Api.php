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
use App\Models\JanitorialAssignmentModel;
use App\Models\JanitorialTaskModel;
use App\Models\KeyBorrowLogModel;

/**
 * JSON API layer for the FU-UBRA Expo mobile app — wired to the same tables/Models
 * the web dashboard uses, so data added from mobile shows up on the web dashboard
 * and vice versa.
 */
class Api extends BaseController
{
    // Friendly building name <-> campus-map slug, same mapping used by
    // SafetyController/JanitorialController so mobile and web agree on zones.
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

        // TODO: generate a real signed token (e.g. firebase/php-jwt) instead of this placeholder
        $token = bin2hex(random_bytes(24));

        return $this->response->setJSON([
            'token' => $token,
            'user' => [
                'name'        => $user['full_name'] ?? $user['emp_id'] ?? 'User',
                'employee_id' => $user['emp_id'] ?? $employeeId,
                'department'  => $user['department'] ?? '',
                'role'        => $user['role'] ?? null,
                'is_guard'    => strtolower((string) ($user['role'] ?? '')) === 'guard',
            ],
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

        return $this->response->setJSON([
            'unit' => [
                'unit'           => $unit['unit_name'],
                'location'       => $unit['location'],
                'last_cleaning'  => $unit['last_cleaning'],
                'next_schedule'  => $unit['next_schedule'],
                'condition'      => $unit['condition_status'],
                'assigned_tech'  => $unit['assigned_tech'],
            ],
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

        $id = $model->insert([
            'unit_id'         => $unitCode,
            'type'            => in_array($data['type'] ?? '', ['CO2', 'Dry Chemical', 'Wet Chemical', 'Foam'], true) ? $data['type'] : 'Dry Chemical',
            'location'        => $locationName,
            'weight_kg'       => (float) ($data['kg'] ?? 6.0),
            'last_inspection' => $data['inspected'] ?: null,
            'next_due'        => $data['expiry'] ?: null,
            'status'          => 'New',
            'year_acquired'   => $data['installed'] ? date('Y', strtotime($data['installed'])) : date('Y'),
            'notes'           => $data['notes'] ?? null,
        ]);

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
}
