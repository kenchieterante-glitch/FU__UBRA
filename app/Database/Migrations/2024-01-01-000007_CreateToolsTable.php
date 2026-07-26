<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateToolsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'tool_name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'tool_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'quantity' => [
                'type' => 'INT',
                'default' => 1,
            ],
            'quantity_available' => [
                'type' => 'INT',
                'default' => 1,
            ],
            'condition' => [
                'type' => 'ENUM',
                'constraint' => ['new', 'good', 'fair', 'damaged'],
                'default' => 'good',
            ],
            'purchase_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'purchase_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'location' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'assigned_to' => [
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
        $this->forge->addKey('assigned_to');
        $this->forge->createTable('tools');
    }

    public function down()
    {
        $this->forge->dropTable('tools');
    }
}
