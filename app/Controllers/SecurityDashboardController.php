<?php

namespace App\Controllers;

use App\Models\FireExtinguisherModel;
use App\Models\KeyBorrowLogModel;
use App\Models\SafetyWorkOrderModel;
use App\Models\AirconUnitModel;
use App\Models\TravelModel;
use App\Models\VehicleModel;

class SecurityDashboardController extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $units = (new FireExtinguisherModel())->findAll();
        $today = date('Y-m-d');
        $criticalFe = count(array_filter(
            $units,
            fn($u) => in_array($u['status'], ['Defective', 'Missing'], true)
                || (!empty($u['next_due']) && $u['next_due'] < $today)
        ));

        $keyLogs    = (new KeyBorrowLogModel())->getAllWithTrip();
        $activeKeys = count(array_filter($keyLogs, fn($k) => $k['status'] === 'Active'));

        $trips             = (new TravelModel())->getAllWithDetails();
        $pendingDispatches = count(array_filter($trips, fn($t) => $t['status'] === 'Approved'));

        $openWorkOrders = (new SafetyWorkOrderModel())->where('stage !=', 'Completed/Verified')->countAllResults();
        $airconUnits    = (new AirconUnitModel())->countAll();
        $fleetStats     = (new VehicleModel())->getFleetStats();

        $hour = (int) date('H');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

        return view('security_dashboard/index', [
            'title'               => 'Security Dashboard',
            'pageCss'             => 'safety.css',
            'full_name'           => session()->get('full_name'),
            'greeting'            => $greeting,
            'last_updated'        => date('l, F j, Y — g:i A'),
            'total_extinguishers' => count($units),
            'critical_fe'         => $criticalFe,
            'active_keys'         => $activeKeys,
            'pending_dispatches'  => $pendingDispatches,
            'open_work_orders'    => $openWorkOrders,
            'aircon_units'        => $airconUnits,
            'fleet_total'         => $fleetStats['total'],
            'fleet_available'     => $fleetStats['available'],
            'fleet_in_use'        => $fleetStats['in_use'],
            'fleet_maintenance'   => $fleetStats['maintenance'],
        ]);
    }
}
