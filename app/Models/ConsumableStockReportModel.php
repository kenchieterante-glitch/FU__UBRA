<?php
namespace App\Models;
use CodeIgniter\Model;

class ConsumableStockReportModel extends Model
{
    protected $table         = 'consumable_stock_reports';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'inventory_item_id', 'item_name', 'zone', 'quantity_left', 'unit', 'reported_by', 'reported_at',
    ];

    public function getRecent(int $limit = 50): array
    {
        return $this->orderBy('reported_at', 'DESC')->findAll($limit);
    }
}
