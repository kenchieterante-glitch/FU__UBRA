<?php

namespace App\Models;

use App\Libraries\MonitoringStatus;
use CodeIgniter\Model;

class PersonnelContractModel extends Model
{
    protected $table         = 'personnel_contracts';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'personnel_id', 'job_order_id', 'contract_number',
        'contract_start_date', 'contract_end_date', 'contract_status',
        'renewal_status', 'remarks',
    ];

    private const TIME_DRIVEN_STATUSES = ['ACTIVE', 'EXPIRING_SOON', 'EXPIRED'];

    public function getAllWithDetails(): array
    {
        $db = $this->db;
        $rows = $db->table('personnel_contracts pc')
            ->select('pc.*, p.full_name, p.emp_id, jo.job_order_number')
            ->join('personnel p', 'p.id = pc.personnel_id', 'left')
            ->join('job_orders jo', 'jo.id = pc.job_order_id', 'left')
            ->orderBy('pc.id', 'DESC')
            ->get()->getResultArray();

        return array_map([$this, 'withDerivedStatus'], $rows);
    }

    public function getForPersonnel(int $personnelId): array
    {
        $db = $this->db;
        $rows = $db->table('personnel_contracts pc')
            ->select('pc.*, jo.job_order_number')
            ->join('job_orders jo', 'jo.id = pc.job_order_id', 'left')
            ->where('pc.personnel_id', $personnelId)
            ->orderBy('pc.id', 'DESC')
            ->get()->getResultArray();

        return array_map([$this, 'withDerivedStatus'], $rows);
    }

    public function withDerivedStatus(array $row): array
    {
        $row['contract_status'] = MonitoringStatus::derive($row['contract_end_date'] ?? null, $row['contract_status'], self::TIME_DRIVEN_STATUSES);
        $row['days_remaining'] = MonitoringStatus::daysRemaining($row['contract_end_date'] ?? null);
        return $row;
    }

    public function countByDerivedStatus(): array
    {
        $rows = $this->getAllWithDetails();
        $counts = ['ACTIVE' => 0, 'EXPIRING_SOON' => 0, 'EXPIRED' => 0, 'RENEWED' => 0, 'TERMINATED' => 0];
        foreach ($rows as $row) {
            $status = $row['contract_status'];
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }
        return $counts;
    }
}
