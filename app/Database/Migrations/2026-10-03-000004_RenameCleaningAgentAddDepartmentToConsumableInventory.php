<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameCleaningAgentAddDepartmentToConsumableInventory extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE consumable_inventory MODIFY category ENUM('Cleaning Agent','Cleaning Detergent','Tools','Disposable','Equipment') NOT NULL DEFAULT 'Cleaning Detergent'");
        $this->db->query("UPDATE consumable_inventory SET category='Cleaning Detergent' WHERE category='Cleaning Agent'");
        $this->db->query("ALTER TABLE consumable_inventory MODIFY category ENUM('Cleaning Detergent','Tools','Disposable','Equipment') NOT NULL DEFAULT 'Cleaning Detergent'");
        $this->forge->addColumn('consumable_inventory', [
            'department' => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => 'Facilities', 'after' => 'category'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('consumable_inventory', 'department');
        $this->db->query("ALTER TABLE consumable_inventory MODIFY category ENUM('Cleaning Agent','Cleaning Detergent','Tools','Disposable','Equipment') NOT NULL DEFAULT 'Cleaning Agent'");
        $this->db->query("UPDATE consumable_inventory SET category='Cleaning Agent' WHERE category='Cleaning Detergent'");
        $this->db->query("ALTER TABLE consumable_inventory MODIFY category ENUM('Cleaning Agent','Tools','Disposable','Equipment') NOT NULL DEFAULT 'Cleaning Agent'");
    }
}
