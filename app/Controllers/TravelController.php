<?php

namespace App\Controllers;

use App\Models\TravelModel;
use App\Models\PersonnelModel;
use App\Models\VehicleModel;
use App\Models\DepartmentModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class TravelController extends BaseController
{
    protected $travelModel;
    protected $personnelModel;
    protected $vehicleModel;
    protected $departmentModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->travelModel = new TravelModel();
        $this->personnelModel = new PersonnelModel();
        $this->vehicleModel = new VehicleModel();
        $this->departmentModel = new DepartmentModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $trips = $this->travelModel->getAllWithDetails();
        if (empty($trips)) {
            $today = date('Y-m-d');
            $trips = [
                [
                    'id' => 1001,
                    'trip_id' => 'TT-2026-001',
                    'requester_name' => 'Maria Santos',
                    'department_name' => 'Administration',
                    'destination' => 'Cebu City Hall',
                    'purpose' => 'Official meeting and document handover',
                    'travel_date' => $today,
                    'departure_time' => '08:00:00',
                    'return_time' => '17:00:00',
                    'driver_name' => 'Ramon Cruz',
                    'vehicle_plate' => 'UBR-204',
                    'vehicle_model' => 'Toyota Hiace',
                    'status' => 'Approved',
                ],
                [
                    'id' => 1002,
                    'trip_id' => 'TT-2026-002',
                    'requester_name' => 'Janelle Rivera',
                    'department_name' => 'Library Services',
                    'destination' => 'Mandaue Campus',
                    'purpose' => 'Inventory transfer and outreach',
                    'travel_date' => date('Y-m-d', strtotime('+1 day')),
                    'departure_time' => '09:30:00',
                    'return_time' => '15:30:00',
                    'driver_name' => 'Leo Dela Cruz',
                    'vehicle_plate' => 'UBR-118',
                    'vehicle_model' => 'Toyota Innova',
                    'status' => 'Pending',
                ],
                [
                    'id' => 1003,
                    'trip_id' => 'TT-2026-003',
                    'requester_name' => 'Arnel Bautista',
                    'department_name' => 'Facilities',
                    'destination' => 'Cebu Provincial Office',
                    'purpose' => 'Inspection of maintenance requests',
                    'travel_date' => date('Y-m-d', strtotime('-1 day')),
                    'departure_time' => '07:15:00',
                    'return_time' => '13:00:00',
                    'driver_name' => 'Cris Lim',
                    'vehicle_plate' => 'UBR-310',
                    'vehicle_model' => 'Isuzu ELF',
                    'status' => 'Completed',
                ],
            ];
        }
        $today = date('Y-m-d');
        $pendingCount = 0;
        $approvedCount = 0;
        $todayCount = 0;
        $completedCount = 0;
        $cancelledCount = 0;

        foreach ($trips as $t) {
            switch ($t['status']) {
                case 'Pending': $pendingCount++; break;
                case 'Approved': $approvedCount++; break;
                case 'Completed': $completedCount++; break;
                case 'Cancelled': $cancelledCount++; break;
            }
            if (isset($t['travel_date']) && $t['travel_date'] == $today) {
                $todayCount++;
            }
        }

        $availableVehicles = count($this->vehicleModel->getAvailableVehicles());

        $data = [
            'title'             => 'Driver\'s Trip Ticket',
            'pageCss'           => 'travel.css',
            'trips'             => $trips,
            'personnel'         => $this->personnelModel->findAll(),
            'drivers'           => $this->personnelModel->getDrivers(),
            'vehicles'          => $this->vehicleModel->findAll(),
            'departments'       => $this->departmentModel->findAll(),
            'pending_count'     => $pendingCount,
            'approved_count'    => $approvedCount,
            'today_count'       => $todayCount,
            'completed_count'   => $completedCount,
            'cancelled_count'   => $cancelledCount,
            'available_vehicles' => $availableVehicles,
            'flash_success'     => session()->getFlashdata('success'),
            'flash_error'       => session()->getFlashdata('error')
        ];

        return view('travel/index', $data);
    }

    public function getTrip($id)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(403);
        }

        $trip = $this->travelModel->getTripWithDetails($id);
        if (!$trip) {
            return $this->response->setStatusCode(404);
        }

        return $this->response->setJSON($trip);
    }

    public function add()
    {
        $tripId = $this->travelModel->generateTripId();

        $this->travelModel->insert([
            'trip_id'            => $tripId,
            'requester_id'       => $this->request->getPost('requester_id'),
            'destination'        => $this->request->getPost('destination'),
            'purpose'            => $this->request->getPost('purpose'),
            'travel_date'        => $this->request->getPost('travel_date'),
            'departure_time'     => $this->request->getPost('departure_time'),
            'return_time'        => $this->request->getPost('return_time'),
            'department_id'      => $this->request->getPost('department_id'),
            'assigned_driver_id' => $this->request->getPost('assigned_driver_id'),
            'assigned_vehicle_id'=> $this->request->getPost('assigned_vehicle_id'),
            'scanned_id'         => $this->request->getPost('scanned_id'),
            'status'             => 'Pending',
            'last_activity_at'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/travel')->with('success', 'Trip ticket created successfully.');
    }

    public function edit($id)
    {
        $this->travelModel->update($id, [
            'requester_id'       => $this->request->getPost('requester_id'),
            'destination'        => $this->request->getPost('destination'),
            'purpose'            => $this->request->getPost('purpose'),
            'travel_date'        => $this->request->getPost('travel_date'),
            'departure_time'     => $this->request->getPost('departure_time'),
            'return_time'        => $this->request->getPost('return_time'),
            'department_id'      => $this->request->getPost('department_id'),
            'assigned_driver_id' => $this->request->getPost('assigned_driver_id'),
            'assigned_vehicle_id'=> $this->request->getPost('assigned_vehicle_id'),
            'status'             => $this->request->getPost('status'),
            'scanned_id'         => $this->request->getPost('scanned_id'),
            'last_activity_at'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/travel')->with('success', 'Trip ticket updated successfully.');
    }

    public function delete($id)
    {
        $this->travelModel->update($id, [
            'is_archived' => 1,
            'archived_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/travel')->with('success', 'Trip ticket archived.');
    }

    public function approve($id)
    {
        $data = [
            'status' => 'Approved',
            'last_activity_at' => date('Y-m-d H:i:s'),
        ];

        if ($driverId = $this->request->getPost('assigned_driver_id')) {
            $data['assigned_driver_id'] = $driverId;
        }
        if ($vehicleId = $this->request->getPost('assigned_vehicle_id')) {
            $data['assigned_vehicle_id'] = $vehicleId;
        }

        $this->travelModel->update($id, $data);
        return redirect()->to('/travel')->with('success', 'Trip approved.');
    }

    public function reject($id)
    {
        $this->travelModel->update($id, [
            'status' => 'Rejected',
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/travel')->with('success', 'Trip rejected.');
    }

    public function complete($id)
    {
        $this->travelModel->update($id, [
            'status' => 'Completed',
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/travel')->with('success', 'Trip completed.');
    }
}
