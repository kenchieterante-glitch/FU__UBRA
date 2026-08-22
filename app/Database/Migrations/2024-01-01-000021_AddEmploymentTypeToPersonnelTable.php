<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmploymentTypeToPersonnelTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('personnel', [
            'employment_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'Regular', 'after' => 'department_id'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('personnel', ['employment_type']);
    }
}
