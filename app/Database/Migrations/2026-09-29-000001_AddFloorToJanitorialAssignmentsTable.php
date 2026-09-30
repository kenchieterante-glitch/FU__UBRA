<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFloorToJanitorialAssignmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('janitorial_assignments', [
            'floor' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'assigned_zone'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('janitorial_assignments', ['floor']);
    }
}
