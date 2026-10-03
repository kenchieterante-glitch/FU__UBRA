<?php

namespace App\Models;

use CodeIgniter\Model;

class MotorpoolWorkOrderModel extends Model
{
    protected $table         = 'motorpool_work_orders';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['wo_number', 'wo_type', 'vehicle_id', 'equipment_id', 'issue', 'priority', 'status', 'requested_by', 'assigned_to', 'created_at', 'updated_at', 'completed_at'];

    public const TYPES    = ['Vehicle Repair', 'Mechanical Equipment'];
    public const STATUSES = ['Pending', 'In Progress', 'Completed', 'Cancelled'];
}
