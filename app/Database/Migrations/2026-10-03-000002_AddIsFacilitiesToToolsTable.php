<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsFacilitiesToToolsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tools', [
            'is_facilities' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'unit'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tools', 'is_facilities');
    }
}
