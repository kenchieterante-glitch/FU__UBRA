<?php

namespace App\Controllers;

use App\Models\ReportModel;
use App\Models\TravelModel;

// Kept only for the two endpoints still wired to the live Records, Archiving &
// Reports page (records/index.php via RecordsController): editing a report's
// metadata, and the monthly-trips chart data used by Mr. UBRA AI. Everything
// else here used to back a second, unreachable "reports/index" view — no route
// pointed to ReportController::index(), records/index.php is the real page —
// so that dead surface (index/generate/add/view/download/delete/activate/
// deactivate/export + the PDF builder) was removed rather than left to rot.
class ReportController extends BaseController
{
    protected $reportModel;
    protected $travelModel;
    protected $session;

    public function __construct()
    {
        $this->reportModel = new ReportModel();
        $this->travelModel = new TravelModel();
        $this->session     = \Config\Services::session();
    }

    public function update()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $id = (int) $this->request->getPost('report_id');
        if (!$id) {
            return redirect()->to('/reports')->withInput();
        }

        try {
            $this->reportModel->update($id, [
                'report_name'  => $this->request->getPost('report_name'),
                'generated_by' => $this->request->getPost('generated_by'),
            ]);
        } catch (\Exception $e) {
            log_message('error', 'ReportController::update failed for id ' . $id . ': ' . $e->getMessage());
            return redirect()->to('/reports')->withInput()->with('error', 'Could not save report changes.');
        }

        $this->session->setFlashdata('success', 'Report changes saved successfully.');
        return redirect()->to('/reports');
    }

    public function chartData()
    {
        if (!$this->session->get('isLoggedIn')) return $this->response->setStatusCode(401)->setJSON([]);
        try {
            $monthly = $this->travelModel->getMonthlyVolume(7);
        } catch (\Exception $e) {
            log_message('error', 'ReportController::chartData failed: ' . $e->getMessage());
            $monthly = [];
        }
        return $this->response->setJSON(['monthly_trips' => $monthly]);
    }
}
