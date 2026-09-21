<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

// Consumable tools (current_stock > 1, e.g. "Disinfectant Spray") can be
// borrowed in a quantity, not just as one whole unit — this records how many
// units a given borrow_records row actually covers, so returns credit the
// right amount back to tools.current_stock.
class AddQuantityToBorrowRecordsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('borrow_records', [
            'quantity' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => 1, 'after' => 'tool_id'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('borrow_records', 'quantity');
    }
}
