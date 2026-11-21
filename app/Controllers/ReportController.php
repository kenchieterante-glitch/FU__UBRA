<?php

namespace App\Controllers;

use App\Models\ReportModel;

// Kept only for the two endpoints still wired to the live Records, Archiving &
// Reports page (records/index.php via RecordsController): editing a report's
// metadata, and the chart data endpoint used by Mr. UBRA AI. Everything else
// here used to back a second, unreachable "reports/index" view — no route
// pointed to ReportController::index(), records/index.php is the real page —
// so that dead surface (index/generate/add/view/download/delete/activate/
// deactivate/export + the PDF builder) was removed rather than left to rot.
class ReportController extends BaseController
{
    protected $reportModel;
    protected $session;

    public function __construct()
    {
        $this->reportModel = new ReportModel();
        $this->session     = \Config\Services::session();
    }

    private function statusUpdate(): array
    {
        $status = (string) $this->request->getPost('status');

        return in_array($status, ['Pending', 'Completed'], true) ? ['status' => $status, 'last_activity_at' => date('Y-m-d H:i:s')] : [];
    }

    // Any signed-in user can file a report from the web; it starts as Pending and is tracked in the Information Hub.
    public function submit()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $name     = trim((string) $this->request->getPost('report_name'));
        $category = trim((string) $this->request->getPost('category'));
        $details  = trim((string) $this->request->getPost('details'));
        if ($name === '' || $category === '' || $details === '') {
            return redirect()->to('/records')->with('error', 'Title, category and details are required.');
        }

        $this->reportModel->insert([
            'report_name'      => $name,
            'type_module'      => $category,
            'details'          => $details,
            'submitted_by'     => (string) $this->session->get('full_name'),
            'status'           => 'Pending',
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);
        $this->logActivity('Report', "Submitted report {$name} ({$category})");

        return redirect()->to('/records')->with('success', 'Your report was submitted. You can track its status in the Information Hub.');
    }

    public function update()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $id = (int) $this->request->getPost('report_id');
        if (!$id) {
            return redirect()->to('/reports')->withInput();
        }

        $name = $this->request->getPost('report_name');
        try {
            $this->reportModel->update($id, [
                'report_name'  => $name,
            ] + $this->statusUpdate());
        } catch (\Exception $e) {
            log_message('error', 'ReportController::update failed for id ' . $id . ': ' . $e->getMessage());
            return redirect()->to('/reports')->withInput()->with('error', 'Could not save report changes.');
        }
        $this->logActivity('Safety', "Edited report {$name} (#{$id})");

        $this->session->setFlashdata('success', 'Report changes saved successfully.');
        return redirect()->to('/reports');
    }

    public function chartData()
    {
        if (!$this->session->get('isLoggedIn')) return $this->response->setStatusCode(401)->setJSON([]);
        return $this->response->setJSON([]);
    }
}
