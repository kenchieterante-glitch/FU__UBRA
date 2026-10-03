<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSafetyEquipmentAndInspectionsTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'equipment_type' => ['type' => 'ENUM', 'constraint' => ['Fire Alarm', 'Smoke Detector', 'Emergency Exit Sign']],
            'code'           => ['type' => 'VARCHAR', 'constraint' => 50],
            'building'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'floor'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'location_note'  => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'status'         => ['type' => 'ENUM', 'constraint' => ['Working', 'Needs Repair', 'Missing'], 'default' => 'Working'],
            'last_checked'   => ['type' => 'DATE', 'null' => true],
            'next_check'     => ['type' => 'DATE', 'null' => true],
            'remarks'        => ['type' => 'TEXT', 'null' => true],
            'created_at'     => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('building');
        $this->forge->addKey('equipment_type');
        $this->forge->createTable('safety_equipment');

        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'building'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'inspection_month' => ['type' => 'CHAR', 'constraint' => 7],
            'safety_status'    => ['type' => 'ENUM', 'constraint' => ['Safe', 'Needs Attention', 'Unsafe']],
            'remarks'          => ['type' => 'TEXT', 'null' => true],
            'inspected_by'     => ['type' => 'VARCHAR', 'constraint' => 150],
            'inspected_at'     => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['building', 'inspection_month']);
        $this->forge->createTable('safety_inspections');
    }

    public function down()
    {
        $this->forge->dropTable('safety_inspections');
        $this->forge->dropTable('safety_equipment');
    }
}
