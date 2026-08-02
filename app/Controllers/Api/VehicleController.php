<?php

namespace App\Controllers\Api;

use App\Models\DepartmentModel;
use App\Models\PersonnelModel;
use App\Models\VehicleModel;

class VehicleController extends BaseApiController
{
    protected VehicleModel $vehicleModel;
    protected PersonnelModel $personnelModel;
    protected DepartmentModel $departmentModel;

    public function __construct()
    {
        $this->vehicleModel    = new VehicleModel();
        $this->personnelModel  = new PersonnelModel();
        $this->departmentModel = new DepartmentModel();
    }

    public function index()
    {
        return $this->ok([
            'vehicles'       => $this->vehicleModel->getAllWithDetails(),
            'fleet_stats'    => $this->vehicleModel->getFleetStats(),
            'maintenance_due'=> (clone $this->vehicleModel)->where('inspection_status', 'Due Soon')->countAllResults(false),
            'drivers'        => $this->personnelModel->getDrivers(),
            'departments'    => $this->departmentModel->findAll(),
        ]);
    }

    public function add()
    {
        $id = $this->vehicleModel->insert([
            'vehicle_name'       => $this->request->getPost('vehicle_name'),
            'plate_no'           => $this->request->getPost('plate_no'),
            'type'               => $this->request->getPost('type'),
            'driver_id'          => $this->request->getPost('driver_id') ?: null,
            'department_id'      => $this->request->getPost('department_id') ?: null,
            'gps_status'         => $this->request->getPost('gps_status') ?: 'Offline',
            'inspection_status'  => $this->request->getPost('inspection_status') ?: 'Pending',
            'availability'       => $this->request->getPost('availability') ?: 'Available',
        ], true);

        return $this->ok(['id' => $id], 201);
    }

    public function edit($id = null)
    {
        $this->vehicleModel->update($id, [
            'vehicle_name'       => $this->request->getPost('vehicle_name'),
            'plate_no'           => $this->request->getPost('plate_no'),
            'type'               => $this->request->getPost('type'),
            'driver_id'          => $this->request->getPost('driver_id') ?: null,
            'department_id'      => $this->request->getPost('department_id') ?: null,
            'inspection_status'  => $this->request->getPost('inspection_status'),
            'availability'       => $this->request->getPost('availability'),
        ]);

        return $this->ok();
    }

    public function delete($id = null)
    {
        if ($resp = $this->requireAdminOrFail()) return $resp;

        $this->vehicleModel->update($id, ['is_archived' => 1, 'archived_at' => date('Y-m-d H:i:s')]);
        return $this->ok();
    }
}
