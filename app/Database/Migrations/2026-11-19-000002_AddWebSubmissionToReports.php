<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Reports can now be submitted from the web system: adds the narrative and the submitter's name. */
class AddWebSubmissionToReports extends Migration
{
    public function up()
    {
        $this->forge->addColumn('reports', [
            'details'      => ['type' => 'TEXT', 'null' => true, 'after' => 'type_module'],
            'submitted_by' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'details'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('reports', ['details', 'submitted_by']);
    }
}
