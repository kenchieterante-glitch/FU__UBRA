<?php

namespace App\Models;

use CodeIgniter\Model;

class SafetyInspectionModel extends Model
{
    protected $table         = 'safety_inspections';
    use \App\Models\Concerns\HidesArchived;
    protected string $archiveType = 'inspection';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'building', 'inspection_month', 'safety_status', 'remarks', 'inspected_by', 'inspected_at',
    ];

    public const STATUSES = ['Safe', 'Urgent', 'Unsafe'];
}
