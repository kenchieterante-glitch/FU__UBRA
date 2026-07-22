<?php

namespace App\Models;

use CodeIgniter\Model;

class TravelModel extends Model
{
    protected $table         = 'travel_requests';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'trip_id', 'requester_id', 'destination', 'purpose', 'travel_date',
        'departure_time', 'return_time', 'department_id', 'assigned_driver_id',
        'assigned_vehicle_id', 'status', 'check_in_time', 'check_out_time',
        'scanned_id', 'is_archived', 'archived_at', 'last_activity_at'
    ];

    // Auto-flag records for archiving after 1 year of inactivity
    public function autoFlagForArchiving()
    {
        $oneYearAgo = date('Y-m-d H:i:s', strtotime('-1 year'));
        $this->where('is_archived', 0)
             ->where('last_activity_at <', $oneYearAgo)
             ->set(['is_archived' => 1, 'archived_at' => date('Y-m-d H:i:s')])
             ->update();
    }

    public function getAllWithDetails(): array
    {
        try {
            $builder = $this->select('travel_requests.*, 
                                      requester.full_name as requester_name, 
                                      driver.full_name as driver_name, 
                                      v.vehicle_name, v.plate_no, 
                                      d.name as department_name')
                            ->join('personnel requester', 'requester.id = travel_requests.requester_id', 'left')
                            ->join('personnel driver', 'driver.id = travel_requests.assigned_driver_id', 'left')
                            ->join('vehicles v', 'v.id = travel_requests.assigned_vehicle_id', 'left')
                            ->join('departments d', 'd.id = travel_requests.department_id', 'left')
                            ->where('travel_requests.is_archived', 0)
                            ->orderBy('travel_requests.id', 'DESC');
            return $builder->findAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getTripWithDetails($id): ?array
    {
        try {
            $builder = $this->select('travel_requests.*, 
                                      requester.full_name as requester_name, 
                                      driver.full_name as driver_name, 
                                      v.vehicle_name, v.plate_no, v.type as vehicle_type,
                                      d.name as department_name')
                            ->join('personnel requester', 'requester.id = travel_requests.requester_id', 'left')
                            ->join('personnel driver', 'driver.id = travel_requests.assigned_driver_id', 'left')
                            ->join('vehicles v', 'v.id = travel_requests.assigned_vehicle_id', 'left')
                            ->join('departments d', 'd.id = travel_requests.department_id', 'left');
            return $builder->find($id);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getMonthlyVolume(int $months = 7): array
    {
        try {
            return $this->db->query("
                SELECT DATE_FORMAT(travel_date, '%b')    AS month,
                       DATE_FORMAT(travel_date, '%Y-%m') AS sort_key,
                       COUNT(*) AS count
                FROM travel_requests
                WHERE is_archived = 0 AND travel_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                GROUP BY sort_key, month
                ORDER BY sort_key ASC
            ", [$months])->getResultArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function generateTripId()
    {
        $today = date('Ymd');
        $last = $this->like('trip_id', "TR-$today-")->orderBy('id', 'DESC')->first();
        
        if ($last) {
            $parts = explode('-', $last['trip_id']);
            $seq = intval(end($parts)) + 1;
        } else {
            $seq = 1;
        }
        
        return "TR-$today-" . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
