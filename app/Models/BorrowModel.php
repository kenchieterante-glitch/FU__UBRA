<?php
namespace App\Models;
use CodeIgniter\Model;

class BorrowModel extends Model
{
    protected $table         = 'borrow_records';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'tool_id','borrower','department','borrowed_date','expected_return',
        'status','is_archived','archived_at','last_activity_at','created_at',
        'disposal_status','disposal_date','disposal_authorized_by','disposal_signature',
    ];

    public function getAllWithDetails()
    {
        $builder = $this->select('borrow_records.*, t.asset_name, t.asset_code')
                        ->join('tools t', 't.id = borrow_records.tool_id', 'left')
                        ->where('borrow_records.is_archived', 0)
                        ->orderBy('borrow_records.id', 'DESC');
        return $builder->findAll();
    }

    // Same as getAllWithDetails() but includes archived rows — used by the Records/Archiving page
    public function getAllWithDetailsForRecords()
    {
        $builder = $this->select('borrow_records.*, t.asset_name, t.asset_code')
                        ->join('tools t', 't.id = borrow_records.tool_id', 'left')
                        ->orderBy('borrow_records.id', 'DESC');
        return $builder->findAll();
    }

    // Auto-flag records for archiving after 1 year of inactivity
    public function autoFlagForArchiving()
    {
        $oneYearAgo = date('Y-m-d H:i:s', strtotime('-1 year'));
        $this->where('is_archived', 0)
             ->where('last_activity_at <', $oneYearAgo)
             ->set(['is_archived' => 1, 'archived_at' => date('Y-m-d H:i:s')])
             ->update();
    }
}
