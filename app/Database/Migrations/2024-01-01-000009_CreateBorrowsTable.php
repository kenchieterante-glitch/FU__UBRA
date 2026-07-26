<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBorrowsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'tool_id' => [
                'type' => 'INT',
            ],
            'borrowed_by' => [
                'type' => 'INT',
            ],
            'borrow_date' => [
                'type' => 'DATE',
            ],
            'borrow_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'expected_return_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'actual_return_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'quantity_borrowed' => [
                'type' => 'INT',
                'default' => 1,
            ],
            'purpose' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['borrowed', 'returned', 'overdue'],
                'default' => 'borrowed',
            ],
            'approved_by' => [
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
        $this->forge->addKey('tool_id');
        $this->forge->addKey('borrowed_by');
        $this->forge->createTable('borrows');
    }

    public function down()
    {
        $this->forge->dropTable('borrows');
    }
}
