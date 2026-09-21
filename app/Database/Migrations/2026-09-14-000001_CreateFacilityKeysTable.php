<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFacilityKeysTable extends Migration
{
    public function up()
    {
        // Table named facility_keys (not `keys`) because KEY is a reserved
        // word in MySQL — using it as a table name would need backticks
        // everywhere and invites mistakes.
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'key_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            // The physical NFC tag's unique id, read by the guard's phone.
            // One tag per key, so this has to be unique.
            'nfc_uid' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Available', 'Borrowed'],
                'default'    => 'Available',
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
        $this->forge->addUniqueKey('nfc_uid');
        $this->forge->createTable('facility_keys');

        $this->forge->addColumn('key_borrow_logs', [
            // Nullable: existing rows logged before this feature (free-text
            // key_item only) stay valid with no key_id.
            'key_id' => ['type' => 'INT', 'null' => true, 'after' => 'key_item'],
        ]);
        $this->forge->addForeignKey('key_id', 'facility_keys', 'id', '', 'SET NULL', 'key_borrow_logs');
        $this->forge->processIndexes('key_borrow_logs');
    }

    public function down()
    {
        $this->forge->dropForeignKey('key_borrow_logs', 'key_borrow_logs_key_id_foreign');
        $this->forge->dropColumn('key_borrow_logs', ['key_id']);
        $this->forge->dropTable('facility_keys');
    }
}
