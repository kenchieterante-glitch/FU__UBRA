<?php
namespace App\Models;
use CodeIgniter\Model;

class ConsumableInventoryModel extends Model
{
    protected $table         = 'consumable_inventory';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'item_name', 'category', 'unit', 'current_stock', 'reorder_threshold', 'last_refill',
    ];
}
