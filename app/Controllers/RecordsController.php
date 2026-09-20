<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
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

        // The Janitorial Supervisor's Information Hub is scoped to
        // Janitorial-module activity only — same restriction as their
        // sidebar (own dashboard, the campus map, Calendar) rather than the
        // full cross-system audit trail every other role sees. Stats are
        // recomputed from the already-filtered list rather than the raw
        // borrow/report sources, since those aren't Janitorial-specific.
        $scopedToJanitorial = strtolower((string) $this->session->get('role')) === 'janitorial';
        if ($scopedToJanitorial) {
            $activities = array_values(array_filter($activities, fn($a) => $a['module'] === 'Janitorial'));

            $today = date('Y-m-d');
            $stats = [
                'total_records'     => count($activities),
                'archived_records'  => count(array_filter($activities, fn($a) => !empty($a['is_archived']))),
                'reports_generated' => count(array_filter($activities, fn($a) => $a['kind'] === 'Report')),
                'today_activities'  => count(array_filter($activities, fn($a) => ($a['date'] ?? null) !== null && date('Y-m-d', strtotime($a['date'])) === $today)),
            ];
        } else {
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
        }

        return view('records/index', [
            'title'               => 'Information Hub',
            'pageCss'             => 'records.css',
            'stats'               => $stats,
            'activities'          => $activities,
            'reportRecords'       => $reportRecords,
            'scopedToJanitorial'  => $scopedToJanitorial,
            'flash_success'       => session()->getFlashdata('success'),
            'flash_error'         => session()->getFlashdata('error'),
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

        // General audit trail (Vehicle/Tools/Personnel/Safety/Janitorial
        // edits — anything logged via BaseController::logActivity(), which
        // now every module's create/update/delete actions call) — folded in
        // here so Information Hub reflects real changes across the whole
        // system, not just borrow/report/disposal/archive events.
        $moduleLabelMap = [
            'Personnel Monitoring' => 'Personnel',
        ];
        foreach ((new ActivityLogModel())->orderBy('id', 'DESC')->findAll() as $log) {
            $raw = (string) ($log['action'] ?? '');
            $performedBy = '—';
            $description = $raw;
            // logActivity() stores "Actor Name: description" — split that
            // back apart for display; falls back to the raw text untouched
            // if a row predates that convention or has no actor on record.
            if (preg_match('/^([^:]{1,100}):\s(.+)$/s', $raw, $m)) {
                $performedBy = $m[1];
                $description = $m[2];
            }
            $moduleName = $moduleLabelMap[$log['module']] ?? $log['module'];

            $activities[] = [
                'type'        => 'activity',
                'id'          => $log['id'],
                'date'        => $log['logged_at'] ?? null,
                'module'      => $moduleName,
                'kind'        => 'Record',
                'record'      => $description,
                'record_sub'  => $log['module'],
                'action'      => 'Updated',
                'performed_by'=> $performedBy,
                'status'      => 'Logged',
                'is_archived' => false,
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

        $module   = strtolower(trim((string) ($this->request->getGet('module') ?? '')));
        $kind     = strtolower(trim((string) ($this->request->getGet('kind') ?? '')));
        $status   = strtolower(trim((string) ($this->request->getGet('status') ?? '')));
        $date     = trim((string) ($this->request->getGet('date') ?? ''));
        // A range (date_from/date_to) is optional and independent of the
        // single-day $date filter above — used by "Generate Weekly Summary"
        // and anything else that needs a span rather than one exact day.
        $dateFrom = trim((string) ($this->request->getGet('date_from') ?? ''));
        $dateTo   = trim((string) ($this->request->getGet('date_to') ?? ''));
        $search   = strtolower(trim((string) ($this->request->getGet('search') ?? '')));

        $activities = $this->buildActivities()['activities'];

        // Same Janitorial-only scoping as index() — a restricted role
        // shouldn't be able to export other modules' records just by
        // editing the query string.
        if (strtolower((string) $this->session->get('role')) === 'janitorial') {
            $activities = array_values(array_filter($activities, fn($a) => $a['module'] === 'Janitorial'));
        }

        $filtered = array_values(array_filter($activities, function ($a) use ($slug, $module, $kind, $status, $date, $dateFrom, $dateTo, $search) {
            $modSlug    = $slug($a['module']);
            $kindSlug   = $slug($a['kind']);
            $statusSlug = $slug($a['status']);
            $dateKey    = !empty($a['date']) ? date('Y-m-d', strtotime($a['date'])) : '';
            $searchBlob = strtolower($a['module'] . ' ' . $a['record'] . ' ' . $a['record_sub'] . ' ' . $a['performed_by'] . ' ' . $a['action'] . ' ' . $a['status']);

            $inRange = true;
            if ($dateFrom !== '' && $dateKey !== '') $inRange = $inRange && $dateKey >= $dateFrom;
            if ($dateTo !== ''   && $dateKey !== '') $inRange = $inRange && $dateKey <= $dateTo;
            if (($dateFrom !== '' || $dateTo !== '') && $dateKey === '') $inRange = false;

            return (!$module || $modSlug === $module)
                && (!$kind || $kindSlug === $kind)
                && (!$status || $statusSlug === $status)
                && (!$date || $dateKey === $date)
                && $inRange
                && (!$search || str_contains($searchBlob, $search));
        }));

        $moduleLabel = $module !== '' ? ucfirst(str_replace('-', ' ', $module)) : 'All Modules';
        if ($dateFrom !== '' || $dateTo !== '') {
            $moduleLabel .= ' (' . ($dateFrom ?: 'earliest') . ' to ' . ($dateTo ?: 'latest') . ')';
        }

        if ($format === 'csv' || $format === 'excel') {
            return $this->exportCSV($filtered, $moduleLabel, $format === 'excel');
        }

        if ($format === 'pdf') {
            return $this->exportPDF($filtered, $moduleLabel);
        }

        return redirect()->to('/records')->with('error', 'Unsupported format');
    }

    // Same logo the sidebar (layouts/main.php) resolves — embedded as a
    // base64 data URI so it renders inside Dompdf (and the HTML-based
    // "Excel" export) without depending on a remote/local file fetch at
    // render time. Returns '' if no logo file is found.
    private function logoDataUri(): string
    {
        $candidates = [
            FCPATH . 'images/UBRA LOGO (no background).png',
            FCPATH . 'uploads/logo.png',
            FCPATH . 'uploads/logo.jpg',
            FCPATH . 'uploads/logo.jpeg',
            FCPATH . 'Assets/images/logo.png',
            FCPATH . 'Assets/images/logo.jpg',
        ];
        foreach ($candidates as $path) {
            if (is_file($path)) {
                $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    default       => 'image/png',
                };
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }
        }
        return '';
    }

    // Shared centered header — system name, module name, and logo when one
    // exists — used by both the PDF and the HTML-based Excel export so the
    // two formats look consistent.
    private function docHeaderHtml(string $moduleLabel, int $count): string
    {
        $logo = $this->logoDataUri();
        $logoHtml = $logo !== '' ? '<img src="' . $logo . '" class="doc-logo">' : '';

        return '<div class="doc-header">'
            . $logoHtml
            . '<div class="doc-system-name">FOUNDATION UNIVERSITY — UBRA</div>'
            . '<div class="doc-module-name">' . esc($moduleLabel) . '</div>'
            . '<div class="meta">Generated: ' . esc(date('M j, Y g:i A')) . ' &middot; ' . $count . ' record(s)</div>'
            . '</div>';
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
                .doc-header { text-align: center; margin-bottom: 16px; }
                .doc-logo { display: block; margin: 0 auto 8px; height: 60px; }
                .doc-system-name { font-size: 15px; font-weight: bold; letter-spacing: .04em; color: #800000; }
                .doc-module-name { font-size: 13px; font-weight: bold; margin-top: 2px; }
                .meta { color: #666; font-size: 11px; margin-top: 6px; }
                table { border-collapse: collapse; width: 100%; }
                th, td { border: 1px solid #ccc; padding: 6px 8px; font-size: 11px; text-align: left; vertical-align: top; }
                th { background: #800000; color: #fff; }
                .sub { color: #888; font-size: 9.5px; }
            </style></head><body>
                ' . $this->docHeaderHtml($moduleLabel, count($activities)) . '
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
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setBody($dompdf->output());
    }

    private function exportCSV(array $activities, string $moduleLabel, bool $asExcel = false)
    {
        $filenameModule = strtolower(str_replace(' ', '-', $moduleLabel));

        // Plain CSV is genuinely just text — there's no such thing as a
        // logo or centered heading inside it, so it stays a real,
        // unformatted CSV exactly as before.
        if (!$asExcel) {
            helper('filesystem');
            $filename = 'fu-ubra-' . $filenameModule . '-records-' . date('Y-m-d') . '.csv';
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

        // A real .xlsx — a plain HTML table saved as .xls could carry the
        // logo in a browser-based "export", but Excel's own HTML-import
        // path does not render base64-embedded <img> sources at all (it
        // just leaves a broken-image placeholder, as seen), so a genuine
        // spreadsheet with a real embedded image is used instead.
        return $this->exportXLSX($activities, $moduleLabel);
    }

    private function exportXLSX(array $activities, string $moduleLabel)
    {
        $columns = ['Date', 'Module', 'Type', 'Record', 'Reference', 'Performed By', 'Status'];
        $lastCol = 'G';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Report');

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setWidth(16);
        }

        // Excel's default row height (~20px) is far shorter than the 60px
        // logo, so without this the image spills downward and covers the
        // text rows below it instead of sitting cleanly above them — row 1
        // is sized (in points; ~1.33px per point) to fully contain it.
        $sheet->getRowDimension(1)->setRowHeight(48);

        // Rows 1-4 are reserved for the logo (drawn on top, offset to sit
        // roughly centered over the table below) plus the centered system
        // name / module name / meta line — same header as the PDF.
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'FOUNDATION UNIVERSITY — UBRA');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('800000');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', $moduleLabel);
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A4:{$lastCol}4");
        $sheet->setCellValue('A4', 'Generated: ' . date('M j, Y g:i A') . ' · ' . count($activities) . ' record(s)');
        $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(9)->getColor()->setRGB('666666');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $logoPath = $this->logoFilePath();
        if ($logoPath !== '') {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setPath($logoPath);
            $drawing->setHeight(60);

            // Anchored at D1 — the exact middle column of A-G (3 columns
            // either side) — with only a small nudge to center the image
            // *within* that one column, rather than computing one large
            // offset across the whole 7-column span from A1. A big offset
            // landed the logo far short of center in testing (some quirk
            // in how the writer resolves a large single-cell offset);
            // a few-pixel nudge within its own anchor cell is far more
            // reliably rendered by Excel.
            $defaultFont = $spreadsheet->getDefaultStyle()->getFont();
            $colWidthPx = \PhpOffice\PhpSpreadsheet\Shared\Drawing::cellDimensionToPixels(16, $defaultFont);
            $drawing->setCoordinates('D1');
            $drawing->setOffsetX((int) round(max(0, $colWidthPx - $drawing->getWidth()) / 2));
            $drawing->setWorksheet($sheet);
        }

        $headerRow = 6;
        foreach ($columns as $i => $label) {
            $col = chr(ord('A') + $i);
            $sheet->setCellValue("{$col}{$headerRow}", $label);
        }
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('800000');

        $row = $headerRow + 1;
        if (empty($activities)) {
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->setCellValue("A{$row}", 'No matching records.');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$row}")->getFont()->setItalic(true)->getColor()->setRGB('888888');
        } else {
            foreach ($activities as $a) {
                $dateStr = !empty($a['date']) ? date('Y-m-d', strtotime($a['date'])) : '';
                $values = [$dateStr, $a['module'], $a['kind'], $a['record'], $a['record_sub'], $a['performed_by'], $a['status']];
                foreach ($values as $i => $val) {
                    $col = chr(ord('A') + $i);
                    $sheet->setCellValue("{$col}{$row}", $val);
                }
                $row++;
            }
        }

        $sheet->getStyle("A{$headerRow}:{$lastCol}" . ($row - 1))
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $filenameModule = strtolower(str_replace(' ', '-', $moduleLabel));
        $filename = 'fu-ubra-' . $filenameModule . '-records-' . date('Y-m-d') . '.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setBody($this->captureWriterOutput($writer));
    }

    // Xlsx writer only knows how to stream to a file handle/path — buffer
    // that into a string so it can go straight into the HTTP response body
    // like every other export here, no temp file to clean up afterward.
    private function captureWriterOutput(\PhpOffice\PhpSpreadsheet\Writer\IWriter $writer): string
    {
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    // Same logo file, resolved to a real filesystem path (not a data URI)
    // — PhpSpreadsheet's Drawing needs an actual path to embed the image.
    private function logoFilePath(): string
    {
        $candidates = [
            FCPATH . 'images/UBRA LOGO (no background).png',
            FCPATH . 'uploads/logo.png',
            FCPATH . 'uploads/logo.jpg',
            FCPATH . 'uploads/logo.jpeg',
            FCPATH . 'Assets/images/logo.png',
            FCPATH . 'Assets/images/logo.jpg',
        ];
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        return '';
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
