<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInstalledExpiresToSafetyEquipment extends Migration
{
    public function up()
    {
        foreach (['fire_extinguishers' => 'year_acquired', 'safety_equipment' => 'next_check'] as $table => $after) {
            $this->forge->addColumn($table, [
                'installed_on' => ['type' => 'DATE', 'null' => true, 'after' => $after],
                'expires_on'   => ['type' => 'DATE', 'null' => true, 'after' => 'installed_on'],
            ]);
        }
    }

    public function down()
    {
        foreach (['fire_extinguishers', 'safety_equipment'] as $table) {
            $this->forge->dropColumn($table, ['installed_on', 'expires_on']);
        }
    }
}
