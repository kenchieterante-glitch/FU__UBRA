<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFuelLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'vehicle_id' => [
                'type' => 'INT',
            ],
            'odometer_km' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,1',
            ],
            'liters_filled' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
            ],
            'logged_at' => [
                'type' => 'DATE',
            ],
            'logged_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('vehicle_id');
        $this->forge->addForeignKey('vehicle_id', 'vehicles', 'id', '', 'CASCADE');
        $this->forge->createTable('fuel_logs');
    }

    public function down()
    {
        $this->forge->dropTable('fuel_logs');
    }
}
