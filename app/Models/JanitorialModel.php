<?php
namespace App\Models;
use CodeIgniter\Model;

class JanitorialModel extends Model
{
    protected $table         = 'janitorial';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'team_name', 'assigned_area', 'task', 'schedule_date',
        'assigned_personnel_id', 'status', 'is_archived',
        'archived_at', 'last_activity_at'
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
}
