<?php
namespace App\Models;
use CodeIgniter\Model;

class BorrowModel extends Model
{
    protected $table         = 'borrow_records';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tool_id','borrower_id','borrowed_date','expected_return','actual_return',
        'status','condition_on_borrow','condition_on_return','remarks',
        'is_archived','archived_at','last_activity_at'
    ];

    public function getAllWithDetails()
    {
        $builder = $this->select('borrow_records.*, t.asset_name, t.asset_code, borrower.full_name as borrower_name')
                        ->join('tools t', 't.id = borrow_records.tool_id', 'left')
                        ->join('personnel borrower', 'borrower.id = borrow_records.borrower_id', 'left')
                        ->where('borrow_records.is_archived', 0)
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
