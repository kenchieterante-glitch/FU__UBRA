<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Adds the Department of Buildings and Grounds (B&G / FGS) to the departments list used by Personnel records. */
class AddBuildingsAndGroundsDepartment extends Migration
{
    private const NAME = 'Buildings and Grounds (B&G/FGS)';

    public function up()
    {
        $exists = $this->db->table('departments')->where('name', self::NAME)->countAllResults();
        if (! $exists) {
            $this->db->table('departments')->insert(['name' => self::NAME]);
        }
    }

    public function down()
    {
        $this->db->table('departments')->where('name', self::NAME)->delete();
    }
}
