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
        $scopeModules = $this->scopeModules();
        $scopedToJanitorial = $scopeModules === ['Janitorial'];
        if ($scopeModules !== null) {
            $activities = array_values(array_filter($activities, fn($a) => in_array($a['module'], $scopeModules, true)));

            $today = date('Y-m-d');
            $stats = [
                'total_records'     => count($activities),
                'archived_records'  => count(array_filter($activities, fn($a) => !empty($a['is_archived']))),
                'reports_generated' => count(array_filter($activities, fn($a) => $a['kind'] === 'Report')),
                'reports_pending'   => count(array_filter($activities, fn($a) => $a['kind'] === 'Report' && $a['status'] === 'Pending')),
                'reports_completed' => count(array_filter($activities, fn($a) => $a['kind'] === 'Report' && $a['status'] === 'Completed')),
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
                'reports_pending'   => count(array_filter($activities, fn($a) => $a['kind'] === 'Report' && $a['status'] === 'Pending')),
                'reports_completed' => count(array_filter($activities, fn($a) => $a['kind'] === 'Report' && $a['status'] === 'Completed')),
                'today_activities'  => count(array_filter($activities, fn($a) => ($a['date'] ?? '') !== null && date('Y-m-d', strtotime($a['date'])) === $today)),
            ];
        }

        return view('records/index', [
            'title'               => 'Report Center',
            'pageCss'             => 'records.css',
            'stats'               => $stats,
            'activities'          => $activities,
            'reportRecords'       => $reportRecords,
            'scopedToJanitorial'  => $scopedToJanitorial,
            'scopeModules'        => $scopeModules,
            'canManageArchive'    => $this->canManageArchive(),
            'canArchive'          => self::canArchiveAny(),
            'disposalRows'        => $this->canManageArchive() ? $this->disposalRows() : [],
            'archivedRows'        => self::canArchiveAny() ? $this->archivedRows() : [],
            'retentionByDept'     => array_combine(array_keys(\App\Libraries\DepartmentScope::CATEGORIES), array_map(fn($d) => self::retentionMonths($d), array_keys(\App\Libraries\DepartmentScope::CATEGORIES))),
            'startView'           => match (true) {
                $this->request->getGet('view') === 'disposal' && $this->canManageArchive() => 'disposal',
                $this->request->getGet('view') === 'archived' && self::canArchiveAny()    => 'archived',
                default => 'records',
            },
            'retentionByDept'     => array_map(fn($d) => self::retentionMonths($d), array_combine(array_keys(\App\Libraries\DepartmentScope::CATEGORIES), array_keys(\App\Libraries\DepartmentScope::CATEGORIES))),
            'expiredByDept'       => $this->canManageArchive() ? $this->expiredCounts() : [],
            'flash_success'       => session()->getFlashdata('success'),
            'flash_error'         => session()->getFlashdata('error'),
        ]);
    }

    // ── Archive retention ────────────────────────────────────────────
    // Archived records stay for the number of months set by the department's policy,
    // after which an administrator can permanently delete them.
    // Record kinds that have no archive column of their own: archiving marks them in the record_archive table.
    // type => [source table, department (category)]
    private const MARKED_TYPES = [
        'facwo'         => ['work_orders', 'Facilities'],
        'aircon'        => ['aircon_units', 'Facilities'],
        'buildingcheck' => ['janitorial_inspections', 'Facilities'],
        'cleaning'      => ['janitorial_assignments', 'Facilities'],
        'supply'        => ['consumable_inventory', 'Facilities'],
        'fire'          => ['fire_extinguishers', 'Safety and Security'],
        'safetyeq'      => ['safety_equipment', 'Safety and Security'],
        'inspection'    => ['safety_inspections', 'Safety and Security'],
        'keylog'        => ['key_borrow_logs', 'Safety and Security'],
        'motorpool'     => ['motorpool_work_orders', 'Equipment'],
        'equipment'     => ['mechanical_equipment', 'Equipment'],
        'maintenance'   => ['vehicle_maintenance', 'Equipment'],
    ];

    // Record kinds whose own table already has is_archived / archived_at.
    // type => [table, department]
    private const FLAGGED_TYPES = [
        'borrow'       => ['borrow_records', 'Equipment'],
        'sportsborrow' => ['borrow_records', 'Facilities'],
        'report'       => ['reports', null],
        'sports'       => ['tools', 'Facilities'],
        'trip'         => ['travel_requests', 'Equipment'],
        'tool'         => ['tools', null],
        'vehicle'      => ['vehicles', 'Equipment'],
        'personnel'    => ['personnel', 'Facilities'],
        'joborder'     => ['job_orders', 'Facilities'],
    ];

    // Kinds of equipment/assets that can be disposed of from the Reports page (any module).
    private const DISPOSABLE = ['aircon', 'fire', 'safetyeq', 'equipment', 'supply', 'tool', 'vehicle'];

    /** Department (category) of a disposal-log record type. */
    private static function disposalCategory(string $type): string
    {
        if ($type === 'travel') return 'Equipment';
        if ($type === 'tool')   return 'Equipment';

        return self::MARKED_TYPES[$type][1] ?? (self::FLAGGED_TYPES[$type][1] ?? 'Equipment');
    }

    /** All record kinds that can be archived from the Reports page. */
    public static function archivableTypes(): array
    {
        return array_merge(array_keys(self::MARKED_TYPES), array_keys(self::FLAGGED_TYPES));
    }

    /** ['type:id' => archived_at] for records marked in record_archive. */
    private function markedArchives(): array
    {
        $out = [];
        try {
            foreach (\Config\Database::connect()->table('record_archive')->get()->getResultArray() as $r) {
                $out[$r['record_type'] . ':' . $r['record_id']] = $r['archived_at'];
            }
        } catch (\Throwable $e) {
            log_message('error', 'record_archive read failed: ' . $e->getMessage());
        }

        return $out;
    }

    // Old report type names -> the module they report on (also used to find a report's department).
    private const REPORT_MODULE_MAP = [
        'Asset Inventory'        => 'Tools',
        'Vehicle Fleet'          => 'Vehicle',
        'Travel Operations'      => 'Vehicle',
        'Maintenance Compliance' => 'Safety',
        'Facilities Management'  => 'Safety',
        'Janitorial Performance' => 'Janitorial',
    ];

    private function canManageArchive(): bool
    {
        return in_array(strtolower((string) $this->session->get('role')), ['administrator', 'facilities'], true);
    }

    private static function deptSlug(string $dept): string
    {
        return \App\Libraries\DepartmentScope::CATEGORIES[$dept]['slug'] ?? strtolower($dept);
    }

    private static function readSetting(string $key): ?int
    {
        try {
            $row = \Config\Database::connect()->table('system_settings')->where('setting_key', $key)->get()->getRow();
            return $row ? (int) $row->setting_value : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Months archived records of a department are kept before they may be permanently deleted. */
    public static function retentionMonths(?string $dept = null): int
    {
        $m = ($dept ? self::readSetting('archive_retention_months_' . self::deptSlug($dept)) : null)
            ?? self::readSetting('archive_retention_months')
            ?? 12;

        return max(1, min(120, $m));
    }

    private function reportDepartment(?string $typeModule): string
    {
        $module = self::REPORT_MODULE_MAP[$typeModule ?? ''] ?? (string) $typeModule;

        return \App\Libraries\DepartmentScope::categoryFor($module);
    }

    /**
     * Archived records already past their department's retention period.
     * Returns a list of ['table' => ..., 'id' => ..., 'dept' => ..., 'marked' => ?record type].
     */
    private function expiredArchives(): array
    {
        $db  = \Config\Database::connect();
        $out = [];
        $cut = [];
        foreach (array_keys(\App\Libraries\DepartmentScope::CATEGORIES) as $dept) {
            $cut[$dept] = date('Y-m-d H:i:s', strtotime('-' . self::retentionMonths($dept) . ' months'));
        }
        $flagged = static fn(string $table, string $cols) => $db->table($table)->select($cols)->where('is_archived', 1)->where('archived_at IS NOT NULL', null, false)->get()->getResultArray();

        try {
            foreach ($flagged('borrow_records', 'id, archived_at') as $r) {
                if ($r['archived_at'] < $cut['Equipment']) $out[] = ['table' => 'borrow_records', 'id' => (int) $r['id'], 'dept' => 'Equipment', 'marked' => null];
            }
            foreach ($flagged('tools', 'id, archived_at, is_facilities') as $r) {
                $dept = !empty($r['is_facilities']) ? 'Facilities' : 'Equipment';
                if ($r['archived_at'] < $cut[$dept]) $out[] = ['table' => 'tools', 'id' => (int) $r['id'], 'dept' => $dept, 'marked' => null];
            }
            foreach ($flagged('reports', 'id, archived_at, type_module') as $r) {
                $dept = $this->reportDepartment($r['type_module']);
                if ($r['archived_at'] < $cut[$dept]) $out[] = ['table' => 'reports', 'id' => (int) $r['id'], 'dept' => $dept, 'marked' => null];
            }
            foreach ($flagged('travel_requests', 'id, archived_at') as $r) {
                if ($r['archived_at'] < $cut['Equipment']) $out[] = ['table' => 'travel_requests', 'id' => (int) $r['id'], 'dept' => 'Equipment', 'marked' => null];
            }
            foreach ($db->table('record_archive')->get()->getResultArray() as $r) {
                [$table, $dept] = self::MARKED_TYPES[$r['record_type']] ?? [null, null];
                if ($table && $r['archived_at'] < $cut[$dept]) $out[] = ['table' => $table, 'id' => (int) $r['record_id'], 'dept' => $dept, 'marked' => $r['record_type']];
            }
        } catch (\Throwable $e) {
            log_message('error', 'expiredArchives failed: ' . $e->getMessage());
        }

        return $out;
    }

    /** Count of expired archives per department, for the retention card. */
    private function expiredCounts(): array
    {
        $counts = array_fill_keys(array_keys(\App\Libraries\DepartmentScope::CATEGORIES), 0);
        foreach ($this->expiredArchives() as $x) {
            $counts[$x['dept']]++;
        }

        return $counts;
    }

    // How each archivable kind appears in the "Archived" view: [title SQL, sub-line SQL]. Tables: see MARKED_TYPES / FLAGGED_TYPES.
    private const ARCHIVE_LABELS = [
        'facwo'         => ["title", "CONCAT_WS(' · ', building, floor)"],
        'aircon'        => ["unit_name", "CONCAT_WS(' · ', location, floor)"],
        'cleaning'      => ["CONCAT('Cleaning — ', assigned_zone)", "CONCAT_WS(' · ', staff_name, floor)"],
        'supply'        => ["item_name", "CONCAT_WS(' · ', building, department)"],
        'buildingcheck' => ["CONCAT('Building check — ', building)", "CONCAT_WS(' · ', floor, inspection_month)"],
        'fire'          => ["unit_id", "CONCAT_WS(' · ', location, floor)"],
        'safetyeq'      => ["CONCAT(equipment_type, ' ', code)", "CONCAT_WS(' · ', building, floor)"],
        'inspection'    => ["CONCAT('Safety inspection — ', building)", "inspection_month"],
        'keylog'        => ["log_number", "CONCAT_WS(' · ', key_item, full_name)"],
        'motorpool'     => ["wo_number", "CONCAT_WS(' · ', wo_type, issue)"],
        'equipment'     => ["CONCAT(code, ' — ', name)", "CONCAT_WS(' · ', equipment_type, location)"],
        'maintenance'   => ["service_type", "CONCAT_WS(' · ', serviced_on, performed_by)"],
        'borrow'        => ["(SELECT asset_name FROM tools WHERE tools.id = t.tool_id)", "CONCAT_WS(' · ', borrower, DATE(borrowed_date))"],
        'sportsborrow'  => ["(SELECT asset_name FROM tools WHERE tools.id = t.tool_id)", "CONCAT_WS(' · ', borrower, DATE(borrowed_date))"],
        'tool'          => ["asset_name", "CONCAT_WS(' · ', asset_code, category)"],
        'vehicle'       => ["vehicle_name", "plate_no"],
        'trip'          => ["trip_id", "CONCAT_WS(' · ', destination, travel_date)"],
        'personnel'     => ["full_name", "CONCAT_WS(' · ', position, emp_id)"],
        'joborder'      => ["CONCAT(job_order_number, ' — ', job_order_title)", "CONCAT_WS(' · ', assignment_location, position)"],
        'report'        => ["report_name", "type_module"],
    ];

    // Readable names for each archivable kind (the "Type" column of the Archived tab).
    private const TYPE_NAMES = [
        'facwo' => 'Repair request', 'aircon' => 'Aircon unit', 'cleaning' => 'Cleaning assignment', 'supply' => 'Supply',
        'buildingcheck' => 'Building check', 'fire' => 'Fire extinguisher', 'safetyeq' => 'Safety equipment',
        'inspection' => 'Safety inspection', 'keylog' => 'Key log', 'motorpool' => 'Motor pool work order',
        'equipment' => 'Mechanical equipment', 'maintenance' => 'Vehicle maintenance', 'borrow' => 'Equipment borrowing',
        'sportsborrow' => 'Sports borrowing', 'tool' => 'Equipment', 'vehicle' => 'Vehicle', 'trip' => 'Trip ticket',
        'personnel' => 'Personnel', 'joborder' => 'Job order', 'report' => 'Report',
    ];

    /**
     * Every archived record of every module the signed-in role may manage, with its category.
     * Rows: key (type:id), category, type, title, sub, archived_at.
     */
    private function archivedRows(): array
    {
        $db = \Config\Database::connect();
        $rows = [];
        foreach (self::ARCHIVE_LABELS as $type => [$title, $sub]) {
            try {
                if (isset(self::MARKED_TYPES[$type])) {
                    [$table, $dept] = self::MARKED_TYPES[$type];
                    if (!$this->canArchiveDepartment($dept)) continue;
                    $found = $db->query("SELECT t.id, {$title} AS title, {$sub} AS sub, a.archived_at, NULL AS extra FROM `{$table}` t JOIN record_archive a ON a.record_type = ? AND a.record_id = t.id ORDER BY a.archived_at DESC LIMIT 500", [$type])->getResultArray();
                } elseif (isset(self::FLAGGED_TYPES[$type])) {
                    [$table, $dept] = self::FLAGGED_TYPES[$type];
                    $extra = $type === 'tool' ? 't.is_facilities' : ($type === 'report' ? 't.type_module' : 'NULL');
                    $dateCol = $type === 'joborder' ? 'updated_at' : 'archived_at';
                    $found = $db->query("SELECT t.id, {$title} AS title, {$sub} AS sub, t.{$dateCol} AS archived_at, {$extra} AS extra FROM `{$table}` t WHERE t.is_archived = 1 ORDER BY t.{$dateCol} DESC LIMIT 500")->getResultArray();
                } else {
                    continue;
                }
            } catch (\Throwable $e) {
                log_message('error', "archived list failed for {$type}: " . $e->getMessage());
                continue;
            }
            foreach ($found as $r) {
                $cat = $dept;
                if ($type === 'tool')   $cat = !empty($r['extra']) ? 'Facilities' : 'Equipment';
                if ($type === 'report') $cat = $this->reportDepartment($r['extra']);
                if (!$this->canArchiveDepartment($cat)) continue;
                $rows[] = [
                    'key' => $type . ':' . $r['id'], 'category' => $cat, 'type' => self::TYPE_NAMES[$type] ?? ucfirst($type),
                    'title' => (string) $r['title'], 'sub' => (string) $r['sub'], 'archived_at' => $r['archived_at'],
                ];
            }
        }
        usort($rows, fn($a, $b) => strcmp((string) $b['archived_at'], (string) $a['archived_at']));

        return $rows;
    }

    /** Departments (categories) whose records the signed-in role may archive or delete; null = all. */
    public static function archiveDepartments(?string $role = null): ?array
    {
        $role = strtolower($role ?? (string) service('session')->get('role'));

        return match ($role) {
            'administrator'                => null,
            'facilities'                   => ['Facilities', 'Equipment'],
            'security'                     => ['Safety and Security', 'Equipment'],
            'safety'                       => ['Safety and Security'],
            'assets', 'tools', 'personnel & tools' => ['Equipment'],
            'sports'                       => ['Facilities'],
            default                        => [],
        };
    }

    public static function canArchiveAny(): bool
    {
        return self::archiveDepartments() !== [];
    }

    private function canArchiveDepartment(?string $dept): bool
    {
        $allowed = self::archiveDepartments();

        return $allowed === null || ($dept !== null && in_array($dept, $allowed, true));
    }

    // Bulk archive / permanent delete from the Reports Hub table. Keys look like "borrow:12" or "report:7".
    // Permanent delete only removes records that are already archived.
    public function bulk()
    {
        if (!$this->session->get('isLoggedIn') || !self::canArchiveAny()) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'You are not allowed to do this.']);
        }
        $action = (string) $this->request->getPost('action');
        $keys   = (array) $this->request->getPost('keys');
        if (!in_array($action, ['archive', 'delete', 'restore', 'dispose'], true) || !$keys) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => 'Nothing selected.']);
        }

        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        $by = (string) $this->session->get('full_name');
        $done = 0;
        $skipped = 0;
        foreach ($keys as $key) {
            [$type, $id] = array_pad(explode(':', (string) $key, 2), 2, '');
            $id = (int) $id;
            if ($id < 1) { $skipped++; continue; }

            $dept = self::MARKED_TYPES[$type][1] ?? (self::FLAGGED_TYPES[$type][1] ?? null);
            if ($type === 'report') {
                $rep = $db->table('reports')->select('type_module')->where('id', $id)->get()->getRowArray();
                $dept = $rep ? $this->reportDepartment($rep['type_module']) : null;
            }
            if ($type === 'tool') {
                $tl = $db->table('tools')->select('is_facilities')->where('id', $id)->get()->getRowArray();
                $dept = ($tl && !empty($tl['is_facilities'])) ? 'Facilities' : 'Equipment';
            }
            if (!$this->canArchiveDepartment($dept)) { $skipped++; continue; }

            if ($action === 'dispose') {
                // Disposal from the Reports page: log it, then archive the record so it leaves its module list.
                if (!in_array($type, self::DISPOSABLE, true)) { $skipped++; continue; }
                try {
                    $table = self::MARKED_TYPES[$type][0] ?? self::FLAGGED_TYPES[$type][0];
                    if ($db->table('disposal_logs')->where('record_type', $type)->where('record_id', $id)->countAllResults() > 0) { $skipped++; continue; }
                    $info = $db->query('SELECT ' . self::ARCHIVE_LABELS[$type][0] . ' AS title, ' . self::ARCHIVE_LABELS[$type][1] . ' AS sub FROM `' . $table . '` t WHERE t.id = ?', [$id])->getRowArray();
                    if (!$info) { $skipped++; continue; }
                    $db->table('disposal_logs')->insert([
                        'record_type'      => $type,
                        'record_id'        => $id,
                        'authorized_by_id' => null,
                        'disposal_date'    => date('Y-m-d'),
                        'remarks'          => trim($info['title'] . ($info['sub'] ? ' (' . $info['sub'] . ')' : '')) . ' — disposed by ' . $by,
                    ]);
                    if (isset(self::MARKED_TYPES[$type])) {
                        if (!$db->table('record_archive')->where('record_type', $type)->where('record_id', $id)->countAllResults()) {
                            $db->table('record_archive')->insert(['record_type' => $type, 'record_id' => $id, 'archived_at' => $now, 'archived_by' => $by]);
                        }
                    } else {
                        $row = $db->table($table)->where('id', $id)->get()->getRowArray();
                        $set = ['is_archived' => 1];
                        if (array_key_exists('archived_at', $row)) $set['archived_at'] = $now;
                        if (array_key_exists('last_activity_at', $row)) $set['last_activity_at'] = $now;
                        $db->table($table)->where('id', $id)->update($set);
                    }
                    $done++;
                } catch (\Throwable $e) {
                    log_message('error', "Bulk dispose failed for {$key}: " . $e->getMessage());
                    $skipped++;
                }
                continue;
            }

            try {
                if (isset(self::MARKED_TYPES[$type])) {
                    $table  = self::MARKED_TYPES[$type][0];
                    $marked = $db->table('record_archive')->where('record_type', $type)->where('record_id', $id)->countAllResults() > 0;
                    if (!$db->table($table)->where('id', $id)->countAllResults()) { $skipped++; continue; }
                    if ($action === 'archive') {
                        if ($marked) { $skipped++; continue; }
                        $db->table('record_archive')->insert(['record_type' => $type, 'record_id' => $id, 'archived_at' => $now, 'archived_by' => $by]);
                    } elseif ($action === 'restore') {
                        if (!$marked) { $skipped++; continue; }
                        $db->table('record_archive')->where('record_type', $type)->where('record_id', $id)->delete();
                    } else {
                        if (!$marked) { $skipped++; continue; }
                        $db->table($table)->where('id', $id)->delete();
                        $db->table('record_archive')->where('record_type', $type)->where('record_id', $id)->delete();
                    }
                    $done++;
                } elseif (isset(self::FLAGGED_TYPES[$type])) {
                    $table = self::FLAGGED_TYPES[$type][0];
                    $row = $db->table($table)->where('id', $id)->get()->getRowArray();
                    if (!$row) { $skipped++; continue; }
                    if ($action === 'archive') {
                        if (!empty($row['is_archived'])) { $skipped++; continue; }
                        $set = ['is_archived' => 1];
                        if (array_key_exists('archived_at', $row)) $set['archived_at'] = $now;
                        if (array_key_exists('last_activity_at', $row)) $set['last_activity_at'] = $now;
                        $db->table($table)->where('id', $id)->update($set);
                    } elseif ($action === 'restore') {
                        if (empty($row['is_archived'])) { $skipped++; continue; }
                        $set = ['is_archived' => 0];
                        if (array_key_exists('archived_at', $row)) $set['archived_at'] = null;
                        if (array_key_exists('last_activity_at', $row)) $set['last_activity_at'] = $now;
                        $db->table($table)->where('id', $id)->update($set);
                    } else {
                        if (empty($row['is_archived'])) { $skipped++; continue; }
                        $db->table($table)->where('id', $id)->delete();
                    }
                    $done++;
                } else {
                    $skipped++;
                }
            } catch (\Throwable $e) {
                log_message('error', "Bulk {$action} failed for {$key}: " . $e->getMessage());
                $skipped++;
            }
        }
        if ($done > 0) {
            $this->logArchiveAction(($action === 'archive' ? 'Archived ' : ($action === 'restore' ? 'Restored ' : ($action === 'dispose' ? 'Disposed ' : 'Permanently deleted '))) . "{$done} record(s) in bulk");
        }

        $msg = $done . ' record(s) ' . ($action === 'archive' ? 'archived' : ($action === 'restore' ? 'restored' : ($action === 'dispose' ? 'disposed and added to the disposal log' : 'permanently deleted'))) . '.'
             . ($action === 'archive' && $done ? ' Find them under Reports → Archived.' : '')
             . ($skipped ? " {$skipped} skipped" . ($action === 'delete' ? ' (only archived records can be deleted, and records still in use stay).' : '.') : '');

        return $this->response->setJSON(['ok' => true, 'done' => $done, 'skipped' => $skipped, 'message' => $msg]);
    }

    /** Data for the Archive & Retention tab in Settings. */
    public function retentionPanelData(): array
    {
        $depts = array_keys(\App\Libraries\DepartmentScope::CATEGORIES);

        return [
            'canManageArchive' => $this->canManageArchive(),
            'retentionByDept'  => array_combine($depts, array_map(fn($d) => self::retentionMonths($d), $depts)),
            'expiredByDept'    => $this->canManageArchive() ? $this->expiredCounts() : [],
        ];
    }

    public function saveRetention()
    {
        if (!$this->session->get('isLoggedIn') || !$this->canManageArchive()) {
            return redirect()->to('/settings#archive')->with('error', 'You are not allowed to change the retention period.');
        }
        $db = \Config\Database::connect();
        $saved = [];
        foreach (array_keys(\App\Libraries\DepartmentScope::CATEGORIES) as $dept) {
            $raw = $this->request->getPost('months')[self::deptSlug($dept)] ?? null;
            if ($raw === null || $raw === '') continue;
            $months = max(1, min(120, (int) $raw));
            $key = 'archive_retention_months_' . self::deptSlug($dept);
            $exists = $db->table('system_settings')->where('setting_key', $key)->countAllResults();
            $exists
                ? $db->table('system_settings')->where('setting_key', $key)->update(['setting_value' => (string) $months])
                : $db->table('system_settings')->insert(['setting_key' => $key, 'setting_value' => (string) $months]);
            $saved[] = "{$dept}: {$months} month(s)";
        }
        $this->logArchiveAction('Archive retention set — ' . implode(', ', $saved));

        return redirect()->to('/settings#archive')->with('success', 'Retention saved. ' . implode(', ', $saved) . '.');
    }

    public function purgeArchive()
    {
        if (!$this->session->get('isLoggedIn') || !$this->canManageArchive()) {
            return redirect()->to('/settings#archive')->with('error', 'You are not allowed to delete archived records.');
        }
        $db = \Config\Database::connect();
        $deleted = array_fill_keys(array_keys(\App\Libraries\DepartmentScope::CATEGORIES), 0);
        foreach ($this->expiredArchives() as $x) {
            try {
                $db->table($x['table'])->where('id', $x['id'])->delete();
                $gone = $db->affectedRows() > 0;
                if ($gone && $x['marked']) {
                    $db->table('record_archive')->where('record_type', $x['marked'])->where('record_id', $x['id'])->delete();
                }
                if ($gone) $deleted[$x['dept']]++;
            } catch (\Throwable $e) {
                // A row still referenced elsewhere cannot be removed; leave it archived.
                log_message('error', "Archive purge skipped for {$x['table']} #{$x['id']}: " . $e->getMessage());
            }
        }
        $parts = [];
        foreach ($deleted as $dept => $n) $parts[] = "{$dept}: {$n}";
        $this->logArchiveAction('Permanently deleted expired archives — ' . implode(', ', $parts));

        return redirect()->to('/settings#archive')->with('success', 'Expired archives permanently deleted (' . implode(', ', $parts) . ').');
    }

    private function logArchiveAction(string $action): void
    {
        try {
            (new ActivityLogModel())->insert(['user_id' => $this->session->get('user_id') ?: null, 'module' => 'Archive', 'action' => $action]);
        } catch (\Throwable $e) {
            log_message('error', 'Audit log insert failed: ' . $e->getMessage());
        }
    }

    // Disposal log rows for the Disposal view on the Reports page.
    private function disposalRows(): array
    {
        $db = \Config\Database::connect();
        $rows = [];
        foreach ($this->disposalLogModel->getAllWithDetails() as $l) {
            $type = (string) $l['record_type'];
            $record = '#' . $l['record_id'];
            // Look up a readable name for the disposed record when it still exists.
            try {
                if ($type === 'travel') {
                    $r = $db->table('travel_requests')->select('trip_id')->where('id', $l['record_id'])->get()->getRowArray();
                    if ($r) $record = $r['trip_id'];
                } elseif (isset(self::ARCHIVE_LABELS[$type]) && (isset(self::MARKED_TYPES[$type]) || isset(self::FLAGGED_TYPES[$type]))) {
                    $table = self::MARKED_TYPES[$type][0] ?? self::FLAGGED_TYPES[$type][0];
                    $r = $db->query('SELECT ' . self::ARCHIVE_LABELS[$type][0] . ' AS title FROM `' . $table . '` t WHERE t.id = ?', [$l['record_id']])->getRowArray();
                    if ($r && $r['title'] !== null && $r['title'] !== '') $record = $r['title'];
                }
            } catch (\Throwable $e) {
                // keep the #id fallback
            }
            $labels = ['aircon' => 'Aircon unit', 'borrow' => 'Equipment borrowed', 'travel' => 'Trip ticket', 'tool' => 'Equipment', 'fire' => 'Fire extinguisher', 'safetyeq' => 'Safety equipment', 'equipment' => 'Mechanical equipment', 'supply' => 'Supply', 'vehicle' => 'Vehicle'];
            $rows[] = [
                'date'     => $l['disposal_date'] ?: substr((string) $l['created_at'], 0, 10),
                'category' => self::disposalCategory($type),
                'type'     => $labels[$type] ?? ucfirst($type),
                'record'   => $record,
                'by'       => $l['authorized_by_name'] ?: (preg_match('/disposed by (.+)$/u', (string) $l['remarks'], $mm) ? trim($mm[1]) : '—'),
                'remarks'  => $l['remarks'] ?: '—',
            ];
        }

        return $rows;
    }

    // Roles that only see their own department's records in the Information Hub (null = everything).
    private function scopeModules(): ?array
    {
        return \App\Libraries\DepartmentScope::modulesForRole((string) $this->session->get('role'));
    }

    private function rec(string $type, $id, $date, string $module, string $record, string $sub, string $action, string $by, string $status): array
    {
        return [
            'type' => $type, 'id' => $id, 'date' => $date ?: null, 'module' => $module, 'kind' => 'Record', 'record' => $record, 'record_sub' => $sub,
            'action' => $action, 'performed_by' => $by ?: '—', 'status' => $status, 'is_archived' => false, 'disposal_status' => 'None',
        ];
    }

    // Safety and Security: fire safety equipment, building safety inspections, and key borrowing.
    private function securityActivities(): array
    {
        $out = [];
        $today = date('Y-m-d');
        foreach ((new \App\Models\FireExtinguisherModel())->withArchived()->findAll() as $e) {
            $state = $e['status'] === 'New' ? ((!empty($e['expires_on']) && $e['expires_on'] < $today) ? 'Expired' : ((!empty($e['next_due']) && $e['next_due'] < $today) ? 'Overdue' : 'Good')) : $e['status'];
            $out[] = $this->rec('fire', $e['id'], $e['updated_at'] ?: $e['created_at'], 'Security', 'Fire Extinguisher ' . $e['unit_id'], $e['location'] . ' — ' . $e['floor'], 'Fire Safety', $e['inspector'] ?? '', $state);
        }
        foreach ((new \App\Models\SafetyEquipmentModel())->withArchived()->findAll() as $e) {
            $out[] = $this->rec('safetyeq', $e['id'], $e['created_at'], 'Security', $e['equipment_type'] . ' ' . $e['code'], $e['building'] . ($e['floor'] ? ' — ' . $e['floor'] : ''), 'Fire Safety', '', $e['status'] === 'Working' ? 'Good' : $e['status']);
        }
        foreach ((new \App\Models\SafetyInspectionModel())->withArchived()->orderBy('inspected_at', 'DESC')->findAll() as $i) {
            $out[] = $this->rec('inspection', $i['id'], $i['inspected_at'], 'Security', 'Safety inspection — ' . $i['building'], $i['remarks'] ?: $i['inspection_month'], 'Inspection', $i['inspected_by'] ?? '', $i['safety_status']);
        }
        foreach ((new \App\Models\KeyBorrowLogModel())->withArchived()->orderBy('id', 'DESC')->findAll() as $l) {
            $out[] = $this->rec('keylog', $l['id'], $l['scan_in'] ?: $l['created_at'], 'Security', $l['log_number'] . ' — ' . $l['key_item'], 'Date borrowed ' . ($l['scan_in'] ? date('M j, Y', strtotime($l['scan_in'])) : '—'), 'Guard Monitoring', $l['full_name'], $l['status'] === 'Active' ? 'Borrowed' : 'Returned');
        }

        return $out;
    }

    // Facilities Administration and General Services: repair requests, aircon units, building checks, cleaning, supplies.
    private function facilitiesActivities(): array
    {
        $out = [];
        foreach ((new \App\Models\WorkOrderModel())->withArchived()->orderBy('id', 'DESC')->findAll() as $w) {
            $out[] = $this->rec('facwo', $w['id'], $w['updated_at'] ?: $w['created_at'], 'Facilities', $w['title'], $w['building'] . ($w['floor'] ? ' — ' . $w['floor'] : ''), 'Repair Request', $w['requested_by'], $w['status']);
        }
        foreach ((new \App\Models\AirconUnitModel())->withArchived()->findAll() as $a) {
            $out[] = $this->rec('aircon', $a['id'], $a['updated_at'] ?: $a['created_at'], 'Facilities', $a['unit_name'], $a['location'] . ' — ' . $a['floor'], 'Aircon', $a['assigned_tech'] ?? '', $a['condition_status']);
        }
        foreach ((new \App\Models\JanitorialInspectionModel())->withArchived()->orderBy('inspected_at', 'DESC')->findAll() as $i) {
            $out[] = $this->rec('buildingcheck', $i['id'], $i['inspected_at'], 'Facilities', 'Building check — ' . $i['building'], $i['notes'] ?: $i['inspection_month'], 'Building Check', $i['inspected_by'] ?? '', $i['result']);
        }
        foreach ((new \App\Models\JanitorialAssignmentModel())->withArchived()->orderBy('id', 'DESC')->findAll() as $j) {
            $out[] = $this->rec('cleaning', $j['id'], $j['date_assigned'], 'Facilities', 'Cleaning — ' . $j['assigned_zone'], ($j['priority'] ?? 'Routine') . ' · ' . ($j['floor'] ?: 'All floors'), 'Janitorial', $j['staff_name'], $j['status']);
        }
        foreach ((new \App\Models\ConsumableInventoryModel())->withArchived()->findAll() as $c) {
            $state = (float) $c['current_stock'] <= 0 ? 'Out of Stock' : ((float) $c['current_stock'] <= (float) $c['reorder_threshold'] ? 'Low Stock' : 'In Stock');
            $out[] = $this->rec('supply', $c['id'], $c['last_refill'] ?: $c['created_at'], 'Facilities', $c['item_name'], ($c['building'] ?: $c['department'] ?: 'Supplies') . ' · ' . (float) $c['current_stock'] . ' ' . $c['unit'], 'Supplies', '', $state);
        }

        return $out;
    }

    // Waste Watch: every daily weigh-in (non-biodegradable, biodegradable, hazardous) per building.
    private function wasteActivities(): array
    {
        $out = [];
        $rows = \Config\Database::connect()->table('waste_logs l')
            ->select('l.*, s.building')
            ->join('waste_stations s', 's.id = l.station_id', 'left')
            ->orderBy('l.log_date', 'DESC')->orderBy('l.id', 'DESC')
            ->get()->getResultArray();
        foreach ($rows as $l) {
            $haz = (float) $l['hazardous_kg'];
            $out[] = $this->rec(
                'waste', $l['id'], $l['log_date'], 'Waste Watch',
                'Waste weigh-in - ' . ($l['building'] ?? 'Station'),
                'Non-bio ' . (float) $l['non_bio_kg'] . ' kg | Bio ' . (float) $l['bio_kg'] . ' kg | Hazardous ' . $haz . ' kg',
                'Daily weigh-in', $l['checked_by'] ?? '',
                $haz >= \App\Controllers\WasteController::HAZARD_LIMIT_KG ? 'High hazardous' : 'Logged'
            );
        }
        return $out;
    }

    // Sports Equipment Monitoring: the sports equipment itself and every borrow / return of it.
    private function sportsActivities(): array
    {
        $out = [];
        $tools = [];
        foreach ($this->toolsModel->where('category', 'Sports Equipment')->findAll() as $t) $tools[$t['id']] = $t;
        foreach ($tools as $t) {
            if (!empty($t['is_archived'])) continue;
            $out[] = [
                'type' => 'sports', 'id' => $t['id'], 'date' => $t['last_activity_at'] ?: ($t['created_at'] ?? null), 'module' => 'Sports', 'kind' => 'Record',
                'record' => ($t['asset_code'] ? $t['asset_code'] . ' — ' : '') . $t['asset_name'], 'record_sub' => ($t['location'] ?: 'No location') . ' · ' . ($t['condition_status'] ?: '—'),
                'action' => 'Equipment', 'performed_by' => $t['custodian'] ?: '—', 'status' => $t['availability'], 'is_archived' => false, 'disposal_status' => 'None',
            ];
        }
        if ($tools) {
            foreach ($this->borrowModel->whereIn('tool_id', array_keys($tools))->where('is_archived', 0)->orderBy('id', 'DESC')->findAll() as $b) {
                $t = $tools[$b['tool_id']];
                $out[] = [
                    'type' => 'sportsborrow', 'id' => $b['id'], 'date' => $b['borrowed_date'], 'module' => 'Sports', 'kind' => 'Record',
                    'record' => $t['asset_name'], 'record_sub' => 'Due back ' . ($b['expected_return'] ? date('M j, Y', strtotime($b['expected_return'])) : '—') . ($b['department'] ? ' · ' . $b['department'] : ''),
                    'action' => 'Borrowing', 'performed_by' => $b['borrower'] ?: '—', 'status' => $b['status'], 'is_archived' => false, 'disposal_status' => 'None',
                ];
            }
        }

        return $out;
    }

    // Asset Acquisition and Monitoring records: trips, vehicle maintenance, motor pool work orders and equipment.
    private function assetActivities(): array
    {
        $out = [];
        $stamp = fn($d) => $d ?: null;

        $vehicles = [];
        foreach ($this->vehicleModel->findAll() as $v) $vehicles[$v['id']] = $v['vehicle_name'] . ' (' . $v['plate_no'] . ')';
        $equipment = [];
        foreach ((new \App\Models\MechanicalEquipmentModel())->withArchived()->findAll() as $e) $equipment[$e['id']] = $e['code'] . ' — ' . $e['name'];

        foreach ((new \App\Models\MotorpoolWorkOrderModel())->withArchived()->orderBy('id', 'DESC')->findAll() as $w) {
            $item = $w['wo_type'] === 'Vehicle Repair' ? ($vehicles[$w['vehicle_id']] ?? '—') : ($equipment[$w['equipment_id']] ?? '—');
            $out[] = [
                'type' => 'motorpool', 'id' => $w['id'], 'date' => $stamp($w['updated_at'] ?: $w['created_at']), 'module' => 'Motor Pool', 'kind' => 'Record',
                'record' => $w['wo_number'] . ' — ' . $item, 'record_sub' => $w['wo_type'] . ': ' . $w['issue'], 'action' => 'Work Order',
                'performed_by' => $w['requested_by'], 'status' => $w['status'], 'is_archived' => false, 'disposal_status' => 'None',
            ];
        }
        foreach ((new \App\Models\MechanicalEquipmentModel())->withArchived()->findAll() as $e) {
            $out[] = [
                'type' => 'equipment', 'id' => $e['id'], 'date' => $stamp($e['created_at']), 'module' => 'Motor Pool', 'kind' => 'Record',
                'record' => $e['code'] . ' — ' . $e['name'], 'record_sub' => $e['equipment_type'] . ($e['location'] ? ' · ' . $e['location'] : ''), 'action' => 'Equipment',
                'performed_by' => '—', 'status' => $e['status'], 'is_archived' => false, 'disposal_status' => 'None',
            ];
        }
        foreach ((new \App\Models\VehicleMaintenanceModel())->withArchived()->orderBy('serviced_on', 'DESC')->findAll() as $m) {
            $out[] = [
                'type' => 'maintenance', 'id' => $m['id'], 'date' => $stamp($m['serviced_on']), 'module' => 'Vehicle', 'kind' => 'Record',
                'record' => $m['service_type'] . ' — ' . ($vehicles[$m['vehicle_id']] ?? 'Vehicle'),
                'record_sub' => $m['odometer_km'] !== null ? number_format((float) $m['odometer_km']) . ' km' : 'Vehicle maintenance',
                'action' => 'Maintenance', 'performed_by' => $m['performed_by'] ?: '—', 'status' => 'Completed', 'is_archived' => false, 'disposal_status' => 'None',
            ];
        }
        foreach ((new \App\Models\TravelModel())->getAllWithDetails() as $t) {
            $out[] = [
                'type' => 'trip', 'id' => $t['id'], 'date' => $stamp($t['travel_date']), 'module' => 'Vehicle', 'kind' => 'Record',
                'record' => $t['trip_id'] . ' — ' . $t['destination'],
                'record_sub' => ($t['driver_name'] ?: 'No driver') . ' · ' . ($t['vehicle_name'] ?: 'No vehicle'),
                'action' => 'Trip Ticket', 'performed_by' => $t['requester_name'] ?: '—', 'status' => $t['status'], 'is_archived' => false, 'disposal_status' => 'None',
            ];
        }

        return $out;
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
        $reportModuleMap = self::REPORT_MODULE_MAP;
        $disposalModuleMap = ['borrow' => 'Tools', 'travel' => 'Vehicle', 'aircon' => 'Facilities'];

        $activities = [];

        foreach ($borrowRecords as $r) {
            // Once disposed, the disposal_logs row below is the record of it — skip the
            // original so it doesn't also show up as a separate, still-active entry.
            if (($r['disposal_status'] ?? 'None') === 'Disposed') continue;

            $activities[] = [
                'type'        => 'borrow',
                'id'          => $r['id'],
                'date'        => $r['borrowed_date'] ?? $r['created_at'] ?? $r['last_activity_at'] ?? null,
                'module'      => ($r['category'] ?? '') === 'Power Tools' ? 'Power Tools' : 'Tools',
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
                'module'      => $reportModuleMap[$r['type_module'] ?? ''] ?? (isset(\App\Libraries\DepartmentScope::SLUGS[$r['type_module'] ?? '']) || in_array($r['type_module'] ?? '', ['Power Tools'], true) ? $r['type_module'] : 'Safety'),
                'kind'        => 'Report',
                'record'      => $r['report_name'] ?? 'Report',
                'record_sub'  => $r['type_module'] ?? 'General',
                'action'      => !empty($r['is_archived']) ? 'Archived' : (!empty($r['submitted_by']) ? 'Submitted' : 'Generated'),
                'performed_by'=> $r['generated_by_name'] ?? ($r['submitted_by'] ?? '—'),
                'status'      => !empty($r['is_archived']) ? 'Archived' : ($r['status'] ?? 'Completed'),
                'details'     => $r['details'] ?? '',
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
        // own "Archive" icon buttons (Equipment Management, Vehicle Management,
        // Personnel Management); this is what makes those actions show up here.
        $archivedTools = $this->toolsModel->where('is_archived', 1)->orderBy('archived_at', 'DESC')->findAll();
        foreach ($archivedTools as $t) {
            $activities[] = [
                'type'        => 'tool',
                'id'          => $t['id'],
                'date'        => $t['archived_at'] ?? $t['last_activity_at'] ?? null,
                'module'      => ($t['category'] ?? '') === 'Power Tools' ? 'Power Tools' : 'Tools',
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

        $activities = array_merge($activities, $this->assetActivities(), $this->sportsActivities(), $this->securityActivities(), $this->facilitiesActivities(), $this->wasteActivities());

        usort($activities, function ($a, $b) {
            return (strtotime($b['date'] ?? '') ?: 0) <=> (strtotime($a['date'] ?? '') ?: 0);
        });

        $marked = $this->markedArchives();
        foreach ($activities as &$act) {
            if (isset(self::MARKED_TYPES[$act['type']]) && isset($marked[$act['type'] . ':' . $act['id']])) {
                $act['kind'] = 'Archive';
                $act['action'] = 'Archived';
                $act['status'] = 'Archived';
                $act['is_archived'] = true;
            }
            $act['category']   = \App\Libraries\DepartmentScope::categoryFor((string) $act['module']);
            $act['type_label'] = \App\Libraries\DepartmentScope::typeLabel((string) $act['module']);
        }
        unset($act);

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
        if (($scopeModules = $this->scopeModules()) !== null) {
            $activities = array_values(array_filter($activities, fn($a) => in_array($a['module'], $scopeModules, true)));
        }

        $filtered = array_values(array_filter($activities, function ($a) use ($slug, $module, $kind, $status, $date, $dateFrom, $dateTo, $search) {
            $modSlug    = $slug($a['category'] ?? $a['module']);
            $typeSlug   = $slug($a['module']);
            $kindSlug   = $slug($a['kind']);
            $statusSlug = $slug($a['status']);
            $dateKey    = !empty($a['date']) ? date('Y-m-d', strtotime($a['date'])) : '';
            $searchBlob = strtolower($a['module'] . ' ' . $a['record'] . ' ' . $a['record_sub'] . ' ' . $a['performed_by'] . ' ' . $a['action'] . ' ' . $a['status']);

            $inRange = true;
            if ($dateFrom !== '' && $dateKey !== '') $inRange = $inRange && $dateKey >= $dateFrom;
            if ($dateTo !== ''   && $dateKey !== '') $inRange = $inRange && $dateKey <= $dateTo;
            if (($dateFrom !== '' || $dateTo !== '') && $dateKey === '') $inRange = false;

            return (!$module || $modSlug === $module || $typeSlug === $module)
                && (!$kind || $kindSlug === $kind)
                && (!$status || $statusSlug === $status)
                && (!$date || $dateKey === $date)
                && $inRange
                && (!$search || str_contains($searchBlob, $search));
        }));

        $moduleLabel = $module !== '' ? ucfirst(str_replace('-', ' ', $module)) : 'All Categories';
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
                $path = $this->smallLogo($path);
                $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    default       => 'image/png',
                };
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }
        }
        return '';
    }

    // The logo file is 800x800 (750 KB); Dompdf re-decodes it on every export and that alone
    // took about a minute. The header only shows it at 60 px, so use a small cached copy.
    private function smallLogo(string $path): string
    {
        $cache = WRITEPATH . 'cache/pdf_logo_' . md5($path . filemtime($path)) . '.png';
        if (is_file($cache)) return $cache;
        if (!function_exists('imagecreatefromstring')) return $path;
        $src = @imagecreatefromstring((string) file_get_contents($path));
        if (!$src) return $path;
        $w = imagesx($src); $h = imagesy($src); $size = 160;
        if ($w <= $size && $h <= $size) { imagedestroy($src); return $path; }
        $ratio = min($size / $w, $size / $h);
        $nw = max(1, (int) round($w * $ratio)); $nh = max(1, (int) round($h * $ratio));
        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false); imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        if (!is_dir(dirname($cache))) @mkdir(dirname($cache), 0775, true);
        $ok = @imagepng($dst, $cache, 9);
        imagedestroy($src); imagedestroy($dst);
        return $ok ? $cache : $path;
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
        // Drawn straight onto the page (see RecordsPdf) - building an HTML table for Dompdf took ~40 ms per row.
        @set_time_limit(120);
        $logo = '';
        foreach ([FCPATH . 'images/UBRA LOGO (no background).png', FCPATH . 'uploads/logo.png', FCPATH . 'Assets/images/logo.png'] as $c) {
            if (is_file($c)) { $logo = $this->smallLogo($c); break; }
        }
        $pdf = \App\Libraries\RecordsPdf::render($activities, $moduleLabel, $logo);

        $filenameModule = strtolower(str_replace(' ', '-', $moduleLabel));
        $filename = 'fu-ubra-' . $filenameModule . '-records-' . date('Y-m-d') . '.pdf';
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setBody($pdf);
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
            fputcsv($file, ['Date', 'Category', 'Record', 'Reference', 'Performed By', 'Status']);
            foreach ($activities as $a) {
                $dateStr = !empty($a['date']) ? date('Y-m-d', strtotime($a['date'])) : '';
                fputcsv($file, [$dateStr, ($a['category'] ?? $a['module']) . ' - ' . ($a['type_label'] ?? ''), $a['record'], $a['record_sub'], $a['performed_by'], $a['status']]);
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
        $columns = ['Date', 'Category', 'Record', 'Reference', 'Performed By', 'Status'];
        $lastCol = 'F';

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
                $values = [$dateStr, ($a['category'] ?? $a['module']) . ' - ' . ($a['type_label'] ?? ''), $a['record'], $a['record_sub'], $a['performed_by'], $a['status']];
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
