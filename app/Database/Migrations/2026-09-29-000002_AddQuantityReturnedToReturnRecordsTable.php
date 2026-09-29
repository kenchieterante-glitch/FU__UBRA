<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

// Lets a consumable's borrow be returned in partial batches (e.g. borrow 10,
// return 4 now and 6 later) instead of only ever all-at-once — each return
// event records how much of the original borrow it actually covers.
class AddQuantityReturnedToReturnRecordsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('return_records', [
            'quantity_returned' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => 1, 'after' => 'tool_id'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('return_records', ['quantity_returned']);
    }
}
