<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePersonnelDocumentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'personnel_id' => ['type' => 'INT', 'constraint' => 11],
            'document_type_id' => ['type' => 'INT', 'constraint' => 11],
            'document_number' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'issue_date' => ['type' => 'DATE', 'null' => true],
            'expiration_date' => ['type' => 'DATE', 'null' => true],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'verification_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'PENDING'],
            'remarks' => ['type' => 'TEXT', 'null' => true],
            'uploaded_at' => ['type' => 'DATETIME', 'null' => true],
            'verified_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('personnel_id');
        $this->forge->addKey('document_type_id');
        $this->forge->createTable('personnel_documents');
    }

    public function down()
    {
        $this->forge->dropTable('personnel_documents');
    }
}
