<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReturnsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'borrow_id' => [
                'type' => 'INT',
            ],
            'tool_id' => [
                'type' => 'INT',
            ],
            'return_date' => [
                'type' => 'DATE',
            ],
            'return_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'quantity_returned' => [
                'type' => 'INT',
                'default' => 1,
            ],
            'condition_on_return' => [
                'type' => 'ENUM',
                'constraint' => ['good', 'damaged', 'lost'],
                'default' => 'good',
            ],
            'damage_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'replacement_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'received_by' => [
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
        $this->forge->addKey('borrow_id');
        $this->forge->addKey('tool_id');
        $this->forge->createTable('returns');
    }

    public function down()
    {
        $this->forge->dropTable('returns');
    }
}
