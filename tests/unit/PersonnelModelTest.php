<?php

use App\Models\PersonnelModel;
use CodeIgniter\Test\CIUnitTestCase;

final class PersonnelModelTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $db = db_connect();
        $db->query("DROP TABLE IF EXISTS db_personnel");
        $db->query("CREATE TABLE db_personnel (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            emp_id TEXT NOT NULL UNIQUE,
            full_name TEXT,
            email TEXT,
            department_id INTEGER,
            position TEXT,
            assigned_task TEXT,
            status TEXT,
            created_at TEXT
        )");
        $db->query("INSERT INTO db_personnel (emp_id, full_name, email, department_id, position, assigned_task, status, created_at)
            VALUES ('EMP-2023-142', 'Test Person', 'test@example.com', 1, 'Staff', 'Task', 'Active', '2026-01-01 00:00:00')");
    }

    public function testDuplicateEmpIdIsDetected(): void
    {
        $model = new PersonnelModel();

        $this->assertTrue($model->isEmpIdTaken('EMP-2023-142'));
        $this->assertFalse($model->isEmpIdTaken('EMP-9999-999'));
    }
}
