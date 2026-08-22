<?php

namespace App\Controllers;

use App\Models\PersonnelModel;
use App\Models\ToolsModel;

class FacilitiesDashboardController extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $personnelModel = new PersonnelModel();
        $toolsModel     = new ToolsModel();

        $totalPersonnel = $personnelModel->where('is_archived', 0)->countAllResults();
        $activePersonnel = $personnelModel->where('is_archived', 0)->where('status', 'Active')->countAllResults();
        $onLeavePersonnel = $personnelModel->where('is_archived', 0)->where('status', 'On Leave')->countAllResults();

        $maintenanceTools = $toolsModel->where('availability', 'Maintenance')->where('is_archived', 0)->countAllResults();
        $disposalTools    = $toolsModel->where('availability', 'Disposal')->where('is_archived', 0)->countAllResults();

        $hour = (int) date('H');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

        return view('facilities_dashboard/index', [
            'title'              => 'Facilities Dashboard',
            'pageCss'            => 'tools.css',
            'full_name'          => session()->get('full_name'),
            'greeting'           => $greeting,
            'last_updated'       => date('l, F j, Y — g:i A'),
            'total_personnel'    => $totalPersonnel,
            'active_personnel'   => $activePersonnel,
            'on_leave_personnel' => $onLeavePersonnel,
            'total_tools'        => $toolsModel->where('is_archived', 0)->countAllResults(),
            'available_tools'    => $toolsModel->where('availability', 'Available')->where('is_archived', 0)->countAllResults(),
            'borrowed_tools'     => $toolsModel->where('availability', 'Borrowed')->where('is_archived', 0)->countAllResults(),
            'maintenance_tools'  => $maintenanceTools,
            'disposal_tools'     => $disposalTools,
        ]);
    }
}
