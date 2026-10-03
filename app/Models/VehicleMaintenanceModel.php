<?php

namespace App\Models;

use CodeIgniter\Model;

class VehicleMaintenanceModel extends Model
{
    protected $table         = 'vehicle_maintenance';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['vehicle_id', 'service_type', 'serviced_on', 'odometer_km', 'cost', 'performed_by', 'next_due', 'notes', 'created_at'];
}
