<?php
namespace App\Models;
use CodeIgniter\Model;

class RefillLogModel extends Model
{
    protected $table         = 'refill_log';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'inventory_item_id', 'item_name', 'quantity_added', 'unit', 'performed_by', 'performed_at',
    ];

    public function getRecent(int $limit = 50): array
    {
        return $this->orderBy('performed_at', 'DESC')->findAll($limit);
    }
}
