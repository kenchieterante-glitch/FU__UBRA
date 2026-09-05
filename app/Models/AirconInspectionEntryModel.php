<?php
namespace App\Models;
use CodeIgniter\Model;

class AirconInspectionEntryModel extends Model
{
    protected $table         = 'aircon_inspection_entries';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['log_id', 'entry_date', 'department', 'qty', 'room_no', 'aircon_type', 'work_done', 'remarks'];
}
