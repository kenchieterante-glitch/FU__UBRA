<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDepartmentToFireExtinguishersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('fire_extinguishers', [
            'department_id' => ['type' => 'INT', 'null' => true, 'after' => 'floor'],
        ]);
        $this->forge->addForeignKey('department_id', 'departments', 'id', '', 'SET NULL', 'fire_extinguishers');
        $this->forge->processIndexes('fire_extinguishers');
    }

    public function down()
    {
        $this->forge->dropForeignKey('fire_extinguishers', 'fire_extinguishers_department_id_foreign');
        $this->forge->dropColumn('fire_extinguishers', ['department_id']);
    }
}
