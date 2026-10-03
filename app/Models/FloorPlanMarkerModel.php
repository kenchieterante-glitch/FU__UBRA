<?php

namespace App\Models;

use CodeIgniter\Model;

class FloorPlanMarkerModel extends Model
{
    protected $table         = 'floor_plan_markers';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['plan_file', 'equipment_type', 'label', 'x_pct', 'y_pct', 'status', 'expires_on', 'created_by', 'created_at'];

    public const TYPES = ['Fire Extinguisher', 'Fire Alarm', 'Smoke Detector', 'Emergency Exit Sign'];
}
