<?php
namespace App\Models;
use CodeIgniter\Model;

class SafetyWorkOrderModel extends Model
{
    protected $table         = 'safety_work_orders';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'wo_number', 'issue', 'location', 'reported_by', 'assigned_to',
        'priority', 'stage', 'date_logged', 'date_closed', 'notes',
    ];
}
