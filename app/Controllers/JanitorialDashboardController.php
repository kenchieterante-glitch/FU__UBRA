<?php

namespace App\Controllers;

use App\Models\JanitorialAssignmentModel;
use App\Models\ConsumableInventoryModel;

// Landing page for the Janitorial Supervisor role — same pattern as
// SecurityDashboardController / ToolsDashboardController /
// FacilitiesDashboardController: a scoped-down summary dashboard for a role
// whose sidebar (see layouts/main.php) only exposes this dashboard, the
// Janitorial Monitoring page (campus map), and Calendar.
class JanitorialDashboardController extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $assignmentModel = new JanitorialAssignmentModel();
        $inventoryModel  = new ConsumableInventoryModel();

        // Same shared zone-completion rule used by JanitorialController's own
        // page and the main Dashboard's "Cleaning Completion" KPI — see
        // JanitorialAssignmentModel::getZoneCleanCounts() — so this card can
        // never disagree with either of those.
        $zoneCounts = $assignmentModel->getZoneCleanCounts();
        $activeShifts = count($assignmentModel->findAll());

        $inventory  = $inventoryModel->findAll();
        $lowStock   = count(array_filter($inventory, fn($i) => (float) $i['current_stock'] <= (float) $i['reorder_threshold'] && (float) $i['current_stock'] > 0));
        $outOfStock = count(array_filter($inventory, fn($i) => (float) $i['current_stock'] <= 0));

        $hour = (int) date('H');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

        return view('janitorial_dashboard/index', [
            'title'         => 'Janitorial Dashboard',
            'pageCss'       => 'safety.css',
            'full_name'     => session()->get('full_name'),
            'greeting'      => $greeting,
            'last_updated'  => date('l, F j, Y — g:i A'),
            'total_zones'   => $zoneCounts['total'],
            'cleaned_zones' => $zoneCounts['cleaned'],
            'pending_zones' => $zoneCounts['pending'],
            'active_shifts' => $activeShifts,
            'low_stock'     => $lowStock,
            'out_of_stock'  => $outOfStock,
        ]);
    }
}
