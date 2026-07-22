<?php

namespace App\Controllers;

use App\Models\VehicleModel;
use App\Models\GPSModel;

class VehicleController extends BaseController
{
    protected $vehicleModel;
    protected $gpsModel;

    public function __construct()
    {
        $this->vehicleModel = new VehicleModel();
        $this->gpsModel     = new GPSModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $vehicles = $this->vehicleModel->findAll();

        $data = [
            'title'    => 'Vehicle Management',
            'pageCss'  => 'vehicle.css',
            'vehicles' => $vehicles,
            'total_vehicles'     => count($vehicles),
            'available_vehicles' => $this->vehicleModel->where('availability', 'Available')->countAllResults(),
            'inuse_vehicles'     => $this->vehicleModel->where('availability', 'In Use')->countAllResults(),
            'maintenance_due'    => $this->vehicleModel->where('inspection_status', 'Due Soon')->countAllResults(),
        ];

        return view('vehicles/index', $data);
    }

    public function add()
    {
        $this->vehicleModel->insert([
            'vehicle_name'      => $this->request->getPost('vehicle_name'),
            'plate_no'          => $this->request->getPost('plate_no'),
            'type'              => $this->request->getPost('type'),
            'driver'            => $this->request->getPost('driver'),
            'department'        => $this->request->getPost('department'),
            'gps_status'        => 'Offline',
            'inspection_status' => $this->request->getPost('inspection_status') ?? 'Pending',
            'availability'      => 'Available',
        ]);

        return redirect()->to('/vehicles')->with('success', 'Vehicle registered successfully.');
    }

    public function edit($id)
    {
        $this->vehicleModel->update($id, [
            'vehicle_name'      => $this->request->getPost('vehicle_name'),
            'plate_no'          => $this->request->getPost('plate_no'),
            'type'              => $this->request->getPost('type'),
            'driver'            => $this->request->getPost('driver'),
            'department'        => $this->request->getPost('department'),
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