<?php
namespace App\Models;
use CodeIgniter\Model;

class AirconUnitModel extends Model
{
    protected $table         = 'aircon_units';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'location', 'floor', 'unit_name', 'last_cleaning', 'next_schedule',
        'condition_status', 'assigned_tech',
    ];

    public function getByLocation(string $location)
    {
        return $this->where('location', $location)->orderBy('id', 'DESC')->first();
    }
}
