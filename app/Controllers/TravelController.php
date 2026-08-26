<?php

namespace App\Controllers;

use App\Models\TravelModel;
use App\Models\PersonnelModel;
use App\Models\VehicleModel;
use App\Models\DepartmentModel;
use App\Models\NotificationModel;

class TravelController extends BaseController
{
    protected $travelModel;
    protected $personnelModel;
    protected $vehicleModel;
    protected $departmentModel;
    protected $notificationModel;

    public function __construct()
    {
        $this->travelModel      = new TravelModel();
        $this->personnelModel   = new PersonnelModel();
        $this->vehicleModel     = new VehicleModel();
        $this->departmentModel  = new DepartmentModel();
        $this->notificationModel = new NotificationModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $trips = $this->travelModel->getAllWithDetails();
        $today = date('Y-m-d');

        $pendingCount = $approvedCount = $todayCount = $completedCount = $cancelledCount = 0;
        foreach ($trips as $t) {
            switch ($t['status']) {
                case 'Pending':   $pendingCount++;   break;
                case 'Approved':  $approvedCount++;  break;
                case 'Completed': $completedCount++; break;
                case 'Cancelled':
                case 'Rejected':  $cancelledCount++; break;
            }
            if ($t['travel_date'] === $today) {
                $todayCount++;
            }
        }

        $fleetStats = $this->vehicleModel->getFleetStats();

        return view('travel/index', [
            'title'              => "Driver's Trip Ticket",
            'pageCss'            => 'travel.css',
            'openModule'         => 'vehicles',
            'trips'              => $trips,
            'personnel'          => $this->personnelModel->where('is_archived', 0)->findAll(),
            'drivers'            => $this->personnelModel->getDrivers(),
            'vehicles'           => $this->vehicleModel->where('is_archived', 0)->findAll(),
            'departments'        => $this->departmentModel->findAll(),
            'pending_count'      => $pendingCount,
            'approved_count'     => $approvedCount,
            'today_count'        => $todayCount,
            'completed_count'    => $completedCount,
            'cancelled_count'    => $cancelledCount,
            'available_vehicles' => $fleetStats['available'],
            'total_vehicles'     => $fleetStats['total'],
        ]);
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
        $destination = trim((string) $this->request->getPost('destination'));
        $purpose     = trim((string) $this->request->getPost('purpose'));
        $requesterId = $this->request->getPost('requester_id');
        $travelDate  = $this->request->getPost('travel_date');
        $departure   = $this->request->getPost('departure_time');
        $return      = $this->request->getPost('return_time');

        if ($destination === '' || $purpose === '' || !$requesterId || !$travelDate || !$departure || !$return) {
            return redirect()->back()->withInput()->with('error', 'Requester, destination, purpose, travel date, departure time, and return time are all required.');
        }

        $requester = $this->personnelModel->find($requesterId);
        $tripId    = $this->travelModel->generateTripId();

        $this->travelModel->insert([
            'trip_id'             => $tripId,
            'requester_id'        => $requesterId,
            'destination'         => $destination,
            'purpose'             => $purpose,
            'travel_date'         => $travelDate,
            'departure_time'      => $departure,
            'return_time'         => $return,
            'department_id'       => $requester['department_id'] ?? null,
            'assigned_driver_id'  => $this->request->getPost('assigned_driver_id') ?: null,
            'assigned_vehicle_id' => $this->request->getPost('assigned_vehicle_id') ?: null,
            'status'              => 'Pending',
            'last_activity_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->notificationModel->insert([
            'category'    => 'Trip Ticket Request',
            'description' => "Trip ticket {$tripId} requested by {$requester['full_name']} to {$destination} on " . date('M j, Y', strtotime($travelDate)) . '.',
            'recipient'   => 'Operations Office',
            'priority'    => 'MODERATE',
            'status'      => 'Pending',
            'is_read'     => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/travel')->with('success', 'Trip ticket created successfully.');
    }

    // The only thing an admin does with a trip ticket notification is approve
    // or deny it here — approving auto-resolves the notification (nothing
    // left to verify); denying leaves it Pending/unread so it still shows up
    // needing review in the Notification Center.
    private function findTripNotification(string $tripId)
    {
        return $this->notificationModel->where('category', 'Trip Ticket Request')
            ->like('description', $tripId)
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function approve($id)
    {
        $trip = $this->travelModel->find($id);
        if (!$trip) {
            return redirect()->to('/travel')->with('error', 'Trip ticket not found.');
        }

        $data = [
            'status'            => 'Approved',
            'last_activity_at'  => date('Y-m-d H:i:s'),
        ];

        if ($driverId = $this->request->getPost('assigned_driver_id')) {
            $data['assigned_driver_id'] = $driverId;
        }

        $vehicleId = $this->request->getPost('assigned_vehicle_id');
        if ($vehicleId) {
            // Prevent double-booking: refuse to assign a vehicle already out on another trip.
            $vehicle = $this->vehicleModel->find($vehicleId);
            if ($vehicle && $vehicle['availability'] === 'In Use') {
                return redirect()->to('/travel')->with('error', 'That vehicle is already in use on another trip.');
            }
            $data['assigned_vehicle_id'] = $vehicleId;
        }

        $this->travelModel->update($id, $data);

        if ($vehicleId) {
            $this->vehicleModel->update($vehicleId, ['availability' => 'In Use']);
        }

        if ($notif = $this->findTripNotification($trip['trip_id'])) {
            $this->notificationModel->update($notif['id'], [
                'status'  => 'Approved',
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Whichever driver ends up assigned — just now, or already set when
        // the ticket was created — gets notified the ticket is dispatched to
        // them, and the admin sees a pop verification confirming it was sent.
        $finalDriverId = $driverId ?: $trip['assigned_driver_id'];
        $redirect = redirect()->to('/travel')->with('success', 'Trip approved.');

        if ($finalDriverId && ($driver = $this->personnelModel->find($finalDriverId))) {
            $this->notificationModel->insert([
                'category'    => 'Trip Ticket Assignment',
                'description' => "Trip ticket {$trip['trip_id']} to {$trip['destination']} on " . date('M j, Y', strtotime($trip['travel_date'])) . " has been assigned to you. Report to the gate for dispatch clearance.",
                'recipient'   => $driver['full_name'],
                'priority'    => 'MODERATE',
                'status'      => 'Pending',
                'is_read'     => 0,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

            $redirect = $redirect->with('ticket_sent', [
                'trip_id'     => $trip['trip_id'],
                'driver'      => $driver['full_name'],
                'destination' => $trip['destination'],
            ]);
        }

        return $redirect;
    }

    public function reject($id)
    {
        $trip = $this->travelModel->find($id);
        if (!$trip) {
            return redirect()->to('/travel')->with('error', 'Trip ticket not found.');
        }

        $this->freeAssignedVehicle($id);
        $this->travelModel->update($id, [
            'status'           => 'Rejected',
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        if ($notif = $this->findTripNotification($trip['trip_id'])) {
            $this->notificationModel->update($notif['id'], [
                'description' => "Trip ticket {$trip['trip_id']} was denied and still needs review.",
            ]);
        }

        return redirect()->to('/travel')->with('success', 'Trip rejected.');
    }

    public function complete($id)
    {
        $this->freeAssignedVehicle($id);
        $this->travelModel->update($id, [
            'status'           => 'Completed',
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/travel')->with('success', 'Trip marked as completed.');
    }

    public function delete($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $this->freeAssignedVehicle($id);
        $this->travelModel->update($id, [
            'is_archived' => 1,
            'archived_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/travel')->with('success', 'Trip ticket archived.');
    }

    // Guard-side: driver departs campus with an approved trip ticket.
    public function checkIn($id)
    {
        if ($resp = $this->requireGuard()) return $resp;

        $trip = $this->travelModel->find($id);
        if (!$trip) {
            return redirect()->back()->with('error', 'Trip ticket not found.');
        }

        $this->travelModel->update($id, [
            'check_in_time'    => date('Y-m-d H:i:s'),
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Driver checked in at the gate for dispatch.');
    }

    // Guard-side: driver returns to campus — trip is now complete.
    public function checkOut($id)
    {
        if ($resp = $this->requireGuard()) return $resp;

        $trip = $this->travelModel->find($id);
        if (!$trip) {
            return redirect()->back()->with('error', 'Trip ticket not found.');
        }

        $this->freeAssignedVehicle($id);
        $this->travelModel->update($id, [
            'check_out_time'   => date('Y-m-d H:i:s'),
            'status'           => 'Completed',
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Driver checked out — trip completed.');
    }

    // Returning/cancelling a trip frees its vehicle back to Available.
    private function freeAssignedVehicle($tripId)
    {
        $trip = $this->travelModel->find($tripId);
        if ($trip && !empty($trip['assigned_vehicle_id'])) {
            $this->vehicleModel->update($trip['assigned_vehicle_id'], ['availability' => 'Available']);
        }
    }
}
