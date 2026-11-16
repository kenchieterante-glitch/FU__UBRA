<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

// Every text the system would send (or did send) is kept here so the admin can review it; works in Simulated mode too.
class CreateSmsOutbox extends Migration
{
    public function up()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS sms_outbox (
            id INT AUTO_INCREMENT PRIMARY KEY,
            personnel_id INT NULL,
            recipient_name VARCHAR(120) NOT NULL,
            phone VARCHAR(20) NULL,
            message TEXT NOT NULL,
            trigger_label VARCHAR(80) NOT NULL DEFAULT 'Manual',
            mode ENUM('Simulated','Sent','Failed','No number') NOT NULL DEFAULT 'Simulated',
            detail VARCHAR(255) NULL,
            created_at DATETIME NOT NULL,
            INDEX (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function down()
    {
        $this->forge->dropTable('sms_outbox', true);
    }
}
