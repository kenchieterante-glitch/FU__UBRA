<?php

namespace App\Models;

use CodeIgniter\Model;

class JanitorialInspectionModel extends Model
{
    protected $table         = 'janitorial_inspections';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'building', 'inspection_month', 'result', 'inspected_by', 'notes', 'inspected_at',
    ];

    public const RESULTS = ['Passed', 'Needs Attention'];
}
