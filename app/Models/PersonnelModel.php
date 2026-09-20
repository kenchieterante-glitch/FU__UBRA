<?php
namespace App\Models;
use CodeIgniter\Model;

class PersonnelModel extends Model
{
    protected $table         = 'personnel';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'emp_id','user_id','full_name','email','contact_number','department_id','employment_type',
        'position','assigned_task','status','is_archived','archived_at','created_at'
    ];

    // Active personnel whose position matches one or more keywords (e.g.
    // 'Driver', or ['Janitor','Cleaning'] to treat both as "janitorial") —
    // shared by anywhere that needs to pick a real person by role: the
    // Notification Center's compose Recipient list, and Mr. UBRA's
    // "Notify Driver" / "Notify Cleaning Personnel" suggested actions.
    public function getActiveByPositionLike($positionLike): array
    {
        $keywords = (array) $positionLike;
        $query = $this->where('is_archived', 0)->groupStart();
        foreach ($keywords as $i => $kw) {
            $i === 0 ? $query->like('position', $kw) : $query->orLike('position', $kw);
        }
        $rows = $query->groupEnd()->orderBy('full_name', 'ASC')->findAll();

        return array_map(fn($p) => [
            'name'          => $p['full_name'],
            'position'      => $p['position'],
            'contactNumber' => $p['contact_number'] ?? '',
        ], $rows);
    }

    public function getWithDetails(int $id)
    {
        return $this->select('personnel.*, departments.name as department_name')
                    ->join('departments', 'departments.id = personnel.department_id', 'left')
                    ->where('personnel.id', $id)
                    ->first();
    }

    public function getJobOrderPersonnel()
    {
        return $this->select('personnel.*, departments.name as department_name')
                    ->join('departments', 'departments.id = personnel.department_id', 'left')
                    ->where('personnel.is_archived', 0)
                    ->where('personnel.employment_type', 'JobOrder')
                    ->orderBy('personnel.id', 'DESC')
                    ->findAll();
    }

    public function getByPositionKeyword($keyword)
    {
        return $this->like('position', $keyword)->where('is_archived', 0)->findAll();
    }

    public function getByEmpId(?string $empId)
    {
        if ($empId === null || trim($empId) === '') {
            return null;
        }
        return $this->select('personnel.*, departments.name as department_name')
                    ->join('departments', 'departments.id = personnel.department_id', 'left')
                    ->where('personnel.emp_id', trim($empId))
                    ->first();
    }

    public function getAllWithDetails()
    {
        $builder = $this->select('personnel.*, departments.name as department_name')
                        ->join('departments', 'departments.id = personnel.department_id', 'left')
                        ->where('personnel.is_archived', 0)
                        ->orderBy('personnel.id', 'DESC');
        return $builder->findAll();
    }

    // Same as getAllWithDetails() but includes archived rows — used by Records, Archiving & Reports.
    public function getAllWithDetailsForRecords()
    {
        $builder = $this->select('personnel.*, departments.name as department_name')
                        ->join('departments', 'departments.id = personnel.department_id', 'left')
                        ->orderBy('personnel.id', 'DESC');
        return $builder->findAll();
    }

    public function getDrivers()
    {
        return $this->like('position', 'Driver')->where('is_archived', 0)->findAll();
    }

    public function isEmpIdTaken($empId, $excludeId = null)
    {
        $builder = $this->where('emp_id', $empId);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }
}
