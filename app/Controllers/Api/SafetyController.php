<?php

namespace App\Controllers\Api;

use App\Models\FireExtinguisherModel;
use App\Models\KeyBorrowLogModel;

class SafetyController extends BaseApiController
{
    protected FireExtinguisherModel $feModel;
    protected KeyBorrowLogModel $keyLogModel;

    public function __construct()
    {
        $this->feModel     = new FireExtinguisherModel();
        $this->keyLogModel = new KeyBorrowLogModel();
    }

    public function index()
    {
        $units = $this->feModel->findAll();
        $today = date('Y-m-d');

        $needsAttention = array_values(array_filter($units, fn ($u) => in_array($u['status'], ['Defective', 'Missing'], true)));
        $dueForRefill   = array_values(array_filter($units, fn ($u) => $u['status'] === 'Refillable'));
        $overdue        = array_values(array_filter($units, fn ($u) => !empty($u['next_due']) && $u['next_due'] < $today));

        $total     = count($units);
        $readiness = $total > 0 ? round(($total - count($overdue)) / $total * 100) : 100;

        return $this->ok([
            'fire_extinguishers'    => $units,
            'coverage_total'        => $total,
            'coverage_attention'    => count($needsAttention),
            'coverage_refill'       => count($dueForRefill),
            'inspection_readiness'  => $readiness,
        ]);
    }

    public function addExtinguisher()
    {
        $unitId   = trim((string) $this->request->getPost('unit_id'));
        $location = trim((string) $this->request->getPost('location'));

        if ($unitId === '' || $location === '') {
            return $this->fail('Unit ID and location are required.', 422);
        }

        $id = $this->feModel->insert([
            'unit_id'         => $unitId,
            'type'            => $this->request->getPost('type') ?: 'CO2',
            'location'        => $location,
            'weight_kg'       => $this->request->getPost('weight_kg') ?: 6.0,
            'last_inspection' => $this->request->getPost('last_inspection') ?: null,
            'next_due'        => $this->request->getPost('next_due') ?: null,
            'status'          => $this->request->getPost('status') ?: 'New',
            'year_acquired'   => $this->request->getPost('year_acquired') ?: null,
            'inspector'       => $this->request->getPost('inspector') ?: null,
            'assigned_guard'  => $this->request->getPost('assigned_guard') ?: null,
        ], true);

        return $this->ok(['id' => $id], 201);
    }

    public function guardDashboard()
    {
        $logs   = $this->keyLogModel->getAllWithTrip();
        $active = array_values(array_filter($logs, fn ($l) => $l['status'] === 'Active'));

        return $this->ok([
            'key_logs'       => $logs,
            'active_borrows' => $active,
        ]);
    }

    public function keylogs()
    {
        return $this->ok(['key_logs' => $this->keyLogModel->getAllWithTrip()]);
    }
}
