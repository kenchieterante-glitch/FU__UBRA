<?php

namespace App\Models;

use CodeIgniter\Model;

class TravelModel extends Model
{
    protected $table         = 'travel_requests';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'trip_id', 'requester_id', 'destination', 'purpose', 'travel_date',
        'departure_time', 'return_time', 'department_id',
        'assigned_driver_id', 'assigned_vehicle_id', 'status',
        'check_in_time', 'check_out_time', 'last_activity_at',
    ];

    public function generateTripId(): string
    {
        $date = date('Ymd');
        $count = $this->like('trip_id', "TR-{$date}-", 'after')->countAllResults(false);
        return sprintf('TR-%s-%04d', $date, $count + 1);
    }

    public function getAllWithDetails()
    {
        return $this->select('travel_requests.*, v.vehicle_name, v.plate_no, p.full_name as driver_name, d.name as department_name')
            ->join('vehicles v', 'v.id = travel_requests.assigned_vehicle_id', 'left')
            ->join('personnel p', 'p.id = travel_requests.assigned_driver_id', 'left')
            ->join('departments d', 'd.id = travel_requests.department_id', 'left')
            ->where('travel_requests.is_archived', 0)
            ->orderBy('travel_requests.id', 'DESC')
            ->findAll();
    }

    public function getForRequester(int $requesterId)
    {
        return $this->select('travel_requests.*, v.vehicle_name, v.plate_no, p.full_name as driver_name')
            ->join('vehicles v', 'v.id = travel_requests.assigned_vehicle_id', 'left')
            ->join('personnel p', 'p.id = travel_requests.assigned_driver_id', 'left')
            ->where('travel_requests.requester_id', $requesterId)
            ->where('travel_requests.is_archived', 0)
            ->orderBy('travel_requests.id', 'DESC')
            ->findAll();
    }
}
