<?php
namespace App\Models;
use CodeIgniter\Model;

class ToolsModel extends Model
{
    protected $table         = 'tools';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'asset_name','asset_code','category','location','custodian_id',
        'condition_status','availability','is_archived','archived_at','last_activity_at'
    ];

    public function getAvailableTools()
    {
        return $this->where('availability', 'Available')->where('is_archived', 0)->findAll();
    }

    public function getAllWithDetails()
    {
        $builder = $this->select('tools.*, p.full_name as custodian_name')
                        ->join('personnel p', 'p.id = tools.custodian_id', 'left')
                        ->where('tools.is_archived', 0)
                        ->orderBy('tools.id', 'DESC');
        return $builder->findAll();
    }

    public function getCategoryDistribution(): array
    {
        try {
            return $this->db->query("
                SELECT category, COUNT(*) AS count
                FROM tools
                WHERE is_archived = 0
                GROUP BY category
                ORDER BY count DESC
            ")->getResultArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
