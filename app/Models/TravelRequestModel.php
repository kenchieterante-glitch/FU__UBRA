<?php
namespace App\Models;
use CodeIgniter\Model;

class TravelRequestModel extends Model
{
    protected $table         = 'travel_requests';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'trip_id', 'requester_id', 'destination', 'purpose', 'travel_date',
        'departure_time', 'return_time', 'department_id', 'assigned_driver_id',
        'assigned_vehicle_id', 'status', 'check_in_time', 'check_out_time', 'scanned_id',
    ];

    public function getNextForDriver(int $driverId)
    {
        return $this->where('assigned_driver_id', $driverId)
                    ->where('status', 'Approved')
                    ->where('check_out_time IS NULL')
                    ->orderBy('travel_date', 'ASC')
                    ->orderBy('departure_time', 'ASC')
                    ->first();
    }

    public function getTodayWithDetails()
    {
        return $this->select('travel_requests.*, v.vehicle_name, v.plate_no, p.full_name as driver_name')
                    ->join('vehicles v', 'v.id = travel_requests.assigned_vehicle_id', 'left')
                    ->join('personnel p', 'p.id = travel_requests.assigned_driver_id', 'left')
                    ->where('travel_requests.travel_date', date('Y-m-d'))
                    ->orderBy('travel_requests.departure_time', 'ASC')
                    ->findAll();
    }
}
