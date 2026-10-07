<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFloorToJanitorialInspections extends Migration
{
    public function up()
    {
        $this->forge->addColumn('janitorial_inspections', [
            'floor' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'building'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('janitorial_inspections', 'floor');
    }
}
