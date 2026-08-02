<?php
namespace App\Models;
use CodeIgniter\Model;

class JanitorialAssignmentModel extends Model
{
    protected $table         = 'janitorial_assignments';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'staff_name', 'assigned_zone', 'shift_start', 'shift_end',
        'date_assigned', 'status', 'priority',
    ];

    public function getTodayAssignments()
    {
        return $this->where('date_assigned', date('Y-m-d'))->findAll();
    }
}
