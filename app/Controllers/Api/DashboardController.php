<?php

namespace App\Controllers\Api;

use App\Models\PersonnelModel;
use App\Models\ToolsModel;
use App\Models\VehicleModel;

class DashboardController extends BaseApiController
{
    public function index()
    {
        $toolsModel      = new ToolsModel();
        $vehicleModel    = new VehicleModel();
        $personnelModel  = new PersonnelModel();

        return $this->ok([
            'total_tools'      => $toolsModel->where('is_archived', 0)->countAllResults(),
            'total_vehicles'   => $vehicleModel->where('is_archived', 0)->countAllResults(),
            'total_personnel'  => $personnelModel->countAllResults(),
            'active_vehicles'  => $vehicleModel->where('availability', 'In Use')->where('is_archived', 0)->findAll(),
        ]);
    }
}
