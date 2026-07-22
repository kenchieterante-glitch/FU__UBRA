<?php
namespace App\Models;
use CodeIgniter\Model;

class GpsLogModel extends Model
{
    protected $table         = 'gps_logs';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'vehicle_id', 'device_id', 'latitude', 'longitude',
        'signal_strength', 'status', 'created_at'
    ];

    public function getLatestForVehicle($vehicleId)
    {
        return $this->where('vehicle_id', $vehicleId)
                    ->orderBy('created_at', 'DESC')
                    ->first();
    }
}
