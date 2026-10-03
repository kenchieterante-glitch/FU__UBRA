<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFloorPlanMarkersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'plan_file'      => ['type' => 'VARCHAR', 'constraint' => 200],
            'equipment_type' => ['type' => 'ENUM', 'constraint' => ['Fire Extinguisher', 'Fire Alarm', 'Smoke Detector', 'Emergency Exit Sign']],
            'label'          => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'x_pct'          => ['type' => 'DECIMAL', 'constraint' => '6,3'],
            'y_pct'          => ['type' => 'DECIMAL', 'constraint' => '6,3'],
            'status'         => ['type' => 'ENUM', 'constraint' => ['Working', 'Needs Repair', 'Missing'], 'default' => 'Working'],
            'expires_on'     => ['type' => 'DATE', 'null' => true],
            'created_by'     => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'created_at'     => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('plan_file');
        $this->forge->createTable('floor_plan_markers');
    }

    public function down()
    {
        $this->forge->dropTable('floor_plan_markers');
    }
}
