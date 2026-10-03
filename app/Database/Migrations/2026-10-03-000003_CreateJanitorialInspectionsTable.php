<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJanitorialInspectionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'building'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'inspection_month' => ['type' => 'CHAR', 'constraint' => 7],
            'result'           => ['type' => 'ENUM', 'constraint' => ['Passed', 'Needs Attention']],
            'inspected_by'     => ['type' => 'VARCHAR', 'constraint' => 150],
            'notes'            => ['type' => 'TEXT', 'null' => true],
            'inspected_at'     => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['building', 'inspection_month']);
        $this->forge->createTable('janitorial_inspections');
    }

    public function down()
    {
        $this->forge->dropTable('janitorial_inspections');
    }
}
