<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

// Waste Watch: one waste station per campus building (three segregated bins:
// non-biodegradable, biodegradable, hazardous) and one end-of-day weigh-in per station per day.
class CreateWasteWatchTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false, 'auto_increment' => true],
            'building'    => ['type' => 'VARCHAR', 'constraint' => 150],
            'location'    => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'is_archived' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('waste_stations', true);

        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false, 'auto_increment' => true],
            'station_id'   => ['type' => 'INT', 'constraint' => 11],
            'log_date'     => ['type' => 'DATE'],
            'non_bio_kg'   => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => 0],
            'bio_kg'       => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => 0],
            'hazardous_kg' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => 0],
            'checked_by'   => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'notes'        => ['type' => 'TEXT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['station_id', 'log_date']);
        $this->forge->createTable('waste_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('waste_logs', true);
        $this->forge->dropTable('waste_stations', true);
    }
}
