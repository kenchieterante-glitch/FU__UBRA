<?php

namespace App\Models;

use CodeIgniter\Model;

class SafetyEquipmentModel extends Model
{
    protected $table         = 'safety_equipment';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'equipment_type', 'code', 'building', 'floor', 'location_note',
        'status', 'last_checked', 'next_check', 'installed_on', 'expires_on', 'remarks', 'created_at',
    ];

    public const TYPES = ['Fire Alarm', 'Smoke Detector', 'Emergency Exit Sign'];
}
