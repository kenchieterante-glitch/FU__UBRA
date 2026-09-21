<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

// Completed janitorial tasks reset every day so staff can redo them on the
// next shift, but that would silently erase the "who did what, when" trail —
// so a finished task is archived here first, then the live row is cleared.
class CreateJanitorialTaskHistoryTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'assignment_id' => ['type' => 'INT', 'constraint' => 11],
            'zone' => ['type' => 'VARCHAR', 'constraint' => 255],
            'task_name' => ['type' => 'VARCHAR', 'constraint' => 200],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20],
            'completed_at' => ['type' => 'DATETIME', 'null' => true],
            'performed_by' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'archived_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('assignment_id');
        $this->forge->createTable('janitorial_task_history');
    }

    public function down()
    {
        $this->forge->dropTable('janitorial_task_history');
    }
}
