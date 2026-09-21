<?php
namespace App\Models;
use CodeIgniter\Model;

class JanitorialTaskHistoryModel extends Model
{
    protected $table         = 'janitorial_task_history';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'assignment_id', 'zone', 'task_name', 'status', 'completed_at', 'performed_by', 'archived_at',
    ];
}
