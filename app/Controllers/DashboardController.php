<?php

namespace App\Controllers;

use App\Models\ToolsModel;
use App\Models\VehicleModel;
use App\Models\PersonnelModel;

class DashboardController extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')
                ->with('error', 'You must be logged in to access the dashboard.');
        }

        $toolsModel = new ToolsModel();
        $vehicleModel = new VehicleModel();
        $personnelModel = new PersonnelModel();

        $data = [
            'title'             => 'Dashboard',
            'pageCss'           => 'dashboard.css',
            'total_tools'       => $toolsModel->where('is_archived', 0)->countAllResults(),
            'total_vehicles'    => $vehicleModel->where('is_archived', 0)->countAllResults(),
            'total_personnel'   => $personnelModel->countAllResults(),
            'active_vehicles'   => $vehicleModel->where('availability', 'In Use')->where('is_archived', 0)->findAll(),
        ];

        return view('dashboard/index', $data);
    }
}
?>