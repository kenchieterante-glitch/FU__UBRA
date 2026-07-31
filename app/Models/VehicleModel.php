<?php

namespace App\Models;

use CodeIgniter\Model;

class VehicleModel extends Model
{
    protected $table         = 'vehicles';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'vehicle_name', 'plate_no', 'type', 'driver_id',
        'department_id', 'gps_status', 'inspection_status', 'availability',
        'is_archived', 'archived_at', 'last_activity_at'
    ];

    // Alias so views can use $v['model'] instead of $v['vehicle_name']
    protected $afterFind = ['addModelAlias'];

    protected function addModelAlias(array $data)
    {
        if (!empty($data['data'])) {
            if (isset($data['data']['vehicle_name'])) {
                // Single row
                $data['data']['model']       = $data['data']['vehicle_name'];
            } else {
                // Array of rows
                foreach ($data['data'] as &$row) {
                    $row['model'] = $row['vehicle_name'] ?? '—';
                }
            }
        }
        return $data;
    }

    public function getAllWithDetails()
    {
        $builder = $this->select('vehicles.*, d.name as department_name, p.full_name as driver_name')
                        ->join('departments d', 'd.id = vehicles.department_id', 'left')
                        ->join('personnel p', 'p.id = vehicles.driver_id', 'left')
                        ->where('vehicles.is_archived', 0)
                        ->orderBy('vehicles.id', 'DESC');
        return $builder->findAll();
    }

    public function getAvailableVehicles()
    {
        return $this->where('availability', 'Available')->where('is_archived', 0)->findAll();
    }

    // Single source of truth for fleet counts — reused by Dashboard, Vehicle Management,
    // and GPS Tracker so the same numbers show everywhere.
    public function getFleetStats(): array
    {
        $total = $this->where('is_archived', 0)->countAllResults();
        $available = $this->where('is_archived', 0)->where('availability', 'Available')->countAllResults();
        $inUse = $this->where('is_archived', 0)->where('availability', 'In Use')->countAllResults();
        $maintenance = $this->where('is_archived', 0)->where('availability', 'Maintenance')->countAllResults();

        return [
            'total'       => $total,
            'available'   => $available,
            'in_use'      => $inUse,
            'maintenance' => $maintenance,
        ];
    }
}
