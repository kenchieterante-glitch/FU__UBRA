<?php

namespace App\Controllers;

use App\Models\GPSModel;
use App\Models\VehicleModel;

class GPSController extends BaseController
{
    protected $gpsModel;
    protected $vehicleModel;
    protected $session;

    public function __construct()
    {
        $this->gpsModel     = new GPSModel();
        $this->vehicleModel = new VehicleModel();
        $this->session      = \Config\Services::session();
    }

    public function index()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        // gps_status lives on the vehicles table itself — that's the single source of
        // truth for online/offline, matching what Vehicle Management shows for the same field.
        $vehicles = $this->vehicleModel->where('is_archived', 0)->findAll();
        $gpsLogs  = $this->gpsModel->getLatestPerVehicle();

        $gpsMap = [];
        foreach ($gpsLogs as $log) {
            $gpsMap[$log['vehicle_id']] = $log;
        }

        $fleet = [];
        foreach ($vehicles as $v) {
            $gps     = $gpsMap[$v['id']] ?? null;
            $fleet[] = array_merge($v, [
                'gps_status'    => $v['gps_status']        ?? 'Offline',
                'latitude'      => $gps['latitude']        ?? null,
                'longitude'     => $gps['longitude']       ?? null,
                'speed'         => 0,
                'signal'        => $gps['signal_strength'] ?? '—',
                'last_location' => '—',
                'logged_at'     => $gps['logged_at']       ?? null,
                'device_id'     => $gps['device_id']       ?? null,
                // map vehicle fields to expected view keys
                'model'         => $v['vehicle_name']      ?? '—',
                'driver_name'   => $v['driver']            ?? 'Unassigned',
                'inspection_status' => $v['inspection_status'] ?? 'Pending',
            ]);
        }

        $online  = count(array_filter($fleet, fn($v) => $v['gps_status'] === 'Online'));
        $offline = count($fleet) - $online;

        $data = [
            'title'         => 'GPS Tracker',
            'fleet'         => $fleet,
            'online_count'  => $online,
            'offline_count' => $offline,
            'transit_count' => count(array_filter($fleet, fn($v) => ($v['availability'] ?? '') === 'In Use')),
            'maint_count'   => count(array_filter($fleet, fn($v) => ($v['availability'] ?? '') === 'Maintenance')),
            'total'         => count($fleet),
            'vehicles'      => $vehicles,
            'flash_success' => $this->session->getFlashdata('success'),
        ];

        return view('gps/index', $data);
    }

    public function getVehicle($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $vehicle = $this->vehicleModel->find($id);
        if (!$vehicle) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);

        $latestGPS = $this->gpsModel->where('vehicle_id', $id)->orderBy('id', 'DESC')->first();

        return $this->response->setJSON(array_merge($vehicle, [
            'gps_status'    => $vehicle['gps_status']        ?? 'Offline',
            'latitude'      => $latestGPS['latitude']        ?? null,
            'longitude'     => $latestGPS['longitude']       ?? null,
            'speed'         => 0,
            'signal'        => $latestGPS['signal_strength'] ?? 0,
            'last_location' => '—',
            'logged_at'     => null,
            'device_id'     => $latestGPS['device_id']       ?? 'N/A',
            'model'         => $vehicle['vehicle_name']      ?? '—',
            'driver_name'   => $vehicle['driver']            ?? 'Unassigned',
        ]));
    }

    public function sync($vehicleId)
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');
        return $this->response->setJSON(['success' => true, 'synced_at' => date('Y-m-d H:i:s'), 'vehicle_id' => $vehicleId]);
    }

    public function logPing()
    {
        $data = [
            'vehicle_id' => $this->request->getPost('vehicle_id'),
            'latitude'   => $this->request->getPost('latitude'),
            'longitude'  => $this->request->getPost('longitude'),
            'status'     => 'Online',
            'device_id'  => $this->request->getPost('device_id') ?? '',
        ];
        if (empty($data['vehicle_id'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing vehicle_id']);
        }
        $this->gpsModel->insert($data);
        return $this->response->setJSON(['success' => true]);
    }
}
