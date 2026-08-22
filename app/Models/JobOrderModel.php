<?php

namespace App\Models;

use App\Libraries\MonitoringStatus;
use CodeIgniter\Model;

class JobOrderModel extends Model
{
    protected $table         = 'job_orders';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'job_order_number', 'job_order_title', 'project_name', 'position',
        'department_id', 'assignment_location', 'personnel_required', 'supervisor',
        'start_date', 'end_date', 'status', 'description', 'remarks', 'is_archived',
    ];

    // Statuses this field auto-recomputes from end_date on every read; every
    // other status (DRAFT, PENDING, COMPLETED, CANCELLED, FOR_RENEWAL) is a
    // deliberate business decision and is left untouched.
    private const TIME_DRIVEN_STATUSES = ['ACTIVE', 'EXPIRING_SOON', 'EXPIRED'];

    public function getAllWithDetails(): array
    {
        $db = $this->db;
        $rows = $db->table('job_orders jo')
            ->select("jo.*,
                (SELECT COUNT(*) FROM personnel_assignments pa WHERE pa.job_order_id = jo.id AND pa.status = 'ACTIVE') as assigned_count")
            ->where('jo.is_archived', 0)
            ->orderBy('jo.id', 'DESC')
            ->get()->getResultArray();

        return array_map([$this, 'withDerivedStatus'], $rows);
    }

    public function getWithDetails(int $id): ?array
    {
        $db = $this->db;
        $row = $db->table('job_orders jo')
            ->select("jo.*,
                (SELECT COUNT(*) FROM personnel_assignments pa WHERE pa.job_order_id = jo.id AND pa.status = 'ACTIVE') as assigned_count")
            ->where('jo.id', $id)
            ->get()->getRowArray();

        return $row ? $this->withDerivedStatus($row) : null;
    }

    public function getForDropdown()
    {
        return $this->where('is_archived', 0)->orderBy('job_order_number', 'ASC')->findAll();
    }

    public function withDerivedStatus(array $row): array
    {
        $row['status'] = MonitoringStatus::derive($row['end_date'] ?? null, $row['status'], self::TIME_DRIVEN_STATUSES);
        $row['days_remaining'] = MonitoringStatus::daysRemaining($row['end_date'] ?? null);
        return $row;
    }

    public function isNumberTaken(string $number, ?int $excludeId = null): bool
    {
        $builder = $this->where('job_order_number', $number);
        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    public function nextJobOrderNumber(): string
    {
        $year = date('Y');
        $count = $this->where('job_order_number LIKE', "JO-{$year}-%")->countAllResults() + 1;
        return sprintf('JO-%s-%03d', $year, $count);
    }
}
