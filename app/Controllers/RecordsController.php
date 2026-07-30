<?php

namespace App\Controllers;

use App\Models\BorrowModel;
use App\Models\TravelModel;
use App\Models\ReportModel;
use App\Models\DisposalLogModel;

class RecordsController extends BaseController
{
    protected $session;
    protected $borrowModel;
    protected $travelModel;
    protected $reportModel;
    protected $disposalLogModel;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->borrowModel = new BorrowModel();
        $this->travelModel = new TravelModel();
        $this->reportModel = new ReportModel();
        $this->disposalLogModel = new DisposalLogModel();
    }

    public function index()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Auto-flag records for archiving
        $this->borrowModel->autoFlagForArchiving();
        $this->travelModel->autoFlagForArchiving();
        $this->reportModel->autoFlagForArchiving();

        // Get all records (including archived — this page needs to show every state)
        $borrowRecords = $this->borrowModel->getAllWithDetailsForRecords();
        $travelRecords = $this->travelModel->getAllWithDetailsForRecords();
        $reportRecords = $this->reportModel->getAllWithDetailsForRecords();
        $disposalLogs  = $this->disposalLogModel->getAllWithDetails();

        // Reports aren't their own module — they're generated *about* one of the four
        // real operational modules, so map each report onto the module it reports on.
        $reportModuleMap = [
            'Asset Inventory'        => 'Tools',
            'Vehicle Fleet'          => 'Vehicle',
            'Travel Operations'      => 'Vehicle',
            'Maintenance Compliance' => 'Safety',
            'Facilities Management'  => 'Safety',
            'Janitorial Performance' => 'Janitorial',
        ];
        $disposalModuleMap = ['borrow' => 'Tools', 'travel' => 'Vehicle'];

        $activities = [];

        foreach ($borrowRecords as $r) {
            // Once disposed, the disposal_logs row below is the record of it — skip the
            // original so it doesn't also show up as a separate, still-active entry.
            if (($r['disposal_status'] ?? 'None') === 'Disposed') continue;

            $activities[] = [
                'type'        => 'borrow',
                'id'          => $r['id'],
                'date'        => $r['borrowed_date'] ?? $r['created_at'] ?? $r['last_activity_at'] ?? null,
                'module'      => 'Tools',
                'kind'        => !empty($r['is_archived']) ? 'Archive' : 'Record',
                'record'      => $r['asset_name'] ?? 'Tool',
                'record_sub'  => 'ID: ' . ($r['asset_code'] ?? ('TL-' . str_pad((string) $r['id'], 5, '0', STR_PAD_LEFT))),
                'action'      => $r['status'] ?? 'Borrowed',
                'performed_by'=> $r['borrower_name'] ?? $r['borrower'] ?? '—',
                'status'      => !empty($r['is_archived']) ? 'Archived' : ($r['status'] ?? '—'),
                'is_archived' => !empty($r['is_archived']),
                'disposal_status' => $r['disposal_status'] ?? 'None',
            ];
        }

        foreach ($travelRecords as $r) {
            if (($r['disposal_status'] ?? 'None') === 'Disposed') continue;

            $activities[] = [
                'type'        => 'travel',
                'id'          => $r['id'],
                'date'        => $r['travel_date'] ?? $r['last_activity_at'] ?? null,
                'module'      => 'Vehicle',
                'kind'        => !empty($r['is_archived']) ? 'Archive' : 'Record',
                'record'      => $r['destination'] ?? 'Trip',
                'record_sub'  => $r['trip_id'] ?? ('VH-' . str_pad((string) $r['id'], 5, '0', STR_PAD_LEFT)),
                'action'      => $r['status'] ?? 'Pending',
                'performed_by'=> $r['requester_name'] ?? '—',
                'status'      => !empty($r['is_archived']) ? 'Archived' : ($r['status'] ?? '—'),
                'is_archived' => !empty($r['is_archived']),
                'disposal_status' => $r['disposal_status'] ?? 'None',
            ];
        }

        foreach ($reportRecords as $r) {
            $activities[] = [
                'type'        => 'report',
                'id'          => $r['id'],
                'date'        => $r['created_at'] ?? $r['last_activity_at'] ?? null,
                'module'      => $reportModuleMap[$r['type_module'] ?? ''] ?? 'Safety',
                'kind'        => 'Report',
                'record'      => $r['report_name'] ?? 'Report',
                'record_sub'  => $r['type_module'] ?? 'General',
                'action'      => !empty($r['is_archived']) ? 'Archived' : 'Generated',
                'performed_by'=> $r['generated_by_name'] ?? '—',
                'status'      => !empty($r['is_archived']) ? 'Archived' : 'Generated',
                'is_archived' => !empty($r['is_archived']),
                'disposal_status' => $r['disposal_status'] ?? 'None',
            ];
        }

        foreach ($disposalLogs as $log) {
            $activities[] = [
                'type'        => 'disposal',
                'id'          => $log['id'],
                'date'        => $log['disposal_date'] ?? null,
                'module'      => $disposalModuleMap[$log['record_type'] ?? ''] ?? 'Tools',
                'kind'        => 'Archive',
                'record'      => ucfirst($log['record_type'] ?? 'Record') . ' Record',
                'record_sub'  => '#' . ($log['record_id'] ?? '—'),
                'action'      => 'Disposed',
                'performed_by'=> $log['authorized_by_name'] ?? '—',
                'status'      => 'Disposed',
                'is_archived' => true,
                'disposal_status' => 'Disposed',
            ];
        }

        usort($activities, function ($a, $b) {
            return (strtotime($b['date'] ?? '') ?: 0) <=> (strtotime($a['date'] ?? '') ?: 0);
        });

        $today = date('Y-m-d');
        $archivableSets = [$borrowRecords, $travelRecords, $reportRecords];
        $archivedCount = 0;
        foreach ($archivableSets as $set) {
            foreach ($set as $r) {
                if (!empty($r['is_archived'])) {
                    $archivedCount++;
                }
            }
        }

        $stats = [
            'total_records'     => count($borrowRecords) + count($travelRecords) + count($reportRecords),
            'archived_records'  => $archivedCount,
            'reports_generated' => count($reportRecords),
            'today_activities'  => count(array_filter($activities, fn($a) => ($a['date'] ?? '') !== null && date('Y-m-d', strtotime($a['date'])) === $today)),
        ];

        return view('records/index', [
            'title'         => 'Records, Archiving & Reports',
            'pageCss'       => 'records.css',
            'stats'         => $stats,
            'activities'    => $activities,
            'reportRecords' => $reportRecords,
            'flash_success' => session()->getFlashdata('success'),
            'flash_error'   => session()->getFlashdata('error'),
        ]);
    }

    // Mark record as "For Disposal"
    public function markForDisposal($type, $id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }
        if ($resp = $this->requireAdmin()) return $resp;

        $model = $this->getModelByType($type);
        if (!$model) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid record type']);
        }

        $model->update($id, ['disposal_status' => 'For Disposal']);
        
        // Update last_activity_at
        $model->update($id, ['last_activity_at' => date('Y-m-d H:i:s')]);

        return redirect()->to('/records')->with('success', 'Record marked for disposal');
    }

    // Authorize disposal (B&G Head only) with digital signature
    public function authorizeDisposal()
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }
        if ($resp = $this->requireAdmin()) return $resp;

        $type = $this->request->getPost('type');
        $id = $this->request->getPost('id');
        $signature = $this->request->getPost('signature');
        $disposalDate = $this->request->getPost('disposal_date');
        $notes = $this->request->getPost('notes');

        $model = $this->getModelByType($type);
        if (!$model) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid record type']);
        }

        $userId = $this->session->get('user_id');

        // Update record
        $model->update($id, [
            'disposal_status' => 'Disposed',
            'disposal_date' => $disposalDate,
            'disposal_authorized_by' => $userId,
            'disposal_signature' => $signature,
            'last_activity_at' => date('Y-m-d H:i:s')
        ]);

        // Log disposal
        $this->disposalLogModel->insert([
            'record_type' => $type,
            'record_id' => $id,
            'authorized_by_id' => $userId,
            'disposal_date' => $disposalDate,
            'signature' => $signature,
            'remarks' => $notes
        ]);

        return redirect()->to('/records')->with('success', 'Disposal authorized and logged');
    }

    // Export archiving report
    public function exportReport($format = 'csv')
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'borrow' => $this->borrowModel->findAll(),
            'travel' => $this->travelModel->findAll(),
            'report' => $this->reportModel->findAll(),
            'disposal' => $this->disposalLogModel->getAllWithDetails(),
        ];

        if ($format === 'csv' || $format === 'excel') {
            return $this->exportCSV($data, $format === 'excel');
        }

        if ($format === 'pdf') {
            return $this->exportPDF($data);
        }

        return redirect()->to('/records')->with('error', 'Unsupported format');
    }

    private function exportPDF($data)
    {
        $lines = [
            'FU-UBRA Records, Archiving and Reports Summary',
            'Generated: ' . date('M j, Y g:i A'),
            '',
            'Total operational records: ' . (count($data['borrow']) + count($data['travel']) + count($data['report'])),
            'Tool borrow logs: ' . count($data['borrow']),
            'Trip tickets: ' . count($data['travel']),
            'Reports generated: ' . count($data['report']),
            'Disposal actions logged: ' . count($data['disposal']),
        ];

        $stream = 'BT /F1 14 Tf 50 770 Td ';
        foreach ($lines as $i => $line) {
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $stream .= ($i === 0 ? '' : '0 -20 Td ') . "($escaped) Tj ";
        }
        $stream .= 'ET';
        $length = strlen($stream);

        $pdf  = "%PDF-1.4\n";
        $pdf .= "1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n";
        $pdf .= "2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n";
        $pdf .= "3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>endobj\n";
        $pdf .= "4 0 obj<< /Length {$length} >>stream\n{$stream}\nendstream\nendobj\n";
        $pdf .= "5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\n";
        $pdf .= "trailer<< /Size 6 /Root 1 0 R >>\n";
        $pdf .= "%%EOF\n";

        $filename = 'fu-ubra-records-summary-' . date('Y-m-d') . '.pdf';
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($pdf);
    }

    private function exportCSV($data, $asExcel = false)
    {
        helper('filesystem');

        $extension = $asExcel ? 'xls' : 'csv';
        $filename = 'fu-ubra-archiving-report-' . date('Y-m-d') . '.' . $extension;
        $tempPath = WRITEPATH . 'uploads/' . $filename;

        $file = fopen($tempPath, 'w');

        // Write headers
        fputcsv($file, ['Record Type', 'ID', 'Status', 'Archived At', 'Disposal Status', 'Disposal Date']);
        
        // Write borrow records
        foreach ($data['borrow'] as $row) {
            fputcsv($file, [
                'Borrowing Log',
                $row['id'],
                $row['status'] ?? '',
                $row['archived_at'] ?? '',
                $row['disposal_status'] ?? '',
                $row['disposal_date'] ?? ''
            ]);
        }
        
        // Write travel records
        foreach ($data['travel'] as $row) {
            fputcsv($file, [
                'Trip Ticket',
                $row['id'],
                $row['status'] ?? '',
                $row['archived_at'] ?? '',
                $row['disposal_status'] ?? '',
                $row['disposal_date'] ?? ''
            ]);
        }
        
        // Write report records
        foreach ($data['report'] as $row) {
            fputcsv($file, [
                'Maintenance Report',
                $row['id'],
                $row['status'] ?? '',
                $row['archived_at'] ?? '',
                $row['disposal_status'] ?? '',
                $row['disposal_date'] ?? ''
            ]);
        }
        
        // Write disposal logs
        fputcsv($file, []);
        fputcsv($file, ['Disposal Logs']);
        fputcsv($file, ['Record Type', 'Record ID', 'Authorized By', 'Disposal Date']);
        foreach ($data['disposal'] as $row) {
            fputcsv($file, [
                $row['record_type'],
                $row['record_id'],
                $row['authorized_by_name'] ?? $row['authorized_by_id'] ?? '',
                $row['disposal_date'] ?? ''
            ]);
        }
        
        fclose($file);
        
        return $this->response->download($tempPath, null)->setFileName($filename);
    }

    private function getModelByType($type)
    {
        switch ($type) {
            case 'borrow':
                return $this->borrowModel;
            case 'travel':
                return $this->travelModel;
            case 'report':
                return $this->reportModel;
            default:
                return null;
        }
    }
}
