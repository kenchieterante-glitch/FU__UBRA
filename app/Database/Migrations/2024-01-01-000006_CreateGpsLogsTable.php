<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGpsLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'vehicle_id' => [
                'type' => 'INT',
            ],
            'travel_request_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'latitude' => [
                'type' => 'DECIMAL',
                'constraint' => '10,8',
                'null' => true,
            ],
            'longitude' => [
                'type' => 'DECIMAL',
                'constraint' => '11,8',
                'null' => true,
            ],
            'speed' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'direction' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'altitude' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'accuracy' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'location_address' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('vehicle_id');
        $this->forge->addKey('travel_request_id');
        $this->forge->createTable('gps_logs');
    }

    public function down()
    {
        $this->forge->dropTable('gps_logs');
    }
}
