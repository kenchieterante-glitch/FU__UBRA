<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'emp_id', 'username', 'email', 'password_hash', 'full_name',
        'role', 'department_id', 'status',
    ];

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
            unset($data['data']['password']);
        }
        return $data;
    }

    public function getByUsername($username)
    {
        return $this->where('username', $username)->first();
    }

    public function getByEmployeeId($empId)
    {
        $empId = trim((string) $empId);

        if ($empId === '') {
            return null;
        }

        $columns = ['emp_id', 'employee_id', 'username'];
        $fieldData = $this->db->getFieldData($this->table) ?: [];
        $availableColumns = [];

        foreach ($fieldData as $field) {
            $availableColumns[] = is_object($field) ? $field->name : $field['name'];
        }

        foreach ($columns as $column) {
            if (in_array($column, $availableColumns, true)) {
                $user = $this->where($column, $empId)->first();
                if ($user) {
                    return $user;
                }
            }
        }

        return null;
    }
}
