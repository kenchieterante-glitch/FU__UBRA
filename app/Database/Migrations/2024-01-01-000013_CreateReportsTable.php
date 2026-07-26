<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReportsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'report_title' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
            ],
            'report_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'generated_by' => [
                'type' => 'INT',
            ],
            'data_summary' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'file_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'is_public' => [
                'type' => 'BOOLEAN',
                'default' => false,
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
        $this->forge->addKey('generated_by');
        $this->forge->createTable('reports');
    }

    public function down()
    {
        $this->forge->dropTable('reports');
    }
}
