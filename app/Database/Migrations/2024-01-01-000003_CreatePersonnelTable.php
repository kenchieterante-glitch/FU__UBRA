<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePersonnelTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'first_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'last_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'employee_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
            ],
            'position' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'department_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'date_of_birth' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'profile_picture' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addKey('department_id');
        $this->forge->createTable('personnel');
    }

    public function down()
    {
        $this->forge->dropTable('personnel');
    }
}
