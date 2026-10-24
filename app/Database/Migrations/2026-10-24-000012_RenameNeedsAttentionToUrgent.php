<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

// "Needs Attention" is shown as the single word "Urgent" (clearer for older users).
class RenameNeedsAttentionToUrgent extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE janitorial_inspections MODIFY result ENUM('Passed','Needs Attention','Urgent') NOT NULL");
        $this->db->query("UPDATE janitorial_inspections SET result = 'Urgent' WHERE result = 'Needs Attention'");
        $this->db->query("ALTER TABLE janitorial_inspections MODIFY result ENUM('Passed','Urgent') NOT NULL");

        $this->db->query("ALTER TABLE safety_inspections MODIFY safety_status ENUM('Safe','Needs Attention','Unsafe','Urgent') NOT NULL");
        $this->db->query("UPDATE safety_inspections SET safety_status = 'Urgent' WHERE safety_status = 'Needs Attention'");
        $this->db->query("ALTER TABLE safety_inspections MODIFY safety_status ENUM('Safe','Urgent','Unsafe') NOT NULL");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE janitorial_inspections MODIFY result ENUM('Passed','Urgent','Needs Attention') NOT NULL");
        $this->db->query("UPDATE janitorial_inspections SET result = 'Needs Attention' WHERE result = 'Urgent'");
        $this->db->query("ALTER TABLE janitorial_inspections MODIFY result ENUM('Passed','Needs Attention') NOT NULL");

        $this->db->query("ALTER TABLE safety_inspections MODIFY safety_status ENUM('Safe','Urgent','Unsafe','Needs Attention') NOT NULL");
        $this->db->query("UPDATE safety_inspections SET safety_status = 'Needs Attention' WHERE safety_status = 'Urgent'");
        $this->db->query("ALTER TABLE safety_inspections MODIFY safety_status ENUM('Safe','Needs Attention','Unsafe') NOT NULL");
    }
}
