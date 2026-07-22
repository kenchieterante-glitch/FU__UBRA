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
            return redirect()->to('/auth/login');
        }

        // Auto-flag records for archiving
        $this->borrowModel->autoFlagForArchiving();
        $this->travelModel->autoFlagForArchiving();
        $this->reportModel->autoFlagForArchiving();

        // Get all records
        $borrowRecords = $this->borrowModel->orderBy('id', 'DESC')->findAll();
        $travelRecords = $this->travelModel->orderBy('id', 'DESC')->findAll();
        $reportRecords = $this->reportModel->orderBy('id', 'DESC')->findAll();
        $disposalLogs = $this->disposalLogModel->orderBy('id', 'DESC')->findAll();

        return view('records/index', [
            'title' => 'Records, Archiving & Disposal',
            'borrowRecords' => $borrowRecords,
            'travelRecords' => $travelRecords,
            'reportRecords' => $reportRecords,
            'disposalLogs' => $disposalLogs
        ]);
    }

    // Mark record as "For Disposal"
    public function markForDisposal($type, $id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

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
            'authorized_by' => $userId,
            'authorized_at' => date('Y-m-d H:i:s'),
            'disposal_date' => $disposalDate,
            'signature' => $signature,
            'notes' => $notes
        ]);

        return redirect()->to('/records')->with('success', 'Disposal authorized and logged');
    }

    // Export archiving report
    public function exportReport($format = 'csv')
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'borrow' => $this->borrowModel->findAll(),
            'travel' => $this->travelModel->findAll(),
            'report' => $this->reportModel->findAll(),
            'disposal' => $this->disposalLogModel->findAll()
        ];

        if ($format === 'csv') {
            return $this->exportCSV($data);
        }

        return redirect()->to('/records')->with('error', 'Unsupported format');
    }

    private function exportCSV($data)
    {
        helper('filesystem');
        
        $filename = 'fu-ubra-archiving-report-' . date('Y-m-d') . '.csv';
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
        fputcsv($file, ['Record Type', 'Record ID', 'Authorized By', 'Authorized At', 'Disposal Date']);
        foreach ($data['disposal'] as $row) {
            fputcsv($file, [
                $row['record_type'],
                $row['record_id'],
                $row['authorized_by'],
                $row['authorized_at'],
                $row['disposal_date']
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
