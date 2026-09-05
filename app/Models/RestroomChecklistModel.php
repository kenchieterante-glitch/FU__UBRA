<?php
namespace App\Models;
use CodeIgniter\Model;

class RestroomChecklistModel extends Model
{
    protected $table         = 'restroom_checklists';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['location', 'reviewed_by', 'reviewed_date', 'is_archived'];

    public function getAllWithEntries(): array
    {
        $checklists = $this->where('is_archived', 0)->orderBy('id', 'DESC')->findAll();
        $entryModel = new RestroomChecklistEntryModel();
        foreach ($checklists as &$c) {
            $c['entries'] = $entryModel->where('checklist_id', $c['id'])->orderBy('entry_date', 'DESC')->findAll();
        }
        return $checklists;
    }
}
