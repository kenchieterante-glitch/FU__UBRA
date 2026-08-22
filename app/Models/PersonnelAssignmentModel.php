<?php

namespace App\Models;

use CodeIgniter\Model;

class PersonnelAssignmentModel extends Model
{
    protected $table         = 'personnel_assignments';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'personnel_id', 'job_order_id', 'position', 'assignment_location',
        'department_id', 'supervisor', 'assignment_start_date', 'assignment_end_date',
        'status', 'remarks',
    ];

    public function getForJobOrder(int $jobOrderId): array
    {
        $db = $this->db;
        return $db->table('personnel_assignments pa')
            ->select('pa.*, p.full_name, p.emp_id')
            ->join('personnel p', 'p.id = pa.personnel_id', 'left')
            ->where('pa.job_order_id', $jobOrderId)
            ->orderBy('pa.status', 'ASC')
            ->orderBy('pa.id', 'DESC')
            ->get()->getResultArray();
    }

    public function getActiveForPersonnel(int $personnelId): ?array
    {
        $db = $this->db;
        return $db->table('personnel_assignments pa')
            ->select('pa.*, jo.job_order_number, jo.job_order_title')
            ->join('job_orders jo', 'jo.id = pa.job_order_id', 'left')
            ->where('pa.personnel_id', $personnelId)
            ->where('pa.status', 'ACTIVE')
            ->orderBy('pa.id', 'DESC')
            ->get()->getRowArray();
    }

    public function getHistoryForPersonnel(int $personnelId): array
    {
        $db = $this->db;
        return $db->table('personnel_assignments pa')
            ->select('pa.*, jo.job_order_number, jo.job_order_title')
            ->join('job_orders jo', 'jo.id = pa.job_order_id', 'left')
            ->where('pa.personnel_id', $personnelId)
            ->orderBy('pa.id', 'DESC')
            ->get()->getResultArray();
    }

    public function hasActiveAssignment(int $personnelId): bool
    {
        return $this->where('personnel_id', $personnelId)->where('status', 'ACTIVE')->countAllResults() > 0;
    }
}
