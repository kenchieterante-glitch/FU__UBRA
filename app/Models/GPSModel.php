<?php

namespace App\Models;

use CodeIgniter\Model;

class GPSModel extends Model
{
    protected $table         = 'gps_logs';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'vehicle_id', 'device_id', 'latitude', 'longitude',
        'signal_strength', 'status', 'logged_at',
    ];

    public function getLatestPerVehicle(): array
    {
        try {
            return $this->db->query("
                SELECT g.*
                FROM gps_logs g
                INNER JOIN (
                    SELECT vehicle_id, MAX(id) AS max_id
                    FROM gps_logs
                    GROUP BY vehicle_id
                ) latest ON g.vehicle_id = latest.vehicle_id
                         AND g.id = latest.max_id
            ")->getResultArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getHistory(int $vehicleId, int $limit = 50): array
    {
        try {
            return $this->where('vehicle_id', $vehicleId)
                        ->orderBy('id', 'DESC')
                        ->limit($limit)
                        ->findAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * All GPS pings for one vehicle within a date/time range, oldest first —
     * for plotting a route on the map (GPSController::getRoute), unlike
     * getHistory() above which is newest-first and capped for the popup's
     * small "Recent Pings" table.
     */
    public function getHistoryInRange(int $vehicleId, string $from, string $to, int $limit = 500): array
    {
        try {
            return $this->where('vehicle_id', $vehicleId)
                        ->where('logged_at >=', $from)
                        ->where('logged_at <=', $to)
                        ->orderBy('logged_at', 'ASC')
                        ->limit($limit)
                        ->findAll();
        } catch (\Exception $e) {
            return [];
        }
    }
}
