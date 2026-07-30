<?php
namespace App\Models;
use CodeIgniter\Model;

class KeyBorrowLogModel extends Model
{
    protected $table         = 'key_borrow_logs';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'log_number', 'borrower_id', 'trip_ticket_id', 'full_name', 'department',
        'key_item', 'scan_in', 'scan_out', 'status', 'guard_on_duty',
    ];

    public function getAllWithTrip()
    {
        return $this->select('key_borrow_logs.*, t.trip_id, t.status as trip_status')
                    ->join('travel_requests t', 't.id = key_borrow_logs.trip_ticket_id', 'left')
                    ->orderBy('key_borrow_logs.id', 'DESC')
                    ->findAll();
    }
}
