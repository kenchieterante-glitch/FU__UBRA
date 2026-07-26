<?php

namespace App\Controllers;

use App\Models\VehicleModel;
use App\Models\GPSModel;
use App\Models\PersonnelModel;
use App\Models\DepartmentModel;

class VehicleController extends BaseController
{
    protected $vehicleModel;
    protected $gpsModel;
    protected $personnelModel;
    protected $departmentModel;

    public function __construct()
    {
        $this->vehicleModel = new VehicleModel();
        $this->gpsModel     = new GPSModel();
        $this->personnelModel = new PersonnelModel();
        $this->departmentModel = new DepartmentModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $vehicles = $this->vehicleModel->getAllWithDetails();

        $data = [
            'title'    => 'Vehicle Management',
            'pageCss'  => 'vehicle.css',
            'vehicles' => $vehicles,
            'total_vehicles'     => count($vehicles),
            'available_vehicles' => $this->vehicleModel->where('availability', 'Available')->countAllResults(),
            'inuse_vehicles'     => $this->vehicleModel->where('availability', 'In Use')->countAllResults(),
            'maintenance_due'    => $this->vehicleModel->where('inspection_status', 'Due Soon')->countAllResults(),
            'personnel'          => $this->personnelModel->findAll(),
            'departments'        => $this->departmentModel->findAll(),
        ];

        return view('vehicles/index', $data);
    }

    public function add()
    {
        $this->vehicleModel->insert([
            'vehicle_name'      => $this->request->getPost('vehicle_name'),
            'plate_no'          => $this->request->getPost('plate_no'),
            'type'              => $this->request->getPost('type'),
            'driver_id'         => $this->request->getPost('driver_id') ?: null,
            'department_id'     => $this->request->getPost('department_id') ?: null,
            'gps_status'        => $this->request->getPost('gps_status') ?? 'Offline',
            'inspection_status' => $this->request->getPost('inspection_status') ?? 'Pending',
            'availability'      => $this->request->getPost('availability') ?? 'Available',
        ]);

        return redirect()->to('/vehicles')->with('success', 'Vehicle registered successfully.');
    }

    public function edit($id)
    {
        $this->vehicleModel->update($id, [
            'vehicle_name'      => $this->request->getPost('vehicle_name'),
            'plate_no'          => $this->request->getPost('plate_no'),
            'type'              => $this->request->getPost('type'),
            'driver_id'         => $this->request->getPost('driver_id') ?: null,
            'department_id'     => $this->request->getPost('department_id') ?: null,
            'inspection_status' => $this->request->getPost('inspection_status'),
            'availability'      => $this->request->getPost('availability'),
        ]);

        return redirect()->to('/vehicles')->with('success', 'Vehicle updated successfully.');
    }

    public function delete($id)
    {
        $this->vehicleModel->delete($id);
        return redirect()->to('/vehicles')->with('success', 'Vehicle removed.');
    }
}