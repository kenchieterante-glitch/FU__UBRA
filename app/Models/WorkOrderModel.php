<?php

namespace App\Models;

use CodeIgniter\Model;

class WorkOrderModel extends Model
{
    protected $table         = 'work_orders';
    use \App\Models\Concerns\HidesArchived;
    protected string $archiveType = 'facwo';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'title', 'building', 'floor', 'details', 'priority', 'status',
        'requested_by', 'created_at', 'updated_at', 'completed_at',
    ];

    public const STATUSES = ['Pending', 'In Progress', 'Completed'];
}
