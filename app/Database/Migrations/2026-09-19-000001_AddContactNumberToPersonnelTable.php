<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddContactNumberToPersonnelTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('personnel', [
            'contact_number' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'email'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('personnel', ['contact_number']);
    }
}
