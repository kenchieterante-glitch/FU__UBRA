<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

// Maps a vehicle to the Traccar device (GPS tracker) reporting its position —
// Traccar's own "identifier"/uniqueId, not a local foreign key, since the
// tracker fleet lives on a separate Traccar server.
class AddGpsDeviceIdToVehiclesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('vehicles', [
            'gps_device_id' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'plate_no'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('vehicles', 'gps_device_id');
    }
}
