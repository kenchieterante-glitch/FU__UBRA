<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePredictionsTable extends Migration
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
                'null' => true,
            ],
            'personnel_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'prediction_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'prediction_data' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'confidence_score' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'prediction_result' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'is_accurate' => [
                'type' => 'BOOLEAN',
                'null' => true,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('vehicle_id');
        $this->forge->addKey('personnel_id');
        $this->forge->createTable('predictions');
    }

    public function down()
    {
        $this->forge->dropTable('predictions');
    }
}
