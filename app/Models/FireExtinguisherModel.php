<?php
namespace App\Models;
use CodeIgniter\Model;

class FireExtinguisherModel extends Model
{
    protected $table         = 'fire_extinguishers';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'unit_id', 'type', 'location', 'weight_kg', 'last_inspection', 'next_due',
        'status', 'year_acquired', 'inspector', 'assigned_guard', 'notes',
    ];
}
