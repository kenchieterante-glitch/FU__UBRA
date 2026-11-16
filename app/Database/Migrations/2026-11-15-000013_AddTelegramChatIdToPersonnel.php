<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

// Telegram alerts: each person links their own Telegram chat once, and the system stores the chat id here.
class AddTelegramChatIdToPersonnel extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('telegram_chat_id', 'personnel')) {
            $this->forge->addColumn('personnel', [
                'telegram_chat_id' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true, 'after' => 'contact_number'],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('telegram_chat_id', 'personnel')) {
            $this->forge->dropColumn('personnel', 'telegram_chat_id');
        }
    }
}
