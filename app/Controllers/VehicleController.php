<?php

namespace App\Controllers;

use App\Models\VehicleModel;
use App\Models\GPSModel;
use App\Models\PersonnelModel;
use App\Models\DepartmentModel;
use App\Models\FuelLogModel;
use App\Models\TravelModel;

class VehicleController extends BaseController
{
    protected $vehicleModel;
    protected $gpsModel;
    protected $personnelModel;
    protected $departmentModel;
    protected $fuelLogModel;
    protected $travelModel;

    public function __construct()
    {
        $this->vehicleModel = new VehicleModel();
        $this->gpsModel     = new GPSModel();
        $this->personnelModel = new PersonnelModel();
        $this->departmentModel = new DepartmentModel();
        $this->fuelLogModel = new FuelLogModel();
        $this->travelModel = new TravelModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $vehicles = $this->vehicleModel->getAllWithDetails();
        $fleetStats = $this->vehicleModel->getFleetStats();

        // Basic average-consumption fuel prediction per vehicle, computed
        // from that vehicle's own fuel log history (see FuelLogModel::getPrediction).
        $fuelPredictions = [];
        foreach ($vehicles as $v) {
            $fuelPredictions[$v['id']] = $this->fuelLogModel->getPrediction((int) $v['id']);
        }

        // Trip history grouped by vehicle, most recent first, for the
        // per-vehicle detail popup (driver/fuel/PSI/history at a glance).
        $tripsByVehicle = [];
        foreach ($this->travelModel->getAllWithDetails() as $t) {
            if (empty($t['assigned_vehicle_id'])) continue;
            $tripsByVehicle[$t['assigned_vehicle_id']][] = [
                'date'        => date('M j, Y', strtotime($t['travel_date'])),
                'destination' => $t['destination'],
                'driver'      => $t['driver_name'] ?? 'Unassigned',
                'status'      => $t['status'],
            ];
        }

        $vehicleDetails = [];
        foreach ($vehicles as $v) {
            $fuelLogs = $this->fuelLogModel->getForVehicle((int) $v['id']);
            $vehicleDetails[$v['id']] = [
                'name'         => $v['vehicle_name'],
                'plate'        => $v['plate_no'],
                'type'         => $v['type'],
                'driver'       => $v['driver_name'] ?? 'Unassigned',
                'department'   => $v['department_name'] ?? 'Unassigned',
                'gpsStatus'    => $v['gps_status'],
                'inspection'   => $v['inspection_status'],
                'availability' => $v['availability'],
                'tirePressure' => $v['tire_pressure_psi'] !== null ? $v['tire_pressure_psi'] . ' PSI' : 'Not recorded',
                'prediction'   => $fuelPredictions[$v['id']],
                'fuelLogs'     => array_map(fn($f) => [
                    'date'    => date('M j, Y', strtotime($f['logged_at'])),
                    'liters'  => (float) $f['liters_filled'],
                    'odo'     => (float) $f['odometer_km'],
                    'by'      => $f['logged_by'],
                ], array_reverse($fuelLogs)),
                'trips' => array_slice($tripsByVehicle[$v['id']] ?? [], 0, 10),
            ];
        }

        $data = [
            'title'    => 'Vehicle Management',
            'pageCss'  => 'vehicle.css',
            'vehicles' => $vehicles,
            'total_vehicles'     => $fleetStats['total'],
            'available_vehicles' => $fleetStats['available'],
            'inuse_vehicles'     => $fleetStats['in_use'],
            'maintenance_due'    => $this->vehicleModel->where('inspection_status', 'Due Soon')->countAllResults(),
            'personnel'   => $this->personnelModel->getDrivers(),
            'departments' => $this->departmentModel->findAll(),
            'fuel_predictions' => $fuelPredictions,
            'vehicle_details_json' => $this->jsonForScript($vehicleDetails),
        ];

        return view('vehicles/index', $data);
    }

    public function add()
    {
        $this->vehicleModel->insert([
            'vehicle_name'      => $this->request->getPost('vehicle_name'),
            'plate_no'          => $this->request->getPost('plate_no'),
            'type'              => $this->request->getPost('type'),
            'driver_id'         => $this->request->getPost('driver_id') ?: null,
            'department_id'     => $this->request->getPost('department_id') ?: null,
            'gps_status'        => $this->request->getPost('gps_status') ?? 'Offline',
            'inspection_status' => $this->request->getPost('inspection_status') ?? 'Pending',
            'availability'      => $this->request->getPost('availability') ?? 'Available',
        ]);

        return redirect()->to('/vehicles')->with('success', 'Vehicle registered successfully.');
    }

    public function edit($id)
    {
        $this->vehicleModel->update($id, [
            'vehicle_name'      => $this->request->getPost('vehicle_name'),
            'plate_no'          => $this->request->getPost('plate_no'),
            'type'              => $this->request->getPost('type'),
            'driver_id'         => $this->request->getPost('driver_id') ?: null,
            'department_id'     => $this->request->getPost('department_id') ?: null,
            'inspection_status' => $this->request->getPost('inspection_status'),
            'availability'      => $this->request->getPost('availability'),
        ]);

        return redirect()->to('/vehicles')->with('success', 'Vehicle updated successfully.');
    }

    public function delete($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $this->vehicleModel->update($id, [
            'is_archived' => 1,
            'archived_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/vehicles')->with('success', 'Vehicle archived.');
    }

    // Logs a refuel (odometer reading + liters filled) so the fuel-need
    // prediction has real history to compute an average consumption rate from.
    public function logFuel($vehicleId)
    {
        $odometer = $this->request->getPost('odometer_km');
        $liters   = $this->request->getPost('liters_filled');
        $loggedAt = $this->request->getPost('logged_at') ?: date('Y-m-d');

        if (!is_numeric($odometer) || !is_numeric($liters) || (float) $liters <= 0) {
            return redirect()->to('/vehicles')->with('error', 'Enter a valid odometer reading and liters filled.');
        }

        $this->fuelLogModel->insert([
            'vehicle_id'    => $vehicleId,
            'odometer_km'   => (float) $odometer,
            'liters_filled' => (float) $liters,
            'logged_at'     => $loggedAt,
            'logged_by'     => (string) (session()->get('full_name') ?? session()->get('emp_id') ?? 'Unknown'),
            'notes'         => $this->request->getPost('notes'),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/vehicles')->with('success', 'Fuel log recorded.');
    }
}