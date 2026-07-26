<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTravelRequestsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'requester_id' => [
                'type' => 'INT',
            ],
            'vehicle_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'driver_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'destination' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'purpose' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'departure_date' => [
                'type' => 'DATE',
            ],
            'departure_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'expected_return_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'expected_return_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'actual_return_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'actual_return_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'passengers' => [
                'type' => 'INT',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected', 'completed', 'cancelled'],
                'default' => 'pending',
            ],
            'approved_by' => [
                'type' => 'INT',
                'null' => true,
            ],
            'approval_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_flagged' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'is_archived' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('requester_id');
        $this->forge->addKey('vehicle_id');
        $this->forge->addKey('driver_id');
        $this->forge->createTable('travel_requests');
    }

    public function down()
    {
        $this->forge->dropTable('travel_requests');
    }
}
