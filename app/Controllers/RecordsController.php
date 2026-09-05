<?php

namespace App\Controllers;

use App\Models\BorrowModel;
use App\Models\ReportModel;
use App\Models\DisposalLogModel;
use App\Models\ToolsModel;
use App\Models\VehicleModel;
use App\Models\PersonnelModel;

class RecordsController extends BaseController
{
    protected $session;
    protected $borrowModel;
    protected $reportModel;
    protected $disposalLogModel;
    protected $toolsModel;
    protected $vehicleModel;
    protected $personnelModel;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->borrowModel = new BorrowModel();
        $this->reportModel = new ReportModel();
        $this->disposalLogModel = new DisposalLogModel();
        $this->toolsModel = new ToolsModel();
        $this->vehicleModel = new VehicleModel();
        $this->personnelModel = new PersonnelModel();
    }

    public function index()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Auto-flag records for archiving
        $this->borrowModel->autoFlagForArchiving();
        $this->reportModel->autoFlagForArchiving();

        $built = $this->buildActivities();
        $activities    = $built['activities'];
        $borrowRecords = $built['borrowRecords'];
        $reportRecords = $built['reportRecords'];

        $today = date('Y-m-d');
        $archivableSets = [$borrowRecords, $reportRecords];
        $archivedCount = 0;
        foreach ($archivableSets as $set) {
            foreach ($set as $r) {
                if (!empty($r['is_archived'])) {
                    $archivedCount++;
                }
            }
        }
        $archivedCount += count($built['archivedTools']) + count($built['archivedVehicles']) + count($built['archivedPersonnel']);

        $stats = [
            'total_records'     => count($borrowRecords) + count($reportRecords),
            'archived_records'  => $archivedCount,
            'reports_generated' => count($reportRecords),
            'today_activities'  => count(array_filter($activities, fn($a) => ($a['date'] ?? '') !== null && date('Y-m-d', strtotime($a['date'])) === $today)),
        ];

        return view('records/index', [
            'title'         => 'Information Hub',
            'pageCss'       => 'records.css',
            'stats'         => $stats,
            'activities'    => $activities,
            'reportRecords' => $reportRecords,
            'flash_success' => session()->getFlashdata('success'),
            'flash_error'   => session()->getFlashdata('error'),
        ]);
    }

    // Builds the exact same unified activity rows the Information Hub table
    // renders (borrow/report/disposal/archived-tool/archived-vehicle/
    // archived-personnel, merged and sorted by date) — shared by index() for
    // display and exportReport() for export, so what's on screen (once
    // filtered) is always what comes out in the exported file.
    private function buildActivities(): array
    {
        $borrowRecords = $this->borrowModel->getAllWithDetailsForRecords();
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

        // Archived Tools, Vehicles, and Personnel — the catalog tables have their
        // own "Archive" icon buttons (Tools Management, Vehicle Management,
        // Personnel Management); this is what makes those actions show up here.
        $archivedTools = $this->toolsModel->where('is_archived', 1)->orderBy('archived_at', 'DESC')->findAll();
        foreach ($archivedTools as $t) {
            $activities[] = [
                'type'        => 'tool',
                'id'          => $t['id'],
                'date'        => $t['archived_at'] ?? $t['last_activity_at'] ?? null,
                'module'      => 'Tools',
                'kind'        => 'Archive',
                'record'      => $t['asset_name'] ?? 'Tool',
                'record_sub'  => 'Code: ' . ($t['asset_code'] ?: '—'),
                'action'      => 'Archived',
                'performed_by'=> $t['custodian'] ?: '—',
                'status'      => 'Archived',
                'is_archived' => true,
                'disposal_status' => 'None',
            ];
        }

        $archivedVehicles = $this->vehicleModel->where('is_archived', 1)->orderBy('archived_at', 'DESC')->findAll();
        foreach ($archivedVehicles as $v) {
            $activities[] = [
                'type'        => 'vehicle',
                'id'          => $v['id'],
                'date'        => $v['archived_at'] ?? $v['last_activity_at'] ?? null,
                'module'      => 'Vehicle',
                'kind'        => 'Archive',
                'record'      => $v['vehicle_name'] ?? 'Vehicle',
                'record_sub'  => 'Plate: ' . ($v['plate_no'] ?: '—'),
                'action'      => 'Archived',
                'performed_by'=> '—',
                'status'      => 'Archived',
                'is_archived' => true,
                'disposal_status' => 'None',
            ];
        }

        $archivedPersonnel = $this->personnelModel->where('is_archived', 1)->orderBy('archived_at', 'DESC')->findAll();
        foreach ($archivedPersonnel as $p) {
            $activities[] = [
                'type'        => 'personnel',
                'id'          => $p['id'],
                'date'        => $p['archived_at'] ?? null,
                'module'      => 'Personnel',
                'kind'        => 'Archive',
                'record'      => $p['full_name'] ?? 'Personnel',
                'record_sub'  => $p['position'] ?: '—',
                'action'      => 'Archived',
                'performed_by'=> '—',
                'status'      => 'Archived',
                'is_archived' => true,
                'disposal_status' => 'None',
            ];
        }

        usort($activities, function ($a, $b) {
            return (strtotime($b['date'] ?? '') ?: 0) <=> (strtotime($a['date'] ?? '') ?: 0);
        });

        return [
            'activities'        => $activities,
            'borrowRecords'     => $borrowRecords,
            'reportRecords'     => $reportRecords,
            'archivedTools'     => $archivedTools,
            'archivedVehicles'  => $archivedVehicles,
            'archivedPersonnel' => $archivedPersonnel,
        ];
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

    // Export archiving report — filtered by the same module/kind/status/date/
    // search the on-screen table uses (records/index.php's filterTable()),
    // passed through as query params, so "export" means "export what I'm
    // actually looking at right now", not an unrelated fixed summary.
    public function exportReport($format = 'csv')
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $slug = fn($s) => strtolower(str_replace(' ', '-', trim((string) $s)));

        $module = strtolower(trim((string) ($this->request->getGet('module') ?? '')));
        $kind   = strtolower(trim((string) ($this->request->getGet('kind') ?? '')));
        $status = strtolower(trim((string) ($this->request->getGet('status') ?? '')));
        $date   = trim((string) ($this->request->getGet('date') ?? ''));
        $search = strtolower(trim((string) ($this->request->getGet('search') ?? '')));

        $activities = $this->buildActivities()['activities'];

        $filtered = array_values(array_filter($activities, function ($a) use ($slug, $module, $kind, $status, $date, $search) {
            $modSlug    = $slug($a['module']);
            $kindSlug   = $slug($a['kind']);
            $statusSlug = $slug($a['status']);
            $dateKey    = !empty($a['date']) ? date('Y-m-d', strtotime($a['date'])) : '';
            $searchBlob = strtolower($a['module'] . ' ' . $a['record'] . ' ' . $a['record_sub'] . ' ' . $a['performed_by'] . ' ' . $a['action'] . ' ' . $a['status']);

            return (!$module || $modSlug === $module)
                && (!$kind || $kindSlug === $kind)
                && (!$status || $statusSlug === $status)
                && (!$date || $dateKey === $date)
                && (!$search || str_contains($searchBlob, $search));
        }));

        $moduleLabel = $module !== '' ? ucfirst(str_replace('-', ' ', $module)) : 'All Modules';

        if ($format === 'csv' || $format === 'excel') {
            return $this->exportCSV($filtered, $moduleLabel, $format === 'excel');
        }

        if ($format === 'pdf') {
            return $this->exportPDF($filtered, $moduleLabel);
        }

        return redirect()->to('/records')->with('error', 'Unsupported format');
    }

    private function exportPDF(array $activities, string $moduleLabel)
    {
        $rows = '';
        foreach ($activities as $a) {
            $dateStr = !empty($a['date']) ? date('M j, Y', strtotime($a['date'])) : '—';
            $rows .= '<tr>'
                . '<td>' . esc($dateStr) . '</td>'
                . '<td>' . esc($a['module']) . '</td>'
                . '<td>' . esc($a['kind']) . '</td>'
                . '<td>' . esc($a['record']) . '<br><span class="sub">' . esc($a['record_sub']) . '</span></td>'
                . '<td>' . esc($a['performed_by']) . '</td>'
                . '<td>' . esc($a['status']) . '</td>'
                . '</tr>';
        }
        if ($rows === '') {
            $rows = '<tr><td colspan="6" style="text-align:center;color:#888;">No matching records.</td></tr>';
        }

        $html = '<html><head><style>
                body { font-family: Helvetica, Arial, sans-serif; color: #222; }
                h1 { font-size: 16px; margin-bottom: 2px; }
                .meta { color: #666; font-size: 11px; margin-bottom: 18px; }
                table { border-collapse: collapse; width: 100%; }
                th, td { border: 1px solid #ccc; padding: 6px 8px; font-size: 11px; text-align: left; vertical-align: top; }
                th { background: #800000; color: #fff; }
                .sub { color: #888; font-size: 9.5px; }
            </style></head><body>
                <h1>UBRA Information Hub — ' . esc($moduleLabel) . '</h1>
                <div class="meta">Generated: ' . esc(date('M j, Y g:i A')) . ' &middot; ' . count($activities) . ' record(s)</div>
                <table>
                    <thead><tr><th>Date</th><th>Module</th><th>Type</th><th>Record</th><th>Performed By</th><th>Status</th></tr></thead>
                    <tbody>' . $rows . '</tbody>
                </table>
            </body></html>';

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filenameModule = strtolower(str_replace(' ', '-', $moduleLabel));
        $filename = 'fu-ubra-' . $filenameModule . '-records-' . date('Y-m-d') . '.pdf';
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    private function exportCSV(array $activities, string $moduleLabel, bool $asExcel = false)
    {
        helper('filesystem');

        $extension = $asExcel ? 'xls' : 'csv';
        $filenameModule = strtolower(str_replace(' ', '-', $moduleLabel));
        $filename = 'fu-ubra-' . $filenameModule . '-records-' . date('Y-m-d') . '.' . $extension;
        $tempPath = WRITEPATH . 'uploads/' . $filename;

        $file = fopen($tempPath, 'w');
        fputcsv($file, ['Date', 'Module', 'Type', 'Record', 'Reference', 'Performed By', 'Status']);
        foreach ($activities as $a) {
            $dateStr = !empty($a['date']) ? date('Y-m-d', strtotime($a['date'])) : '';
            fputcsv($file, [$dateStr, $a['module'], $a['kind'], $a['record'], $a['record_sub'], $a['performed_by'], $a['status']]);
        }
        fclose($file);

        return $this->response->download($tempPath, null)->setFileName($filename);
    }

    private function getModelByType($type)
    {
        switch ($type) {
            case 'borrow':
                return $this->borrowModel;
            case 'report':
                return $this->reportModel;
            default:
                return null;
        }
    }
}
