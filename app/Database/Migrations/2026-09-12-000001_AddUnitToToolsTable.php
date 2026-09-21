<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUnitToToolsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tools', [
            'unit' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'pcs', 'after' => 'reorder_threshold'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tools', ['unit']);
    }
}
