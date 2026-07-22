<?php

namespace App\Controllers;

use App\Models\ToolsModel;
use App\Models\VehicleModel;
use App\Models\PersonnelModel;
use App\Models\TravelModel;

class DashboardController extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            // Set test session for development
            session()->set([
                'user_id'     => 1,
                'full_name'   => 'Test User',
                'role'        => 'Admin',
                'department'  => 'Facilities',
                'isLoggedIn'  => true,
            ]);
        }

        $toolsModel = new ToolsModel();
        $vehicleModel = new VehicleModel();
        $personnelModel = new PersonnelModel();
        $travelModel = new TravelModel();

        $data = [
            'title'             => 'Dashboard',
            'pageCss'           => 'dashboard.css',
            'total_tools'       => $toolsModel->where('is_archived', 0)->countAllResults(),
            'total_vehicles'    => $vehicleModel->where('is_archived', 0)->countAllResults(),
            'total_personnel'   => $personnelModel->countAllResults(),
            'scheduled_trips'   => $travelModel->where('status', 'Pending')->where('is_archived', 0)->countAllResults(),
            'active_vehicles'   => $vehicleModel->where('availability', 'In Use')->where('is_archived', 0)->findAll(),
        ];

        return view('dashboard/index', $data);
    }
}
?>