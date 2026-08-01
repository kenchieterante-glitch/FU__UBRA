<?php
namespace App\Models;
use CodeIgniter\Model;

class PersonnelModel extends Model
{
    protected $table         = 'personnel';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'emp_id','user_id','full_name','email','department_id','position',
        'assigned_task','status','created_at'
    ];

    public function getByPositionKeyword($keyword)
    {
        return $this->like('position', $keyword)->findAll();
    }

    public function getByEmpId($empId)
    {
        return $this->select('personnel.*, departments.name as department_name')
                    ->join('departments', 'departments.id = personnel.department_id', 'left')
                    ->where('personnel.emp_id', $empId)
                    ->first();
    }

    public function getAllWithDetails()
    {
        $builder = $this->select('personnel.*, departments.name as department_name')
                        ->join('departments', 'departments.id = personnel.department_id', 'left')
                        ->orderBy('personnel.id', 'DESC');
        return $builder->findAll();
    }

    public function getDrivers()
    {
        return $this->like('position', 'Driver')->findAll();
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
