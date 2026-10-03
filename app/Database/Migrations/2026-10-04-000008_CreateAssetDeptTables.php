<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssetDeptTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'code'           => ['type' => 'VARCHAR', 'constraint' => 50],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 150],
            'equipment_type' => ['type' => 'VARCHAR', 'constraint' => 80],
            'location'       => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'status'         => ['type' => 'ENUM', 'constraint' => ['Operational', 'Needs Repair', 'Under Maintenance', 'Out of Service'], 'default' => 'Operational'],
            'last_service'   => ['type' => 'DATE', 'null' => true],
            'next_service'   => ['type' => 'DATE', 'null' => true],
            'remarks'        => ['type' => 'TEXT', 'null' => true],
            'created_at'     => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('mechanical_equipment');

        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'vehicle_id'   => ['type' => 'INT', 'constraint' => 11],
            'service_type' => ['type' => 'VARCHAR', 'constraint' => 120],
            'serviced_on'  => ['type' => 'DATE'],
            'odometer_km'  => ['type' => 'DECIMAL', 'constraint' => '10,1', 'null' => true],
            'cost'         => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'performed_by' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'next_due'     => ['type' => 'DATE', 'null' => true],
            'notes'        => ['type' => 'TEXT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('vehicle_id');
        $this->forge->createTable('vehicle_maintenance');

        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'wo_number'    => ['type' => 'VARCHAR', 'constraint' => 30],
            'wo_type'      => ['type' => 'ENUM', 'constraint' => ['Vehicle Repair', 'Mechanical Equipment']],
            'vehicle_id'   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'equipment_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'issue'        => ['type' => 'TEXT'],
            'priority'     => ['type' => 'ENUM', 'constraint' => ['Routine', 'Urgent'], 'default' => 'Routine'],
            'status'       => ['type' => 'ENUM', 'constraint' => ['Pending', 'In Progress', 'Completed', 'Cancelled'], 'default' => 'Pending'],
            'requested_by' => ['type' => 'VARCHAR', 'constraint' => 150],
            'assigned_to'  => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'created_at'   => ['type' => 'DATETIME'],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'completed_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('wo_number');
        $this->forge->addKey('wo_type');
        $this->forge->createTable('motorpool_work_orders');

        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'work_order_id' => ['type' => 'INT', 'constraint' => 11],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 30],
            'changed_by'    => ['type' => 'VARCHAR', 'constraint' => 150],
            'changed_at'    => ['type' => 'DATETIME'],
            'notes'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('work_order_id');
        $this->forge->createTable('motorpool_wo_history');
    }

    public function down()
    {
        foreach (['motorpool_wo_history', 'motorpool_work_orders', 'vehicle_maintenance', 'mechanical_equipment'] as $t) {
            $this->forge->dropTable($t, true);
        }
    }
}
