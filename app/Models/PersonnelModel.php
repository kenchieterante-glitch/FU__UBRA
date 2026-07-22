<?php
namespace App\Models;
use CodeIgniter\Model;

class PersonnelModel extends Model
{
    protected $table         = 'personnel';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'emp_id','user_id','full_name','email','department_id','position',
        'assigned_task','status'
    ];

    public function getByPositionKeyword($keyword)
    {
        return $this->like('position', $keyword)->findAll();
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
}
