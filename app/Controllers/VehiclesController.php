<?php

namespace App\Controllers;

use App\Models\VehicleModel;
use App\Models\PersonnelModel;
use App\Models\DepartmentModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class VehiclesController extends BaseController
{
    protected $vehicleModel;
    protected $personnelModel;
    protected $departmentModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->vehicleModel = new VehicleModel();
        $this->personnelModel = new PersonnelModel();
        $this->departmentModel = new DepartmentModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title'       => 'Vehicle Management',
            'vehicles'    => $this->vehicleModel->getAllWithDetails(),
            'personnel'   => $this->personnelModel->findAll(),
            'departments' => $this->departmentModel->findAll(),
        ];

        return view('vehicles/index', $data);
    }

    public function add()
    {
        $this->vehicleModel->insert([
            'vehicle_name'     => $this->request->getPost('vehicle_name'),
            'plate_no'         => $this->request->getPost('plate_no'),
            'type'             => $this->request->getPost('type'),
            'driver_id'        => $this->request->getPost('driver_id') ?: null,
            'department_id'    => $this->request->getPost('department_id') ?: null,
            'gps_status'       => $this->request->getPost('gps_status') ?? 'Offline',
            'inspection_status'=> $this->request->getPost('inspection_status') ?? 'Due Soon',
            'availability'     => $this->request->getPost('availability') ?? 'Available',
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/vehicles')->with('success', 'Vehicle added successfully.');
    }

    public function edit($id)
    {
        $this->vehicleModel->update($id, [
            'vehicle_name'     => $this->request->getPost('vehicle_name'),
            'plate_no'         => $this->request->getPost('plate_no'),
            'type'             => $this->request->getPost('type'),
            'driver_id'        => $this->request->getPost('driver_id') ?: null,
            'department_id'    => $this->request->getPost('department_id') ?: null,
            'gps_status'       => $this->request->getPost('gps_status'),
            'inspection_status'=> $this->request->getPost('inspection_status'),
            'availability'     => $this->request->getPost('availability'),
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/vehicles')->with('success', 'Vehicle updated successfully.');
    }

    public function delete($id)
    {
        $this->vehicleModel->update($id, [
            'is_archived' => 1,
            'archived_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/vehicles')->with('success', 'Vehicle archived.');
    }
}
