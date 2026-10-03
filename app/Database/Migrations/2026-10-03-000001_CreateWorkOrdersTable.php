<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWorkOrdersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'title'        => ['type' => 'VARCHAR', 'constraint' => 150],
            'building'     => ['type' => 'VARCHAR', 'constraint' => 150],
            'floor'        => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'details'      => ['type' => 'TEXT', 'null' => true],
            'priority'     => ['type' => 'ENUM', 'constraint' => ['Routine', 'Urgent'], 'default' => 'Routine'],
            'status'       => ['type' => 'ENUM', 'constraint' => ['Pending', 'In Progress', 'Completed'], 'default' => 'Pending'],
            'requested_by' => ['type' => 'VARCHAR', 'constraint' => 150],
            'created_at'   => ['type' => 'DATETIME'],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'completed_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('building');
        $this->forge->createTable('work_orders');
    }

    public function down()
    {
        $this->forge->dropTable('work_orders');
    }
}
