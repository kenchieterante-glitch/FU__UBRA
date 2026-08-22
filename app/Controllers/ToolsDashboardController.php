<?php

namespace App\Controllers;

use App\Models\ToolsModel;

class ToolsDashboardController extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $toolsModel = new ToolsModel();

        $maintenanceTools = $toolsModel->where('availability', 'Maintenance')->where('is_archived', 0)->countAllResults();
        $disposalTools    = $toolsModel->where('availability', 'Disposal')->where('is_archived', 0)->countAllResults();
        $lowStockItems    = $toolsModel->where('category', 'Consumable')
            ->where('is_archived', 0)
            ->where('current_stock <= reorder_threshold', null, false)
            ->countAllResults();

        $categoryDistribution = $toolsModel->getCategoryDistribution();

        $hour = (int) date('H');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

        return view('tools_dashboard/index', [
            'title'                 => 'Tools Dashboard',
            'pageCss'               => 'tools.css',
            'full_name'             => session()->get('full_name'),
            'greeting'              => $greeting,
            'last_updated'          => date('l, F j, Y — g:i A'),
            'total_tools'           => $toolsModel->where('is_archived', 0)->countAllResults(),
            'available_tools'       => $toolsModel->where('availability', 'Available')->where('is_archived', 0)->countAllResults(),
            'borrowed_tools'        => $toolsModel->where('availability', 'Borrowed')->where('is_archived', 0)->countAllResults(),
            'maintenance_tools'     => $maintenanceTools,
            'disposal_tools'        => $disposalTools,
            'low_stock_items'       => $lowStockItems,
            'category_distribution' => $categoryDistribution,
        ]);
    }
}
