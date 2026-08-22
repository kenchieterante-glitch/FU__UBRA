<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePersonnelContractsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'personnel_id' => ['type' => 'INT', 'constraint' => 11],
            'job_order_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'contract_number' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'contract_start_date' => ['type' => 'DATE', 'null' => true],
            'contract_end_date' => ['type' => 'DATE', 'null' => true],
            'contract_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'ACTIVE'],
            'renewal_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'remarks' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('personnel_id');
        $this->forge->addKey('job_order_id');
        $this->forge->createTable('personnel_contracts');
    }

    public function down()
    {
        $this->forge->dropTable('personnel_contracts');
    }
}
