<?php

namespace App\Controllers;

use App\Models\FacilityKeyModel;
use App\Models\FireExtinguisherModel;
use App\Models\FloorPlanMarkerModel;
use App\Models\KeyBorrowLogModel;
use App\Models\SafetyEquipmentModel;
use App\Models\SafetyInspectionModel;
use App\Models\TravelModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class SecurityDeptController extends BaseController
{
    private const SECTIONS = [
        'fire-safety' => 'Fire Safety',
        'guard'       => 'Guard & Security Monitoring',
        'inspection'  => 'Safety Inspection',
    ];

    private function fmtDate($d): string
    {
        return !empty($d) ? date('M d, Y', strtotime($d)) : '—';
    }

    private function fmtDateTime($d): string
    {
        return !empty($d) ? date('M d, Y g:i A', strtotime($d)) : '—';
    }

    // What the tables show for an item: its problem if it has one, otherwise its check state ('OK' reads as 'Good').
    private function shownState(array $r): string
    {
        if ($r['status'] !== 'Working') return $r['status'];

        return $r['due'] === 'OK' ? 'Good' : $r['due'];
    }

    // 'OK' | 'Due in 7 Days' | 'Overdue' for a next-check date.
    private function dueState(?string $next): string
    {
        if (empty($next)) return 'OK';
        $today = date('Y-m-d');
        if ($next < $today) return 'Overdue';
        if ($next <= date('Y-m-d', strtotime('+7 days'))) return 'Due in 7 Days';
        return 'OK';
    }

    // A working item past its expiry date is "Expired"; within 30 days of it, the check is "Due Soon".
    private function expiryAdjust(string $status, string $due, ?string $expires): array
    {
        if (!$expires) return [$status, $due];
        $today = date('Y-m-d');
        if ($expires < $today) {
            if (in_array($status, ['Working', 'New'], true)) $status = 'Expired';
            return [$status, 'Overdue'];
        }
        if ($due === 'OK' && $expires <= date('Y-m-d', strtotime('+30 days'))) $due = 'Due Soon';
        return [$status, $due];
    }

    // Everything the Fire Safety tab shows, as one flat list of equipment rows.
    private function equipmentRows(): array
    {
        $rows = [];

        foreach ((new FireExtinguisherModel())->orderBy('location', 'ASC')->findAll() as $e) {
            $status = in_array($e['status'], ['Defective', 'Missing'], true) ? $e['status']
                : ($e['status'] === 'Refillable' ? 'Needs Refill' : 'Working');
            [$status, $due] = $this->expiryAdjust($status, $this->dueState($e['next_due']), $e['expires_on'] ?? null);
            $rows[] = [
                'type'     => 'Fire Extinguisher',
                'code'     => $e['unit_id'],
                'building' => $e['location'],
                'floor'    => $e['floor'],
                'status'   => $status,
                'installed' => $e['installed_on'] ?? null,
                'expires'  => $e['expires_on'] ?? null,
                'due'      => $due,
                'next'     => $e['next_due'],
                'detail'   => trim(($e['type'] ?? '') . ($e['weight_kg'] ? ', ' . $e['weight_kg'] . ' kg' : '')),
                'remarks'  => $e['notes'] ?? null,
            ];
        }

        foreach ((new SafetyEquipmentModel())->orderBy('building', 'ASC')->findAll() as $e) {
            [$status, $due] = $this->expiryAdjust($e['status'], $this->dueState($e['next_check']), $e['expires_on'] ?? null);
            $rows[] = [
                'type'     => $e['equipment_type'],
                'code'     => $e['code'],
                'building' => $e['building'],
                'floor'    => $e['floor'],
                'status'   => $status,
                'installed' => $e['installed_on'] ?? null,
                'expires'  => $e['expires_on'] ?? null,
                'due'      => $due,
                'next'     => $e['next_check'],
                'detail'   => (string) ($e['location_note'] ?? ''),
                'remarks'  => $e['remarks'],
            ];
        }

        // Equipment placed on the floor plans counts too, so every number on the portal agrees.
        $plans = [];
        foreach ($this->floorPlans() as $p) $plans[$p['file']] = $p;
        $today = date('Y-m-d');
        foreach ((new FloorPlanMarkerModel())->findAll() as $m) {
            $p = $plans[$m['plan_file']] ?? null;
            if (!$p) continue;
            $exp = in_array($m['equipment_type'], ['Fire Extinguisher', 'Smoke Detector'], true) ? $m['expires_on'] : null;
            $status = $m['status'];
            if ($status === 'Working' && $exp && $exp < $today) $status = 'Expired';
            $due = !$exp ? 'OK' : ($exp < $today ? 'Overdue' : ($exp <= date('Y-m-d', strtotime('+30 days')) ? 'Due Soon' : 'OK'));
            $rows[] = [
                'type'     => $m['equipment_type'],
                'code'     => $m['label'] ?: 'Plan marker #' . $m['id'],
                'building' => $p['campus'] ?? $p['building'],
                'floor'    => $p['floor'],
                'status'   => $status,
                'due'      => $due,
                'installed' => $m['created_at'] ? substr($m['created_at'], 0, 10) : null,
                'expires'  => $exp,
                'next'     => $exp,
                'detail'   => 'Placed on floor plan',
                'remarks'  => null,
                'src'      => 'plan',
            ];
        }

        return $rows;
    }

    private function needsAttention(array $r): bool
    {
        return $r['status'] !== 'Working';
    }

    public function overview()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $equipment = $this->equipmentRows();
        $inspections = $this->latestInspections();
        $keyLogs = (new KeyBorrowLogModel())->where('status', 'Active')->countAllResults();
        $trips = (new TravelModel())->where('status', 'In Transit')->where('is_archived', 0)->countAllResults();

        $fs = $this->fireSafetyData();
        $gd = $this->guardData();
        $in = $this->inspectionData();
        $overdue = array_values(array_filter($equipment, fn($r) => $r['due'] === 'Overdue'));
        $overdueRows = array_map(fn($r) => [$r['type'], $r['code'], $r['building'], $r['floor'] ?? '—', $this->shownState($r), $this->fmtDate($r['next'])], $overdue);
        $details = [
            'sd_total'  => ['title' => 'All Safety Equipment'] + $fs['stat_detail']['fs_total'],
            'sd_attn'   => $fs['stat_detail']['fs_attention'],
            'sd_over'   => ['title' => 'Checks Overdue', 'columns' => $fs['stat_detail']['fs_due']['columns'], 'rows' => $overdueRows],
            'sd_keys'   => $gd['stat_detail']['g_out'],
            'sd_veh'    => $gd['stat_detail']['g_veh'],
            'sd_unsafe' => $in['stat_detail']['i_unsafe'],
        ];

        return view('security_dept/dashboard', [
            'details' => $details,
            'title'   => 'Safety and Security Department',
            'pageCss' => 'safety.css',
            'stats'   => [
                ['key' => 'sd_total', 'label' => 'Safety Equipment', 'value' => count($equipment), 'icon' => 'bi-fire', 'tone' => 'maroon'],
                ['key' => 'sd_attn', 'label' => 'Equipment Needing Attention', 'value' => count(array_filter($equipment, fn($r) => $this->needsAttention($r))), 'icon' => 'bi-exclamation-triangle-fill', 'tone' => 'red'],
                ['key' => 'sd_over', 'label' => 'Checks Overdue', 'value' => count($overdue), 'icon' => 'bi-calendar-x', 'tone' => 'red'],
                ['key' => 'sd_keys', 'label' => 'Keys Currently Out', 'value' => $keyLogs, 'icon' => 'bi-key-fill', 'tone' => 'amber'],
                ['key' => 'sd_veh', 'label' => 'Vehicles Out', 'value' => $trips, 'icon' => 'bi-truck', 'tone' => 'blue'],
                ['key' => 'sd_unsafe', 'label' => 'Unsafe Buildings', 'value' => count(array_filter($inspections, fn($i) => ($i['safety_status'] ?? null) === 'Unsafe')), 'icon' => 'bi-shield-exclamation', 'tone' => 'red'],
            ],
            'sections' => [
                ['label' => 'Fire Safety', 'url' => 'security-dept/fire-safety', 'icon' => 'bi-fire', 'desc' => 'Fire extinguishers, fire alarms, smoke detectors, and exit signs.'],
                ['label' => 'Guard & Security Monitoring', 'url' => 'security-dept/guard', 'icon' => 'bi-shield-check', 'desc' => 'Key borrowing with QR scanning, guard records, and vehicle entry and exit.'],
                ['label' => 'Safety Inspection', 'url' => 'security-dept/inspection', 'icon' => 'bi-clipboard2-check', 'desc' => 'Monthly inspection form, building safety status, and history.'],
            ],
        ]);
    }

    // Combined status page — the department's own dashboard (the sidebar label opens this).
    // Combined status page — the department's own dashboard (the sidebar label opens this).
    public function status()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $cards = [];
        foreach ([$this->fireSafetyData(), $this->guardData(), $this->inspectionData()] as $d) {
            foreach ($d['status'] as $card) {
                $detail = $d['stat_detail'][$card['key']];
                $cards[] = $card + ['title' => $detail['title'], 'columns' => $detail['columns'], 'rows' => $detail['rows']];
            }
        }

        return view('facilities/status', [
            'title'      => 'Safety and Security Status',
            'page_title' => 'Safety and Security Status',
            'pageCss'    => 'safety.css',
            'cards'      => $cards,
        ]);
    }

    // Latest inspection this month for every campus building (null fields if none yet).
    private function latestInspections(): array
    {
        $month = date('Y-m');
        $latest = [];
        foreach ((new SafetyInspectionModel())->where('inspection_month', $month)->orderBy('inspected_at', 'DESC')->findAll() as $r) {
            $latest[$r['building']] ??= $r;
        }

        return array_map(fn($b) => [
            'building'      => $b,
            'safety_status' => $latest[$b]['safety_status'] ?? null,
            'remarks'       => $latest[$b]['remarks'] ?? null,
            'inspected_by'  => $latest[$b]['inspected_by'] ?? null,
            'inspected_at'  => $latest[$b]['inspected_at'] ?? null,
        ], FireExtinguisherModel::BUILDINGS);
    }

    public function index(string $section = 'fire-safety')
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');
        if (!isset(self::SECTIONS[$section])) throw PageNotFoundException::forPageNotFound();

        $data = match ($section) {
            'fire-safety' => $this->fireSafetyData(),
            'guard'       => $this->guardData(),
            'inspection'  => $this->inspectionData(),
        };

        $stat = (string) $this->request->getGet('stat');

        return view('security_dept/index', array_merge([
            'title'       => self::SECTIONS[$section],
            'section'     => $section,
            'pageCss'     => 'safety.css',
            'buildings'   => FireExtinguisherModel::BUILDINGS,
            'active_stat' => $stat ?: null,
            'stat_rows'   => ($data['stat_detail'][$stat] ?? null),
        ], $data));
    }

    // List of Keys: every key on record (registered keys + any key that has ever been borrowed), and where it is now.
    public function keyList()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $keys = [];
        foreach ((new FacilityKeyModel())->orderBy('key_name', 'ASC')->findAll() as $k) {
            $keys[mb_strtolower($k['key_name'])] = ['id' => (int) $k['id'], 'name' => $k['key_name'], 'location' => $k['location'], 'floor' => $k['floor'] ?? null, 'registered' => true, 'uid' => $k['nfc_uid'], 'times' => 0, 'out' => false, 'last_by' => null, 'last_at' => null, 'borrower' => null];
        }
        foreach ((new KeyBorrowLogModel())->orderBy('scan_in', 'ASC')->findAll() as $l) {
            $name = trim((string) $l['key_item']);
            if ($name === '') continue;
            $id = mb_strtolower($name);
            $keys[$id] ??= ['id' => null, 'name' => $name, 'location' => null, 'floor' => null, 'registered' => false, 'uid' => null, 'times' => 0, 'out' => false, 'last_by' => null, 'last_at' => null, 'borrower' => null];
            $keys[$id]['times']++;
            $keys[$id]['last_by'] = $l['full_name'];
            $keys[$id]['last_at'] = $l['scan_in'];
            $keys[$id]['out'] = $l['status'] === 'Active';
            $keys[$id]['borrower'] = $l['status'] === 'Active' ? $l['full_name'] : null;
        }
        uasort($keys, fn($a, $b) => strcasecmp($a['name'], $b['name']));

        $cols = ['Key', 'Location', 'Floor', 'Tag ID', 'Status', 'With', 'Times Borrowed', 'Last Borrowed', 'Action'];
        // QR button for keys that have a Tag ID; keys that were only ever borrowed (never listed) get an "Add to list" button.
        $action = function ($k) {
            if ($k['registered']) {
                return ['raw' => '<div style="display:flex;gap:6px;justify-content:center;align-items:center;"><button type="button" class="fp-btn" style="height:30px;padding:0 12px;font-size:12px;" data-name="' . esc($k['name']) . '" data-uid="' . esc($k['uid']) . '" data-place="' . esc(trim(($k['location'] ?: '') . ($k['floor'] ? ', ' . $k['floor'] : ''), ', ')) . '" onclick="showKeyQr(this)"><i class="bi bi-qr-code"></i> QR Code</button>'
                    . '<button type="button" class="fp-btn secondary" style="height:30px;width:34px;padding:0;font-size:13px;" title="Delete this key" aria-label="Delete this key" data-id="' . (int) $k['id'] . '" data-name="' . esc($k['name']) . '" onclick="askDeleteKey(this)"><i class="bi bi-trash3"></i></button></div>'];
            }
            return ['raw' => '<form method="post" action="' . base_url('security-dept/keys') . '" style="display:inline">' . csrf_field() . '<input type="hidden" name="key_name" value="' . esc($k['name']) . '"><button type="submit" class="fp-btn secondary" style="height:30px;padding:0 12px;font-size:12px;">Add to list</button></form>'];
        };
        $row = fn($k) => [$k['name'], $k['location'] ?: '—', $k['floor'] ?: '—', $k['uid'] ?: '—', $k['out'] ? 'Borrowed' : 'Available', $k['borrower'] ?: '—', (string) $k['times'], $k['last_at'] ? $this->fmtDate($k['last_at']) : '—', $action($k)];
        $qrKeys = array_values(array_map(fn($k) => ['name' => $k['name'], 'uid' => $k['uid'], 'place' => trim(($k['location'] ?: '') . ($k['floor'] ? ', ' . $k['floor'] : ''), ', ')], array_filter($keys, fn($k) => $k['registered'])));
        $all = array_values($keys);
        $out = array_values(array_filter($all, fn($k) => $k['out']));
        $in = array_values(array_filter($all, fn($k) => !$k['out']));

        return view('security_dept/keys', [
            'title'       => 'List of Keys',
            'pageCss'     => 'safety.css',
            'columns'     => $cols,
            'rows'        => array_map($row, $all),
            'status'      => [
                ['key' => 'k_total', 'label' => 'Total Keys', 'value' => count($all), 'icon' => 'bi-key-fill', 'tone' => 'maroon'],
                ['key' => 'k_avail', 'label' => 'Available', 'value' => count($in), 'icon' => 'bi-check-circle-fill', 'tone' => 'green'],
                ['key' => 'k_out', 'label' => 'Borrowed', 'value' => count($out), 'icon' => 'bi-box-arrow-up-right', 'tone' => 'gold'],
            ],
            'details'     => [
                'k_total' => ['title' => 'All Keys', 'rows' => array_map($row, $all)],
                'k_avail' => ['title' => 'Available Keys', 'rows' => array_map($row, $in)],
                'k_out'   => ['title' => 'Borrowed Keys', 'rows' => array_map($row, $out)],
            ],
            'buildings'   => FireExtinguisherModel::BUILDINGS,
            'floors_json' => $this->jsonForScript(\App\Libraries\FloorPlanCatalog::floorsByBuilding()),
            'qr_keys_json' => $this->jsonForScript($qrKeys),
        ]);
    }

    // PDF of key QR codes on bond paper (same header look as the Information Hub PDF). The browser draws the QR
    // pictures; this adds the university header and lays them out N to a sheet.
    public function keysPdf()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $paperKey = (string) $this->request->getPost('paper');
        $papers = [
            'letter' => ['size' => 'letter', 'w' => 215.9, 'h' => 279.4],
            'a4'     => ['size' => 'a4', 'w' => 210.0, 'h' => 297.0],
            'long'   => ['size' => [0, 0, 612, 936], 'w' => 215.9, 'h' => 330.2], // 8.5 x 13 in
        ];
        $grids = [6 => [2, 3], 8 => [2, 4], 12 => [3, 4], 15 => [3, 5], 20 => [4, 5], 24 => [4, 6], 30 => [5, 6], 35 => [5, 7], 48 => [6, 8]];
        $per = (int) $this->request->getPost('per');
        if (!isset($papers[$paperKey]) || !isset($grids[$per])) return redirect()->to('/security-dept/keys')->with('error', 'Choose the bond paper and how many QR codes per sheet.');
        $paper = $papers[$paperKey];
        [$cols, $rows] = $grids[$per];

        $images = json_decode((string) $this->request->getPost('images'), true) ?: [];
        $only = trim((string) $this->request->getPost('only'));
        $keys = [];
        foreach ((new FacilityKeyModel())->orderBy('key_name', 'ASC')->findAll() as $k) {
            $img = $images[$k['nfc_uid']] ?? '';
            if (!is_string($img) || !preg_match('#^data:image/png;base64,[A-Za-z0-9+/=]+$#', $img)) continue;
            if ($only !== '' && $k['nfc_uid'] !== $only) continue;
            $keys[] = ['name' => $k['key_name'], 'place' => trim(($k['location'] ?: '') . ($k['floor'] ? ', ' . $k['floor'] : ''), ', '), 'img' => $img];
        }
        if (!$keys) return redirect()->to('/security-dept/keys')->with('error', 'No keys with a QR code to print.');

        $margin = 10; // mm
        $headerH = 30; // mm reserved for the header on every sheet
        $cw = ($paper['w'] - $margin * 2) / $cols;
        $ch = ($paper['h'] - $margin * 2 - $headerH - 12) / $rows; // 12 mm safety so a sheet never spills onto a second page
        $qr = min($cw * 0.72, $ch * 0.58);
        $nameFont = max(7, min(11, $qr / 2.6));

        $logoPath = FCPATH . 'images/UBRA LOGO (no background).png';
        $logo = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
        $count = count($keys);
        $header = '<div class="doc-header">' . ($logo ? '<img src="' . $logo . '" class="doc-logo">' : '')
            . '<div class="doc-system-name">FOUNDATION UNIVERSITY — UBRA</div>'
            . '<div class="doc-module-name">Key QR Codes</div>'
            . '<div class="meta">Generated: ' . esc(date('M j, Y g:i A')) . ' &middot; ' . $count . ' key(s)</div></div>';

        $pages = '';
        $chunks = array_chunk($keys, $per);
        foreach ($chunks as $pi => $chunk) {
            $table = '<table class="grid">';
            for ($r = 0; $r < $rows; $r++) {
                $table .= '<tr>';
                for ($c = 0; $c < $cols; $c++) {
                    $k = $chunk[$r * $cols + $c] ?? null;
                    $table .= '<td class="cell">' . ($k
                        ? '<img src="' . $k['img'] . '" class="qr"><div class="n">' . esc($k['name']) . '</div>' . ($k['place'] !== '' ? '<div class="p">' . esc($k['place']) . '</div>' : '')
                        : '') . '</td>';
                }
                $table .= '</tr>';
            }
            $pages .= '<div class="sheet"' . ($pi < count($chunks) - 1 ? ' style="page-break-after:always"' : '') . '>' . $header . $table . '</div>';
        }

        $html = '<html><head><style>
            @page { margin: ' . $margin . 'mm; }
            body { font-family: Helvetica, Arial, sans-serif; color: #222; margin: 0; }
            .doc-header { text-align: center; height: ' . $headerH . 'mm; }
            .doc-logo { display: block; margin: 0 auto 4px; height: 38px; }
            .doc-system-name { font-size: 15px; font-weight: bold; letter-spacing: .04em; color: #800000; }
            .doc-module-name { font-size: 13px; font-weight: bold; margin-top: 2px; }
            .meta { color: #666; font-size: 11px; margin-top: 4px; }
            table.grid { border-collapse: collapse; width: 100%; table-layout: fixed; }
            td.cell { width: ' . $cw . 'mm; height: ' . ($ch - 2) . 'mm; border: 0.2mm dashed #999; text-align: center; vertical-align: middle; padding: 1mm; overflow: hidden; }
            img.qr { width: ' . $qr . 'mm; height: ' . $qr . 'mm; }
            .n { font-weight: bold; font-size: ' . $nameFont . 'px; margin-top: 1mm; }
            .p { font-size: ' . max(5, $nameFont * 0.75) . 'px; color: #555; margin-top: 0.5mm; }
        </style></head><body>' . $pages . '</body></html>';

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper['size'], 'portrait');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="fu-ubra-key-qr-codes-' . date('Y-m-d') . '.pdf"')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setBody($dompdf->output());
    }

    // Remove a key from the list (typo fix). A key that is out on loan can't be removed.
    public function deleteKey(int $id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $model = new FacilityKeyModel();
        $key = $model->find($id);
        if (!$key) return redirect()->to('/security-dept/keys')->with('error', 'That key is no longer on the list.');

        $out = (new KeyBorrowLogModel())->where('status', 'Active')->groupStart()->where('key_id', $id)->orWhere('key_item', $key['key_name'])->groupEnd()->countAllResults();
        if ($key['status'] === 'Borrowed' || $out > 0) {
            return redirect()->to('/security-dept/keys')->with('error', "\"{$key['key_name']}\" is out on loan right now, so it can't be deleted. Delete it after it is returned.");
        }

        $model->delete($id);

        return redirect()->to('/security-dept/keys')->with('success', "Key \"{$key['key_name']}\" deleted.");
    }

    public function storeKey()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $name = trim((string) $this->request->getPost('key_name'));
        $location = trim((string) $this->request->getPost('location'));
        $uid = trim((string) $this->request->getPost('nfc_uid'));
        $floor = trim((string) $this->request->getPost('floor'));
        if ($floor !== '' && !in_array($floor, \App\Libraries\FloorPlanCatalog::floorsByBuilding()[$location] ?? [], true)) $floor = '';
        if ($name === '') return redirect()->to('/security-dept/keys')->with('error', 'Key name is required.');

        $model = new FacilityKeyModel();
        if ($model->where('key_name', $name)->countAllResults() > 0) return redirect()->to('/security-dept/keys')->with('error', "A key named \"{$name}\" is already listed.");
        if ($uid === '') $uid = 'KEY-' . strtoupper(bin2hex(random_bytes(4)));
        if ($model->where('nfc_uid', $uid)->countAllResults() > 0) return redirect()->to('/security-dept/keys')->with('error', "Tag ID {$uid} is already used by another key.");

        $model->insert(['key_name' => $name, 'location' => $location ?: null, 'floor' => $floor !== '' ? $floor : null, 'nfc_uid' => $uid, 'status' => 'Available']);

        return redirect()->to('/security-dept/keys')->with('success', "Key \"{$name}\" added to the list.");
    }

    // File-name label -> campus building it belongs to (used to open plans from the campus map).
    // "Main Building" is left out on purpose: it is not clear which campus building it is.
    private const PLAN_BUILDINGS = [
        'ADMINandARTS'                => 'Administration Building',
        'Agriculture Building'        => 'College of Agriculture and SIE',
        'Arts and Sciences'           => 'College of Art & Sciences Building',
        'Business and Administration' => 'College of Business Economics and Accountancy',
        'EDUC'                        => 'College of Education Building',
        'IT Building'                 => 'LG Sinco Computer Center Building',
        'Kennel Caf'                  => 'University Cafeteria, Bookstore, Sewing',
        'Law Building'                => 'College of Law Building',
        'Main Library'                => 'University Library',
        'Museum'                      => 'Museo de Vicente',
        'SSS'                         => 'Sofia Soller Sinco Hall',
    ];

    private const PLAN_FLOORS = ['Ground Floor' => 1, '2nd Floor' => 2, '3rd Floor' => 3, '4th Floor' => 4];

    // Reads public/Files/Floor Plans and works out each image's building and floor from its file name.
    private function floorPlans(): array
    {
        $dir = FCPATH . 'Files/Floor Plans/';
        $plans = [];
        foreach (glob($dir . '*.{png,jpg,jpeg,PNG,JPG,JPEG}', GLOB_BRACE) ?: [] as $path) {
            $file = basename($path);
            $name = pathinfo($file, PATHINFO_FILENAME);
            $label = trim(explode('_', $name, 2)[0]);
            $lower = strtolower($name);
            $floor = str_contains($lower, 'second') ? '2nd Floor'
                : (str_contains($lower, 'third') ? '3rd Floor'
                : (str_contains($lower, 'fourth') ? '4th Floor' : 'Ground Floor'));

            $plans[] = [
                'file'     => $file,
                'building' => $label === 'ADMINandARTS' ? 'Admin and Arts Building' : $label,
                'key'      => $label,
                'campus'   => self::PLAN_BUILDINGS[$label] ?? null,
                'floor'    => $floor,
                'url'      => site_url('security-dept/plan-image') . '?f=' . rawurlencode($file) . '&v=' . filemtime($path),
            ];
        }

        usort($plans, fn($a, $b) => [$a['building'], self::PLAN_FLOORS[$a['floor']]] <=> [$b['building'], self::PLAN_FLOORS[$b['floor']]]);

        return $plans;
    }

    private function planFileExists(string $file): bool
    {
        foreach ($this->floorPlans() as $p) {
            if ($p['file'] === $file) return true;
        }
        return false;
    }

    private function dateOrNull($v): ?string
    {
        $v = trim((string) $v);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) ? $v : null;
    }

    private function markerJson($m): array
    {
        return [
            'id' => (int) $m['id'], 'plan' => $m['plan_file'], 'type' => $m['equipment_type'], 'label' => $m['label'] ?? '',
            'x' => (float) $m['x_pct'], 'y' => (float) $m['y_pct'], 'status' => $m['status'], 'exp' => $m['expires_on'] ?? '', 'by' => $m['created_by'] ?? '',
        ];
    }

    public function planImage()
    {
        $file = (string) $this->request->getGet('f');
        if (!$this->planFileExists($file)) return $this->response->setStatusCode(404);

        return $this->response
            ->setHeader('Content-Type', 'image/png')
            ->setHeader('Cache-Control', 'private, max-age=86400')
            ->setBody(file_get_contents(\App\Libraries\FloorPlanImage::path($file)));
    }

    public function storeMarker()
    {
        if (!session()->get('isLoggedIn')) return $this->response->setStatusCode(401)->setJSON(['error' => 'Not signed in.']);

        $file = (string) $this->request->getPost('plan_file');
        $type = (string) $this->request->getPost('equipment_type');
        $x = (float) $this->request->getPost('x');
        $y = (float) $this->request->getPost('y');
        if (!$this->planFileExists($file) || !in_array($type, FloorPlanMarkerModel::TYPES, true) || $x < 0 || $x > 100 || $y < 0 || $y > 100) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Invalid marker.']);
        }

        $status = (string) $this->request->getPost('status');
        $model = new FloorPlanMarkerModel();
        $id = $model->insert([
            'plan_file'      => $file,
            'equipment_type' => $type,
            'label'          => trim((string) $this->request->getPost('label')) ?: null,
            'x_pct'          => round($x, 3),
            'y_pct'          => round($y, 3),
            'status'         => in_array($status, ['Working', 'Needs Repair', 'Missing'], true) ? $status : 'Working',
            'expires_on'     => in_array($type, ['Fire Extinguisher', 'Smoke Detector'], true) ? $this->dateOrNull($this->request->getPost('expires_on')) : null,
            'created_by'     => (string) (session()->get('full_name') ?? 'Unknown'),
            'created_at'     => date('Y-m-d H:i:s'),
        ], true);

        return $this->response->setJSON($this->markerJson($model->find($id)));
    }

    public function markerStatus(int $id)
    {
        if (!session()->get('isLoggedIn')) return $this->response->setStatusCode(401)->setJSON(['error' => 'Not signed in.']);

        $status = (string) $this->request->getPost('status');
        if (!in_array($status, ['Working', 'Needs Repair', 'Missing'], true)) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Invalid status.']);
        }
        $model = new FloorPlanMarkerModel();
        if (!$model->find($id)) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found.']);
        $update = ['status' => $status];
        if ($this->request->getPost('expires_on') !== null) $update['expires_on'] = $this->dateOrNull($this->request->getPost('expires_on'));
        $type = (string) $this->request->getPost('equipment_type');
        if (in_array($type, FloorPlanMarkerModel::TYPES, true)) $update['equipment_type'] = $type;
        $finalType = $update['equipment_type'] ?? $model->find($id)['equipment_type'];
        if (!in_array($finalType, ['Fire Extinguisher', 'Smoke Detector'], true)) $update['expires_on'] = null;
        $model->update($id, $update);

        return $this->response->setJSON($this->markerJson($model->find($id)));
    }

    public function deleteMarker(int $id)
    {
        if (!session()->get('isLoggedIn')) return $this->response->setStatusCode(401)->setJSON(['error' => 'Not signed in.']);

        (new FloorPlanMarkerModel())->delete($id);

        return $this->response->setJSON(['deleted' => $id]);
    }

    private function fireSafetyData(): array
    {
        $rows = $this->equipmentRows();
        $cols = ['Type', 'Code', 'Building', 'Floor', 'Status', 'Next Check'];
        $row = fn($r) => [$r['type'], $r['code'], $r['building'], $r['floor'] ?? '—', $this->shownState($r), $this->fmtDate($r['next'])];

        $attention = array_values(array_filter($rows, fn($r) => $this->needsAttention($r)));
        $due = array_values(array_filter($rows, fn($r) => in_array($r['due'], ['Overdue', 'Due in 7 Days', 'Due Soon'], true)));

        // Map: red for any building with something broken or missing, yellow for checks due soon, green otherwise.
        $mapState = [];
        foreach ($rows as $r) {
            $b = $r['building'];
            $mapState[$b] ??= ['red' => null, 'yellow' => null, 'done' => true];
            if ($this->needsAttention($r)) {
                $mapState[$b]['red'][] = $r['floor'] ?: 'Ground Floor';
            } elseif ($r['due'] !== 'OK') {
                $mapState[$b]['yellow'][] = $r['floor'] ?: 'Ground Floor';
            }
        }
        foreach ($mapState as $b => $v) {
            foreach (['red', 'yellow'] as $k) {
                if ($v[$k] !== null) $mapState[$b][$k] = array_values(array_unique($v[$k]));
            }
        }

        $alerts = [];
        foreach ($attention as $r) {
            $alerts[] = ['cols' => [$r['type'], $r['code'], $r['building'], $r['status']], 'level' => 'red', 'src' => $r['src'] ?? null];
        }
        foreach ($due as $r) {
            if (!$this->needsAttention($r)) {
                $alerts[] = ['cols' => [$r['type'], $r['code'], $r['building'], $r['due']], 'level' => 'yellow', 'src' => $r['src'] ?? null];
            }
        }

        $byType = [];
        foreach ($rows as $r) {
            $byType[$r['type']][] = $r;
        }

        return [
            'status' => [
                ['key' => 'fs_total', 'label' => 'Total Equipment', 'value' => count($rows), 'icon' => 'bi-fire', 'tone' => 'maroon'],
                ['key' => 'fs_attention', 'label' => 'Equipment Needing Attention', 'value' => count($attention), 'icon' => 'bi-exclamation-triangle-fill', 'tone' => 'red'],
                ['key' => 'fs_due', 'label' => 'Check Due or Overdue', 'value' => count($due), 'icon' => 'bi-calendar-event', 'tone' => 'amber'],
            ],
            'stat_detail' => [
                'fs_total'     => ['title' => 'All Fire Safety Equipment', 'columns' => $cols, 'rows' => array_map($row, $rows)],
                'fs_attention' => ['title' => 'Equipment Needing Attention', 'columns' => $cols, 'rows' => array_map($row, $attention)],
                'fs_due'       => ['title' => 'Checks Due or Overdue', 'columns' => $cols, 'rows' => array_map($row, $due)],
            ],
            'equipment_by_type' => $byType,
            'equipment_types'   => array_merge(['Fire Extinguisher'], SafetyEquipmentModel::TYPES),
            'map_state_json'    => $this->jsonForScript($mapState),
            'plans_json'        => $this->jsonForScript($this->floorPlans()),
            'markers_json'      => $this->jsonForScript(array_map(fn($m) => $this->markerJson($m), (new FloorPlanMarkerModel())->findAll())),
            'marker_types_json' => $this->jsonForScript(FloorPlanMarkerModel::TYPES),
            'alerts_json'       => $this->jsonForScript($alerts),
            'rows_json'         => $this->jsonForScript($rows),
        ];
    }

    private function guardData(): array
    {
        $logs = (new KeyBorrowLogModel())->getAllWithTrip();
        $active = array_values(array_filter($logs, fn($l) => $l['status'] === 'Active'));
        $today = date('Y-m-d');
        $returnedToday = array_values(array_filter($logs, fn($l) => $l['status'] === 'Returned' && substr((string) $l['scan_out'], 0, 10) === $today));
        $longOut = array_values(array_filter($active, fn($l) => strtotime($l['scan_in']) < strtotime('-8 hours')));

        $trips = (new TravelModel())->getAllWithDetails();
        $gateTrips = array_values(array_filter($trips, fn($t) => in_array($t['status'], ['Approved', 'In Transit'], true)));
        $out = array_values(array_filter($gateTrips, fn($t) => $t['status'] === 'In Transit'));
        $awaiting = array_values(array_filter($gateTrips, fn($t) => $t['status'] === 'Approved'));

        $keyCols = ['Log #', 'Borrower', 'Department', 'Key Item', 'Date Borrowed', 'Returned', 'Status'];
        $keyRow = fn($l) => [$l['log_number'], $l['full_name'], $l['department'], $l['key_item'], $this->fmtDateTime($l['scan_in']), $this->fmtDateTime($l['scan_out']), $l['status'] === 'Active' ? 'Borrowed' : 'Returned'];
        $tripCols = ['Trip ID', 'Requester', 'Destination', 'Assigned Driver', 'Gate Status'];
        $tripRow = fn($t) => [$t['trip_id'], $t['requester_name'] ?? 'Unknown', $t['destination'], ($t['driver_name'] ?? 'Unassigned') . ' / ' . ($t['plate_no'] ?? 'No vehicle'), $t['status'] === 'In Transit' ? 'Vehicle Out' : 'Awaiting Dispatch'];

        $events = [];
        foreach ($logs as $l) {
            $events[] = ['time' => $l['scan_in'], 'guard' => $l['guard_on_duty'] ?: '—', 'action' => "Key {$l['log_number']} ({$l['key_item']}) issued to {$l['full_name']}"];
            if (!empty($l['scan_out'])) {
                $events[] = ['time' => $l['scan_out'], 'guard' => $l['guard_on_duty'] ?: '—', 'action' => "Key {$l['log_number']} ({$l['key_item']}) returned by {$l['full_name']}"];
            }
        }
        foreach ($trips as $t) {
            if (!empty($t['check_in_time'])) {
                $events[] = ['time' => $t['check_in_time'], 'guard' => 'Gate', 'action' => "Vehicle out for trip {$t['trip_id']} to {$t['destination']}"];
            }
            if (!empty($t['check_out_time'])) {
                $events[] = ['time' => $t['check_out_time'], 'guard' => 'Gate', 'action' => "Vehicle returned from trip {$t['trip_id']}"];
            }
        }
        usort($events, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));
        $events = array_slice($events, 0, 100);

        $alerts = [];
        foreach ($longOut as $l) {
            $alerts[] = ['cols' => [$l['full_name'], $l['key_item'], $this->fmtDateTime($l['scan_in']), 'Key out over 8 hours'], 'level' => 'red'];
        }
        foreach ($awaiting as $t) {
            $alerts[] = ['cols' => [$t['requester_name'] ?? 'Unknown', $t['destination'], $t['trip_id'], 'Awaiting dispatch'], 'level' => 'yellow'];
        }

        return [
            'status' => [
                ['key' => 'g_out', 'label' => 'Keys Out', 'value' => count($active), 'icon' => 'bi-key-fill', 'tone' => 'amber'],
                ['key' => 'g_back', 'label' => 'Returned Today', 'value' => count($returnedToday), 'icon' => 'bi-check-circle-fill', 'tone' => 'green'],
                ['key' => 'g_veh', 'label' => 'Vehicles Out', 'value' => count($out), 'icon' => 'bi-truck', 'tone' => 'blue'],
                ['key' => 'g_wait', 'label' => 'Awaiting Dispatch', 'value' => count($awaiting), 'icon' => 'bi-hourglass-split', 'tone' => 'gold'],
            ],
            'stat_detail' => [
                'g_out'  => ['title' => 'Keys Currently Out', 'columns' => $keyCols, 'rows' => array_map($keyRow, $active)],
                'g_back' => ['title' => 'Keys Returned Today', 'columns' => $keyCols, 'rows' => array_map($keyRow, $returnedToday)],
                'g_veh'  => ['title' => 'Vehicles Currently Out', 'columns' => $tripCols, 'rows' => array_map($tripRow, $out)],
                'g_wait' => ['title' => 'Trips Awaiting Dispatch', 'columns' => $tripCols, 'rows' => array_map($tripRow, $awaiting)],
            ],
            'key_logs'     => $logs,
            'active_keys'  => $active,
            'gate_trips'   => $gateTrips,
            'events'       => $events,
            'alerts_json'  => $this->jsonForScript($alerts),
            'key_logs_json' => $this->jsonForScript(array_map(fn($l) => [
                'id' => (int) $l['id'], 'log' => $l['log_number'], 'borrower' => $l['full_name'], 'borrower_id' => $l['borrower_id'],
                'dept' => $l['department'], 'key' => $l['key_item'], 'borrowed' => $this->fmtDateTime($l['scan_in']),
                'returned' => $this->fmtDateTime($l['scan_out']), 'status' => $l['status'] === 'Active' ? 'Borrowed' : 'Returned',
                'guard' => $l['guard_on_duty'] ?: '—',
            ], $logs)),
        ];
    }

    private function inspectionData(): array
    {
        $current = $this->latestInspections();
        $history = (new SafetyInspectionModel())->orderBy('inspected_at', 'DESC')->findAll();

        $cols = ['Building', 'Safety Status', 'Inspected By', 'Date', 'Remarks'];
        $row = fn($i) => [$i['building'], $i['safety_status'] ?? 'Not inspected', $i['inspected_by'] ?? '—', $this->fmtDate($i['inspected_at']), $i['remarks'] ?? '—'];
        $by = fn($s) => array_values(array_filter($current, fn($i) => ($i['safety_status'] ?? null) === $s));
        $notDone = array_values(array_filter($current, fn($i) => $i['safety_status'] === null));

        $mapState = [];
        $alerts = [];
        foreach ($current as $i) {
            $s = $i['safety_status'];
            $mapState[$i['building']] = [
                'red'    => in_array($s, ['Unsafe', 'Needs Attention'], true) ? [] : null,
                'yellow' => $s === null ? [] : null,
                'done'   => $s === 'Safe',
            ];
            if (in_array($s, ['Unsafe', 'Needs Attention'], true)) {
                $alerts[] = ['cols' => [$i['building'], $s, $i['remarks'] ?? '—'], 'level' => 'red'];
            } elseif ($s === null) {
                $alerts[] = ['cols' => [$i['building'], 'Not inspected', '—'], 'level' => 'yellow'];
            }
        }

        return [
            'status' => [
                ['key' => 'i_safe', 'label' => 'Safe', 'value' => count($by('Safe')), 'icon' => 'bi-shield-check', 'tone' => 'green'],
                ['key' => 'i_attn', 'label' => 'Buildings Needing Attention', 'value' => count($by('Needs Attention')), 'icon' => 'bi-exclamation-circle-fill', 'tone' => 'amber'],
                ['key' => 'i_unsafe', 'label' => 'Unsafe', 'value' => count($by('Unsafe')), 'icon' => 'bi-shield-exclamation', 'tone' => 'red'],
                ['key' => 'i_none', 'label' => 'Not Inspected Yet', 'value' => count($notDone), 'icon' => 'bi-hourglass', 'tone' => 'gold'],
            ],
            'stat_detail' => [
                'i_safe'   => ['title' => 'Safe Buildings', 'columns' => $cols, 'rows' => array_map($row, $by('Safe'))],
                'i_attn'   => ['title' => 'Buildings Needing Attention', 'columns' => $cols, 'rows' => array_map($row, $by('Needs Attention'))],
                'i_unsafe' => ['title' => 'Unsafe Buildings', 'columns' => $cols, 'rows' => array_map($row, $by('Unsafe'))],
                'i_none'   => ['title' => 'Buildings Not Inspected Yet', 'columns' => $cols, 'rows' => array_map($row, $notDone)],
            ],
            'current'          => $current,
            'history'          => $history,
            'inspection_month' => date('F Y'),
            'map_state_json'   => $this->jsonForScript($mapState),
            'alerts_json'      => $this->jsonForScript($alerts),
            'current_json'     => $this->jsonForScript($current),
        ];
    }

    public function storeEquipment()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $type = (string) $this->request->getPost('equipment_type');
        $building = trim((string) $this->request->getPost('building'));
        $code = trim((string) $this->request->getPost('code'));
        $back = '/security-dept/fire-safety';

        if ($code === '' || !in_array($building, FireExtinguisherModel::BUILDINGS, true)) {
            return redirect()->to($back)->with('error', 'Code and building are required.');
        }

        if ($type === 'Fire Extinguisher') {
            $model = new FireExtinguisherModel();
            if ($model->where('unit_id', $code)->countAllResults() > 0) {
                return redirect()->to($back)->with('error', "Unit ID \"{$code}\" is already in use.");
            }
            $model->insert([
                'unit_id'         => $code,
                'type'            => $this->request->getPost('ext_type') ?: 'CO2',
                'location'        => $building,
                'floor'           => trim((string) $this->request->getPost('floor')) ?: 'Ground Floor',
                'weight_kg'       => $this->request->getPost('weight_kg') ?: 6.0,
                'last_inspection' => $this->request->getPost('last_checked') ?: null,
                'next_due'        => $this->request->getPost('next_check') ?: null,
                'status'          => 'New',
                'year_acquired'   => date('Y'),
                'installed_on'    => $this->dateOrNull($this->request->getPost('installed_on')),
                'expires_on'      => $this->dateOrNull($this->request->getPost('expires_on')),
            ]);
        } elseif (in_array($type, SafetyEquipmentModel::TYPES, true)) {
            $status = (string) $this->request->getPost('status');
            (new SafetyEquipmentModel())->insert([
                'equipment_type' => $type,
                'code'           => $code,
                'building'       => $building,
                'floor'          => trim((string) $this->request->getPost('floor')) ?: 'Ground Floor',
                'location_note'  => trim((string) $this->request->getPost('location_note')) ?: null,
                'status'         => in_array($status, ['Working', 'Needs Repair', 'Missing'], true) ? $status : 'Working',
                'last_checked'   => $this->request->getPost('last_checked') ?: null,
                'next_check'     => $this->request->getPost('next_check') ?: null,
                'installed_on'   => $this->dateOrNull($this->request->getPost('installed_on')),
                'expires_on'     => $type === 'Smoke Detector' ? $this->dateOrNull($this->request->getPost('expires_on')) : null,
                'remarks'        => trim((string) $this->request->getPost('remarks')) ?: null,
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
        } else {
            return redirect()->to($back)->with('error', 'Choose an equipment type.');
        }

        return redirect()->to($back)->with('success', "{$type} {$code} added.");
    }

    public function storeInspection()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $building = trim((string) $this->request->getPost('building'));
        $status = (string) $this->request->getPost('safety_status');
        if (!in_array($building, FireExtinguisherModel::BUILDINGS, true) || !in_array($status, SafetyInspectionModel::STATUSES, true)) {
            return redirect()->to('/security-dept/inspection')->with('error', 'Choose a building and a safety status.');
        }

        (new SafetyInspectionModel())->insert([
            'building'         => $building,
            'inspection_month' => date('Y-m'),
            'safety_status'    => $status,
            'remarks'          => trim((string) $this->request->getPost('remarks')) ?: null,
            'inspected_by'     => (string) (session()->get('full_name') ?? 'Unknown'),
            'inspected_at'     => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/security-dept/inspection')->with('success', "Inspection saved for {$building}.");
    }
}
