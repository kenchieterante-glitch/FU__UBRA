<?php

namespace App\Controllers\Api;

use App\Libraries\ApiAuth;
use App\Models\PersonnelModel;
use App\Models\TravelModel;

class TravelController extends BaseApiController
{
    protected TravelModel $travelModel;
    protected PersonnelModel $personnelModel;

    public function __construct()
    {
        $this->travelModel   = new TravelModel();
        $this->personnelModel = new PersonnelModel();
    }

    // GET /api/travel — the logged-in user's own travel requests
    public function index()
    {
        $personnel = $this->personnelModel->getByEmpId(ApiAuth::user()['emp_id'] ?? null);

        if (!$personnel) {
            return $this->ok([
                'requests' => [],
                'linked'   => false,
                'message'  => 'Your account is not linked to a personnel record, so travel requests cannot be tracked yet. Contact an administrator.',
            ]);
        }

        return $this->ok([
            'requests' => $this->travelModel->getForRequester((int) $personnel['id']),
            'linked'   => true,
        ]);
    }

    // POST /api/travel/add
    public function add()
    {
        $personnel = $this->personnelModel->getByEmpId(ApiAuth::user()['emp_id'] ?? null);

        if (!$personnel) {
            return $this->fail('Your account is not linked to a personnel record. Contact an administrator before submitting a travel request.', 422);
        }

        $destination = trim((string) $this->request->getPost('destination'));
        $purpose     = trim((string) $this->request->getPost('purpose'));
        $travelDate  = $this->request->getPost('travel_date');
        $departure   = $this->request->getPost('departure_time');
        $return      = $this->request->getPost('return_time');

        if ($destination === '' || $purpose === '' || !$travelDate || !$departure || !$return) {
            return $this->fail('Destination, purpose, travel date, departure time, and return time are all required.', 422);
        }

        $id = $this->travelModel->insert([
            'trip_id'         => $this->travelModel->generateTripId(),
            'requester_id'    => $personnel['id'],
            'destination'     => $destination,
            'purpose'         => $purpose,
            'travel_date'     => $travelDate,
            'departure_time'  => $departure,
            'return_time'     => $return,
            'department_id'   => $personnel['department_id'] ?? null,
            'status'          => 'Pending',
            'last_activity_at'=> date('Y-m-d H:i:s'),
        ], true);

        return $this->ok(['id' => $id], 201);
    }

    // GET /api/travel/today — all non-archived trip tickets for today (guard dashboard)
    public function today()
    {
        $rows = $this->travelModel->getAllWithDetails();
        $today = date('Y-m-d');
        $todays = array_values(array_filter($rows, fn ($r) => $r['travel_date'] === $today));

        return $this->ok(['trips' => $todays]);
    }

    // POST /api/travel/(:num)/scanout — driver departs campus
    public function scanOut($id = null)
    {
        $trip = $this->travelModel->find($id);
        if (!$trip) {
            return $this->fail('Trip ticket not found.', 404);
        }

        $this->travelModel->update($id, [
            'check_out_time'   => date('Y-m-d H:i:s'),
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->ok();
    }

    // POST /api/travel/(:num)/scanin — driver returns to campus
    public function scanIn($id = null)
    {
        $trip = $this->travelModel->find($id);
        if (!$trip) {
            return $this->fail('Trip ticket not found.', 404);
        }

        $this->travelModel->update($id, [
            'check_in_time'    => date('Y-m-d H:i:s'),
            'status'           => 'Completed',
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->ok();
    }
}
