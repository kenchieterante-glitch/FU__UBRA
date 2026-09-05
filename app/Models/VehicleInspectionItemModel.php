<?php
namespace App\Models;
use CodeIgniter\Model;

class VehicleInspectionItemModel extends Model
{
    protected $table         = 'vehicle_inspection_items';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['checklist_id', 'section', 'item_label', 'response', 'remarks'];
}
