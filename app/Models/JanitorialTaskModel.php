<?php
namespace App\Models;
use CodeIgniter\Model;

class JanitorialTaskModel extends Model
{
    protected $table         = 'janitorial_tasks';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'assignment_id', 'task_name', 'is_done', 'completed_at',
    ];

    public function getForAssignment(int $assignmentId)
    {
        return $this->where('assignment_id', $assignmentId)->findAll();
    }
}
