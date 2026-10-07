<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFloorToFacilityKeys extends Migration
{
    public function up()
    {
        $this->forge->addColumn('facility_keys', [
            'floor' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'location'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('facility_keys', 'floor');
    }
}
