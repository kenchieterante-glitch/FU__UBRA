<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVehiclesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'vehicle_name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'vehicle_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'license_plate' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
            ],
            'registration_number' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'capacity' => [
                'type' => 'INT',
                'null' => true,
            ],
            'year_of_manufacture' => [
                'type' => 'INT',
                'null' => true,
            ],
            'fuel_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'condition_status' => [
                'type' => 'ENUM',
                'constraint' => ['excellent', 'good', 'fair', 'poor'],
                'default' => 'good',
            ],
            'maintenance_status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'maintenance', 'retired'],
                'default' => 'active',
            ],
            'assigned_driver_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'last_service_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'mileage' => [
                'type' => 'INT',
                'null' => true,
            ],
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
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
        $this->forge->addKey('assigned_driver_id');
        $this->forge->createTable('vehicles');
    }

    public function down()
    {
        $this->forge->dropTable('vehicles');
    }
}
