<?php
namespace App\Models;
use CodeIgniter\Model;

class EquipmentMaintenanceLogModel extends Model
{
    protected $table         = 'equipment_maintenance_logs';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['department', 'date_submitted', 'reviewed_by', 'reviewed_date', 'approved_by', 'is_archived'];

    public function getAllWithEntries(): array
    {
        $logs = $this->where('is_archived', 0)->orderBy('id', 'DESC')->findAll();
        $entryModel = new EquipmentMaintenanceEntryModel();
        foreach ($logs as &$l) {
            $l['entries'] = $entryModel->where('log_id', $l['id'])->orderBy('entry_date', 'DESC')->findAll();
        }
        return $logs;
    }
}
