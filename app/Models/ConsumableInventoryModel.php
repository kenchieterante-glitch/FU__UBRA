<?php
namespace App\Models;
use CodeIgniter\Model;

class ConsumableInventoryModel extends Model
{
    protected $table         = 'consumable_inventory';
    use \App\Models\Concerns\HidesArchived;
    protected string $archiveType = 'supply';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'item_name', 'category', 'department', 'unit', 'building', 'floor', 'location_note', 'current_stock', 'reorder_threshold', 'last_refill',
    ];
}
