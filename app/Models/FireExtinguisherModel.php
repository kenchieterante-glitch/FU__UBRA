<?php
namespace App\Models;
use CodeIgniter\Model;

class FireExtinguisherModel extends Model
{
    protected $table         = 'fire_extinguishers';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'unit_id', 'type', 'location', 'floor', 'weight_kg', 'last_inspection', 'next_due',
        'status', 'year_acquired', 'inspector', 'assigned_guard', 'notes',
    ];

    public function getBuildingCounts(): array
    {
        return $this->select('location, COUNT(*) AS count')
                    ->groupBy('location')
                    ->findAll();
    }
}
