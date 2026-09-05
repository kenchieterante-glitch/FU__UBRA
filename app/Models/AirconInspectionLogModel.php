<?php
namespace App\Models;
use CodeIgniter\Model;

class AirconInspectionLogModel extends Model
{
    protected $table         = 'aircon_inspection_logs';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['performed_by', 'date_submitted', 'reviewed_by', 'reviewed_date', 'approved_by', 'is_archived'];

    public function getAllWithEntries(): array
    {
        $logs = $this->where('is_archived', 0)->orderBy('id', 'DESC')->findAll();
        $entryModel = new AirconInspectionEntryModel();
        foreach ($logs as &$l) {
            $l['entries'] = $entryModel->where('log_id', $l['id'])->orderBy('entry_date', 'DESC')->findAll();
        }
        return $logs;
    }
}
