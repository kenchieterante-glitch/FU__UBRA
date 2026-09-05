<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

// UbraChatLogModel expected this table since it was written, but nothing
// ever created it — every chat turn 500'd trying to log itself.
class CreateUbraChatLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'emp_id' => ['type' => 'VARCHAR', 'constraint' => 50],
            'role' => ['type' => 'VARCHAR', 'constraint' => 20],
            'message' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('emp_id');
        $this->forge->createTable('ubra_chat_logs');
    }

    public function down()
    {
        $this->forge->dropTable('ubra_chat_logs');
    }
}
