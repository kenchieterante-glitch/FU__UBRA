<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

// Records each "quantity left" reading a janitor submits from the mobile
// app after cleaning a zone — kept separate from refill_log (which logs
// admin restocking) so both directions of the stock story are traceable.
class CreateConsumableStockReportsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'inventory_item_id' => ['type' => 'INT', 'constraint' => 11],
            'item_name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'zone' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'quantity_left' => ['type' => 'DECIMAL', 'constraint' => '8,2'],
            'unit' => ['type' => 'VARCHAR', 'constraint' => 40],
            'reported_by' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'reported_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('inventory_item_id');
        $this->forge->createTable('consumable_stock_reports');
    }

    public function down()
    {
        $this->forge->dropTable('consumable_stock_reports');
    }
}
