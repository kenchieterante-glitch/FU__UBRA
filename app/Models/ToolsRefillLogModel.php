<?php
namespace App\Models;
use CodeIgniter\Model;

class ToolsRefillLogModel extends Model
{
    protected $table         = 'tools_refill_log';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'tool_id', 'asset_name', 'quantity_added', 'performed_by', 'performed_at',
    ];

    public function getRecent(int $limit = 50): array
    {
        return $this->orderBy('performed_at', 'DESC')->findAll($limit);
    }
}
