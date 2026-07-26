<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationsTable extends Migration
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
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'related_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'related_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'is_read' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'is_dismissed' => [
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
        $this->forge->addKey('user_id');
        $this->forge->createTable('notifications');
    }

    public function down()
    {
        $this->forge->dropTable('notifications');
    }
}
