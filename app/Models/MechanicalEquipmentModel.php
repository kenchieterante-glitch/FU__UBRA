<?php

namespace App\Models;

use CodeIgniter\Model;

class MechanicalEquipmentModel extends Model
{
    protected $table         = 'mechanical_equipment';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['code', 'name', 'equipment_type', 'location', 'status', 'last_service', 'next_service', 'remarks', 'created_at'];

    public const STATUSES = ['Operational', 'Needs Repair', 'Under Maintenance', 'Out of Service'];
}
