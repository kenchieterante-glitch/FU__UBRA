<?php
namespace App\Models;
use CodeIgniter\Model;

class EquipmentMaintenanceEntryModel extends Model
{
    protected $table         = 'equipment_maintenance_entries';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'log_id', 'entry_date', 'asset_name', 'serial_number', 'maintenance_frequency',
        'work_description', 'status', 'next_due_date', 'performed_by', 'signature',
    ];
}
