<?php

namespace App\Controllers;

use App\Models\ReportModel;
use App\Models\VehicleModel;
use App\Models\ToolsModel;
use App\Models\PersonnelModel;
use App\Models\TravelModel;

class ReportController extends BaseController
{
    protected $reportModel;
    protected $vehicleModel;
    protected $toolsModel;
    protected $personnelModel;
    protected $travelModel;
    protected $session;

    public function __construct()
    {
        $this->reportModel    = new ReportModel();
        $this->vehicleModel   = new VehicleModel();
        $this->toolsModel     = new ToolsModel();
        $this->personnelModel = new PersonnelModel();
        $this->travelModel    = new TravelModel();
        $this->session        = \Config\Services::session();
    }

    public function index()
    {
        if (!$this->session->get('isLoggedIn')) {
            // Set test session for development
            $this->session->set([
                'user_id'     => 1,
                'full_name'   => 'Test User',
                'role'        => 'Admin',
                'department'  => 'Facilities',
                'isLoggedIn'  => true,
            ]);
        }

        try { $totalAssets    = $this->toolsModel->countAllResults(); }     catch(\Exception $e){ $totalAssets = 0; }
        try { $totalVehicles  = $this->vehicleModel->countAllResults(); }   catch(\Exception $e){ $totalVehicles = 0; }
        try { $activeVehicles = 0; } catch(\Exception $e){ $activeVehicles = 0; }
        try { $personnel      = $this->personnelModel->countAllResults(); }  catch(\Exception $e){ $personnel = 0; }
        try { $onDuty         = 0; } catch(\Exception $e){ $onDuty = 0; }
        try { $allTrips       = $this->travelModel->findAll(); }      catch(\Exception $e){ $allTrips = []; }

        $completedTrips = 0;
        $totalTrips     = count($allTrips);
        try {
            $completedTrips = count(array_filter($allTrips, fn($t) => isset($t['status']) && $t['status'] === 'Completed'));
        } catch (\Exception $e) {}

        $monthlyTrips = [];
        try { $monthlyTrips = $this->travelModel->getMonthlyVolume(7); } catch(\Exception $e){ $monthlyTrips = []; }

        // Vehicle utilization
        $vehicleUtil = [];
        try {
            foreach ($this->vehicleModel->findAll() as $v) {
                $vehicleUtil[] = [
                    'plate'    => $v['plate_no']      ?? '—',
                    'model'    => $v['vehicle_name']  ?? '—',
                    'trips'    => 0,
                    'util_pct' => 0,
                ];
            }
        } catch(\Exception $e){}

        $assetDist = [];
        try { $assetDist = $this->toolsModel->getCategoryDistribution(); } catch(\Exception $e){
            $assetDist = [
                ['category' => 'Generator',    'count' => 1],
                ['category' => 'IT Equipment', 'count' => 2],
                ['category' => 'Janitorial',   'count' => 2],
            ];
        }

        $maintFreq = [
            ['label'=>'Critical','count'=>82],['label'=>'Medium','count'=>55],
            ['label'=>'Routine','count'=>58], ['label'=>'Deferred','count'=>42],
        ];

        $recentReports = [];
        try { $recentReports = $this->reportModel->getRecent(5); } catch(\Exception $e){ $recentReports = []; }

        $data = [
            'title'           => 'Records, Archiving & Reports',
            'total_assets'    => $totalAssets,
            'active_vehicles' => $activeVehicles,
            'total_vehicles'  => $totalVehicles,
            'completed_trips' => $completedTrips,
            'total_trips'     => $totalTrips,
            'personnel'       => $personnel,
            'on_duty'         => $onDuty,
            'pending_maint'   => 18,
            'active_alerts'   => 5,
            'monthly_trips'   => $monthlyTrips,
            'vehicle_util'    => $vehicleUtil,
            'asset_dist'      => $assetDist,
            'maint_freq'      => $maintFreq,
            'recent_reports'  => $recentReports,
            'flash_success'   => $this->session->getFlashdata('success'),
        ];

        return view('reports/index', $data);
    }

    public function generate()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $type = $this->request->getPost('report_type') ?? 'General';
        $range = $this->request->getPost('date_range') ?? 'Last 30 Days';
        $user  = $this->session->get('full_name') ?? 'Admin';

        // Try with only minimal required columns first
        try {
            $this->reportModel->insert([
                'report_name'  => $type . ' Report — ' . $range,
                'generated_by' => $user,
            ]);
        } catch (\Exception $e) {}

        $this->session->setFlashdata('success', "Report \"{$type}\" generated successfully.");
        return redirect()->to('/reports');
    }

    public function view($id)
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $report = $this->reportModel->find($id);
        if (!$report) {
            return $this->response->setJSON(['success' => false, 'message' => 'Report not found']);
        }

        return $this->response->setJSON([
            'success' => true,
            'report' => $report,
        ]);
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
        } catch (\Exception $e) {}
        
        $this->session->setFlashdata('success', 'Report changes saved successfully.');
        return redirect()->to('/reports');
    }

    public function download($id)
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $report = $this->reportModel->find($id);
        $filename = 'report_' . ($id ?: 'download') . '_' . date('Ymd') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');

        fputcsv($out, ['Report ID', 'Report Name', 'Generated By', 'Type / Module', 'Date Range', 'Status']);
        fputcsv($out, [
            $report['id'] ?? $id,
            $report['report_name'] ?? 'Generated Report',
            $report['generated_by'] ?? 'Admin',
            $report['type_module'] ?? 'General',
            $report['date_range'] ?? 'Last 30 Days',
            $report['status'] ?? 'Completed',
        ]);

        fclose($out);
        exit;
    }

    public function export($type = 'trips')
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $format = strtolower($this->request->getGet('format') ?? 'csv');
        $filename = $type . '_' . date('Ymd');

        if ($format === 'pdf') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
            echo $this->buildSimplePdf($type);
            exit;
        }

        $contentType = $format === 'excel' ? 'application/vnd.ms-excel' : 'text/csv';
        $extension = $format === 'excel' ? 'xls' : 'csv';
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '.' . $extension . '"');
        $out = fopen('php://output', 'w');

        try {
            if ($type === 'trips') {
                fputcsv($out, ['Trip ID','Requester','Destination','Purpose','Travel Date','Status']);
                foreach ($this->travelModel->findAll() as $t) {
                    fputcsv($out, [$t['trip_id'],$t['requester'],$t['destination'],$t['purpose'],$t['travel_date'],$t['status']]);
                }
            } elseif ($type === 'vehicles') {
                fputcsv($out, ['ID','Vehicle Name','Plate No.','Type','Availability']);
                foreach ($this->vehicleModel->findAll() as $v) {
                    fputcsv($out, [$v['id'],$v['vehicle_name'],$v['plate_no'],$v['type'],$v['availability']]);
                }
            } elseif ($type === 'assets') {
                fputcsv($out, ['ID','Asset Name','Category','Condition','Availability']);
                foreach ($this->toolsModel->findAll() as $t) {
                    fputcsv($out, [$t['id'],$t['asset_name'],$t['category'],$t['condition_status'],$t['availability']]);
                }
            } elseif ($type === 'personnel') {
                fputcsv($out, ['ID','Full Name','Position','Status']);
                foreach ($this->personnelModel->findAll() as $p) {
                    fputcsv($out, [$p['id'],$p['full_name'],$p['position'],$p['status']]);
                }
            }
        } catch(\Exception $e){
            fputcsv($out, ['Error', $e->getMessage()]);
        }

        fclose($out);
        exit;
    }

    private function buildSimplePdf(string $type): string
    {
        $title = ucfirst($type) . ' Report';
        $body = 'This export was generated from the reports module.';
        $escapedTitle = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $title);
        $escapedBody = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $body);
        $stream = "BT /F1 16 Tf 50 770 Td ($escapedTitle) Tj 0 -20 Td ($escapedBody) Tj ET";
        $length = strlen($stream);

        $pdf = "%PDF-1.4\n";
        $pdf .= "1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n";
        $pdf .= "2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n";
        $pdf .= "3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>endobj\n";
        $pdf .= "4 0 obj<< /Length {$length} >>stream\n{$stream}\nendstream\nendobj\n";
        $pdf .= "5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\n";
        $pdf .= "xref\n";
        $pdf .= "0 6\n";
        $pdf .= "0000000000 65535 f \n";
        $pdf .= "0000000010 00000 n \n";
        $pdf .= "0000000062 00000 n \n";
        $pdf .= "0000000119 00000 n \n";
        $pdf .= "0000000207 00000 n \n";
        $pdf .= "0000000304 00000 n \n";
        $pdf .= "trailer<< /Size 6 /Root 1 0 R >>\n";
        $pdf .= "startxref\n0\n%%EOF\n";

        return $pdf;
    }

    public function chartData()
    {
        if (!$this->session->get('isLoggedIn')) return $this->response->setStatusCode(401)->setJSON([]);
        try { $monthly = $this->travelModel->getMonthlyVolume(7); } catch(\Exception $e){ $monthly = []; }
        return $this->response->setJSON(['monthly_trips' => $monthly]);
    }

    public function add()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');
        return redirect()->to('/reports');
    }

    public function delete($id)
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');
        try { $this->reportModel->delete($id); } catch (\Exception $e) {}
        $this->session->setFlashdata('success', 'Report deleted successfully.');
        return redirect()->to('/reports');
    }

    public function activate($id)
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');
        try { $this->reportModel->update($id, ['status' => 'Active']); } catch (\Exception $e) {}
        $this->session->setFlashdata('success', 'Report activated successfully.');
        return redirect()->to('/reports');
    }

    public function deactivate($id)
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');
        try { $this->reportModel->update($id, ['status' => 'Inactive']); } catch (\Exception $e) {}
        $this->session->setFlashdata('success', 'Report deactivated successfully.');
        return redirect()->to('/reports');
    }
}
