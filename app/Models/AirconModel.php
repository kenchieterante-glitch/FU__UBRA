<?php

namespace App\Models;

use CodeIgniter\Model;

class AirconModel extends Model
{
    protected $table         = 'aircon_units';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'unit_brand', 'location', 'building', 'last_cleaning', 'next_schedule',
        'condition_status', 'assigned_tech',
    ];

    public function getByBuilding(string $building)
    {
        return $this->where('building', $building)->orderBy('id', 'DESC')->findAll();
    }
}
