<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePersonnelAssignmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'personnel_id' => ['type' => 'INT', 'constraint' => 11],
            'job_order_id' => ['type' => 'INT', 'constraint' => 11],
            'position' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'assignment_location' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'department_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'supervisor' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'assignment_start_date' => ['type' => 'DATE', 'null' => true],
            'assignment_end_date' => ['type' => 'DATE', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'ACTIVE'],
            'remarks' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('personnel_id');
        $this->forge->addKey('job_order_id');
        $this->forge->createTable('personnel_assignments');
    }

    public function down()
    {
        $this->forge->dropTable('personnel_assignments');
    }
}
