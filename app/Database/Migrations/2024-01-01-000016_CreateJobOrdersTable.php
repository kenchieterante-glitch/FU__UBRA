<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJobOrdersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'job_order_number' => ['type' => 'VARCHAR', 'constraint' => 40],
            'job_order_title' => ['type' => 'VARCHAR', 'constraint' => 150],
            'project_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'position' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'department_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'assignment_location' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'personnel_required' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'supervisor' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'start_date' => ['type' => 'DATE', 'null' => true],
            'end_date' => ['type' => 'DATE', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'ACTIVE'],
            'description' => ['type' => 'TEXT', 'null' => true],
            'remarks' => ['type' => 'TEXT', 'null' => true],
            'is_archived' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('job_order_number');
        $this->forge->addKey('department_id');
        $this->forge->createTable('job_orders');
    }

    public function down()
    {
        $this->forge->dropTable('job_orders');
    }
}
