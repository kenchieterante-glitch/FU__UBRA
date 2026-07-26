<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJanitorialLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'personnel_id' => [
                'type' => 'INT',
            ],
            'location' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'task_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'task_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'start_time' => [
                'type' => 'DATETIME',
            ],
            'end_time' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'in-progress', 'completed'],
                'default' => 'pending',
            ],
            'quality_rating' => [
                'type' => 'INT',
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
        $this->forge->addKey('personnel_id');
        $this->forge->createTable('janitorial_logs');
    }

    public function down()
    {
        $this->forge->dropTable('janitorial_logs');
    }
}
