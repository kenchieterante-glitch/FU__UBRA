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

    public function getAllWithDetails()
    {
        $builder = $this->select('disposal_logs.*, p.full_name as authorized_by_name')
                        ->join('personnel p', 'p.id = disposal_logs.authorized_by_id', 'left')
                        ->orderBy('disposal_logs.id', 'DESC');
        return $builder->findAll();
    }
}
