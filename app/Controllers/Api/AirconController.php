<?php

namespace App\Controllers\Api;

use App\Models\AirconModel;

class AirconController extends BaseApiController
{
    protected AirconModel $airconModel;

    public function __construct()
    {
        $this->airconModel = new AirconModel();
    }

    public function index()
    {
        $units = $this->airconModel->orderBy('id', 'DESC')->findAll();

        $byBuilding = [];
        foreach ($units as $u) {
            $byBuilding[$u['building']][] = $u;
        }

        return $this->ok(['units' => $units, 'by_building' => $byBuilding]);
    }

    public function add()
    {
        $brand    = trim((string) $this->request->getPost('unit_brand'));
        $location = trim((string) $this->request->getPost('location'));
        $building = trim((string) $this->request->getPost('building'));

        if ($brand === '' || $location === '' || $building === '') {
            return $this->fail('Unit brand, location, and building are required.', 422);
        }

        $id = $this->airconModel->insert([
            'unit_brand'       => $brand,
            'location'         => $location,
            'building'         => $building,
            'last_cleaning'    => $this->request->getPost('last_cleaning') ?: null,
            'next_schedule'    => $this->request->getPost('next_schedule') ?: null,
            'condition_status' => $this->request->getPost('condition_status') ?: 'Operational',
            'assigned_tech'    => $this->request->getPost('assigned_tech') ?: null,
        ], true);

        return $this->ok(['id' => $id], 201);
    }
}
