<?php
namespace App\Models;
use CodeIgniter\Model;

class FacilityChecklistItemModel extends Model
{
    protected $table         = 'facility_checklist_items';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['checklist_id', 'section', 'item_code', 'item_label', 'rating', 'corrective_action'];
}
