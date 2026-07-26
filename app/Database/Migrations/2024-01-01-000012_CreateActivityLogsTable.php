<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActivityLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'entity_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'entity_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'old_values' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'new_values' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('entity_type');
        $this->forge->createTable('activity_logs');
    }

    public function down()
    {
        $this->forge->dropTable('activity_logs');
    }
}
