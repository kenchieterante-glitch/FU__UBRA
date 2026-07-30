<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    // The real users table's primary key is department_id — there is no 'id'
    // column — and it has no updated_at column either (only created_at, set by
    // the DB's own default). password (not password_hash) is the real column.
    protected $table         = 'users';
    protected $primaryKey    = 'department_id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'emp_id', 'username', 'email', 'password', 'full_name',
        'role', 'department_id', 'department', 'photo',
    ];

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
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
