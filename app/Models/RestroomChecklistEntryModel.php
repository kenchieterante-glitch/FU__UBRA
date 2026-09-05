<?php
namespace App\Models;
use CodeIgniter\Model;

class RestroomChecklistEntryModel extends Model
{
    protected $table         = 'restroom_checklist_entries';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'checklist_id', 'entry_date', 'entry_time', 'empty_trash', 'refill_paper',
        'refill_soap', 'clean_floor', 'clean_sink', 'clean_toilet', 'cleaned_by', 'signature',
    ];
}
