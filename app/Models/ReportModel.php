<?php
namespace App\Models;
use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $table         = 'reports';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'report_name', 'generated_by_id', 'type_module', 'status',
        'file_path', 'is_archived', 'archived_at', 'last_activity_at'
    ];

    public function getAllWithDetails()
    {
        $builder = $this->select('reports.*, p.full_name as generated_by_name')
                        ->join('personnel p', 'p.id = reports.generated_by_id', 'left')
                        ->where('reports.is_archived', 0)
                        ->orderBy('reports.id', 'DESC');
        return $builder->findAll();
    }

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
