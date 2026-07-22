<?php
namespace App\Models;
use CodeIgniter\Model;

class DisposalLogModel extends Model
{
    protected $table         = 'disposal_logs';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'record_type', 'record_id', 'authorized_by_id',
        'signature', 'disposal_date', 'remarks'
    ];
}
