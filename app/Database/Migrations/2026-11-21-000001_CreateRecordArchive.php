<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Marks records of modules that have no archive column of their own as archived (Reports page "Archive"). */
class CreateRecordArchive extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'record_type' => ['type' => 'VARCHAR', 'constraint' => 30],
            'record_id'   => ['type' => 'INT', 'constraint' => 11],
            'archived_at' => ['type' => 'DATETIME'],
            'archived_by' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['record_type', 'record_id']);
        $this->forge->createTable('record_archive', true);
    }

    public function down()
    {
        $this->forge->dropTable('record_archive', true);
    }
}
