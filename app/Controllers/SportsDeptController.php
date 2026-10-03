<?php

namespace App\Controllers;

use App\Models\BorrowModel;
use App\Models\PersonnelModel;
use App\Models\ReturnModel;
use App\Models\ToolsModel;

// Sports Equipment Monitoring: equipment records, status, location, and borrowing / return monitoring.
// It works on the existing tools + borrow_records tables, limited to the "Sports Equipment" category.
class SportsDeptController extends BaseController
{
    private const CATEGORY = 'Sports Equipment';
    private const SECTIONS = [
        'equipment' => 'Sports Equipment',
        'borrowing' => 'Borrowing and Return',
    ];
    private const CONDITIONS = ['Excellent', 'Good', 'Fair', 'Poor'];

    private function fmtDate($d): string
    {
        return !empty($d) ? date('M d, Y', strtotime($d)) : '—';
    }

    private function me(): string
    {
        return (string) (session()->get('full_name') ?? 'Sports Office');
    }

    private function back(string $path, string $key, string $msg)
    {
        return redirect()->to('/sports-dept/' . $path)->with($key, $msg);
    }

    private function sportsTools(): array
    {
        return (new ToolsModel())->where('category', self::CATEGORY)->where('is_archived', 0)->orderBy('asset_code', 'ASC')->findAll();
    }

    // Storage places for the Location dropdown: the ones already in use plus the usual sports spots.
    private function locationOptions(array $tools): array
    {
        $all = array_merge(['Gymnasium Storage', 'PE Equipment Room', 'Sports Complex'], array_filter(array_column($tools, 'location')));
        $all = array_values(array_unique($all));
        sort($all);

        return $all;
    }

    // People for the Property Custodian dropdown: property / equipment custodians first, then everyone else.
    private function custodianOptions(): array
    {
        $custodians = [];
        $others = [];
        foreach ((new PersonnelModel())->where('is_archived', 0)->orderBy('full_name', 'ASC')->findAll() as $p) {
            $label = $p['full_name'] . ($p['position'] ? ' — ' . $p['position'] : '');
            if (stripos((string) $p['position'], 'custodian') !== false) $custodians[] = ['name' => $p['full_name'], 'label' => $label];
            else $others[] = ['name' => $p['full_name'], 'label' => $label];
        }

        return ['custodians' => $custodians, 'others' => $others];
    }

    // ---------------------------------------------------------------- pages

    public function overview()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $e = $this->equipmentData();
        $b = $this->borrowingData();
        $pick = [
            'sp_total' => ['Sports Equipment', 'e_total', 'bi-trophy-fill', 'maroon', $e],
            'sp_avail' => ['Available', 'e_avail', 'bi-check-circle-fill', 'green', $e],
            'sp_out'   => ['Currently Borrowed', 'b_out', 'bi-box-arrow-up-right', 'gold', $b],
            'sp_over'  => ['Overdue Returns', 'b_over', 'bi-alarm-fill', 'red', $b],
            'sp_attn'  => ['Needs Attention', 'e_attn', 'bi-exclamation-triangle-fill', 'red', $e],
            'sp_back'  => ['Returned This Month', 'b_back', 'bi-arrow-return-left', 'blue', $b],
        ];
        $stats = [];
        $details = [];
        foreach ($pick as $key => [$label, $src, $icon, $tone, $data]) {
            $d = $data['stat_detail'][$src];
            $stats[] = ['key' => $key, 'label' => $label, 'value' => count($d['rows']), 'icon' => $icon, 'tone' => $tone];
            $details[$key] = $d;
        }

        return view('sports_dept/dashboard', [
            'title'    => 'Sports Equipment Monitoring',
            'pageCss'  => 'safety.css',
            'stats'    => $stats,
            'details'  => $details,
            'sections' => [
                ['label' => 'Sports Equipment', 'url' => 'sports-dept/equipment', 'icon' => 'bi-trophy', 'desc' => 'Equipment records, equipment status, and where each item is kept.'],
                ['label' => 'Borrowing and Return', 'url' => 'sports-dept/borrowing', 'icon' => 'bi-arrow-left-right', 'desc' => 'Who has which equipment, when it is due back, and what was returned.'],
            ],
        ]);
    }

    public function status()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $cards = [];
        foreach ([$this->equipmentData(), $this->borrowingData()] as $d) {
            foreach ($d['status'] as $card) {
                $detail = $d['stat_detail'][$card['key']];
                $cards[] = $card + ['title' => $detail['title'], 'columns' => $detail['columns'], 'rows' => $detail['rows']];
            }
        }

        return view('facilities/status', [
            'title'      => 'Sports Equipment Monitoring Status',
            'page_title' => 'Sports Equipment Monitoring Status',
            'pageCss'    => 'safety.css',
            'cards'      => $cards,
        ]);
    }

    public function index(string $section = 'equipment')
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');
        if (!isset(self::SECTIONS[$section])) return redirect()->to('/sports-dept');

        $data = $section === 'borrowing' ? $this->borrowingData() : $this->equipmentData();
        $stat = (string) $this->request->getGet('stat');

        return view('sports_dept/index', $data + [
            'title'       => self::SECTIONS[$section],
            'section'     => $section,
            'pageCss'     => 'safety.css',
            'active_stat' => isset($data['stat_detail'][$stat]) ? $stat : null,
            'stat_rows'   => $data['stat_detail'][$stat] ?? null,
        ]);
    }

    // ------------------------------------------------------------- data

    // Open (not yet returned) borrow records for sports equipment, keyed by tool id.
    private function openBorrows(array $toolIds): array
    {
        if (!$toolIds) return [];
        $open = [];
        foreach ((new BorrowModel())->whereIn('tool_id', $toolIds)->where('status', 'Borrowed')->where('is_archived', 0)->orderBy('id', 'DESC')->findAll() as $b) {
            $open[$b['tool_id']] ??= $b;
        }
        return $open;
    }

    private function equipmentData(): array
    {
        $tools = $this->sportsTools();
        $open = $this->openBorrows(array_column($tools, 'id'));
        $today = date('Y-m-d');
        $isOverdue = fn($t) => isset($open[$t['id']]) && !empty($open[$t['id']]['expected_return']) && $open[$t['id']]['expected_return'] < $today;
        $attn = fn($t) => in_array($t['condition_status'], ['Poor'], true) || in_array($t['availability'], ['Maintenance', 'Disposal'], true) || $isOverdue($t);

        $recCols = ['Code', 'Equipment', 'Location', 'Condition', 'Availability', 'Custodian'];
        $recRow = fn($t) => [$t['asset_code'] ?: '—', $t['asset_name'], $t['location'] ?: '—', $t['condition_status'] ?: '—', $t['availability'], $t['custodian'] ?: '—'];

        $statCols = ['Code', 'Equipment', 'Condition', 'Availability', 'Currently With', 'Due Back', 'Flag'];
        $statRow = function ($t) use ($open, $isOverdue) {
            $b = $open[$t['id']] ?? null;
            $flag = $isOverdue($t) ? 'Overdue' : ($t['condition_status'] === 'Poor' ? 'Needs Repair' : ($t['availability'] === 'Maintenance' ? 'Maintenance' : 'OK'));
            return [$t['asset_code'] ?: '—', $t['asset_name'], $t['condition_status'] ?: '—', $t['availability'], $b ? $b['borrower'] : '—', $b ? $this->fmtDate($b['expected_return']) : '—', $flag];
        };

        // Location tab: one row per storage place, with what is in it right now.
        $byLoc = [];
        foreach ($tools as $t) {
            $loc = $t['location'] ?: 'No location set';
            $byLoc[$loc]['items'][] = $t['asset_name'];
            $byLoc[$loc]['total'] = ($byLoc[$loc]['total'] ?? 0) + 1;
            $byLoc[$loc]['avail'] = ($byLoc[$loc]['avail'] ?? 0) + ($t['availability'] === 'Available' ? 1 : 0);
            $byLoc[$loc]['out'] = ($byLoc[$loc]['out'] ?? 0) + ($t['availability'] === 'Borrowed' ? 1 : 0);
        }
        ksort($byLoc);
        $locCols = ['Location', 'Items Kept Here', 'Total', 'In Place', 'Out on Loan', 'Equipment'];
        $locRows = [];
        foreach ($byLoc as $loc => $v) {
            $locRows[] = [$loc, count($v['items']) . ' item' . (count($v['items']) === 1 ? '' : 's'), (string) $v['total'], (string) $v['avail'], (string) $v['out'], implode(', ', $v['items'])];
        }

        $avail = array_values(array_filter($tools, fn($t) => $t['availability'] === 'Available'));
        $borrowed = array_values(array_filter($tools, fn($t) => $t['availability'] === 'Borrowed'));
        $attention = array_values(array_filter($tools, $attn));
        $overdue = array_values(array_filter($tools, $isOverdue));

        $alerts = [];
        foreach ($tools as $t) {
            $name = ($t['asset_code'] ? $t['asset_code'] . ' — ' : '') . $t['asset_name'];
            if ($isOverdue($t)) $alerts[] = ['cols' => [$name, 'Return', 'Overdue'], 'level' => 'red'];
            if ($t['condition_status'] === 'Poor') $alerts[] = ['cols' => [$name, 'Condition', 'Poor'], 'level' => 'red'];
            elseif ($t['condition_status'] === 'Fair') $alerts[] = ['cols' => [$name, 'Condition', 'Fair'], 'level' => 'yellow'];
            if ($t['availability'] === 'Maintenance') $alerts[] = ['cols' => [$name, 'Availability', 'Maintenance'], 'level' => 'yellow'];
        }

        return [
            'status' => [
                ['key' => 'e_total', 'label' => 'Total Equipment', 'value' => count($tools), 'icon' => 'bi-trophy-fill', 'tone' => 'maroon'],
                ['key' => 'e_avail', 'label' => 'Available', 'value' => count($avail), 'icon' => 'bi-check-circle-fill', 'tone' => 'green'],
                ['key' => 'e_attn', 'label' => 'Needs Attention', 'value' => count($attention), 'icon' => 'bi-exclamation-triangle-fill', 'tone' => 'red'],
            ],
            'stat_detail' => [
                'e_total'    => ['title' => 'All Sports Equipment', 'columns' => $recCols, 'rows' => array_map($recRow, $tools)],
                'e_avail'    => ['title' => 'Available Equipment', 'columns' => $recCols, 'rows' => array_map($recRow, $avail)],
                'e_borrowed' => ['title' => 'Equipment On Loan', 'columns' => $statCols, 'rows' => array_map($statRow, $borrowed)],
                'e_attn'     => ['title' => 'Equipment Needing Attention', 'columns' => $statCols, 'rows' => array_map($statRow, $attention)],
            ],
            'tabs' => [
                ['key' => 'records', 'label' => 'Equipment Records', 'columns' => $recCols, 'rows' => array_map($recRow, $tools), 'empty' => 'No sports equipment recorded yet.'],
                ['key' => 'status', 'label' => 'Equipment Status', 'columns' => $statCols, 'rows' => array_map($statRow, $tools), 'empty' => 'No sports equipment recorded yet.'],
                ['key' => 'location', 'label' => 'Location', 'columns' => $locCols, 'rows' => $locRows, 'empty' => 'No locations yet.'],
            ],
            'alerts_json'  => $this->jsonForScript($alerts),
            'alert_cols'   => ['Equipment', 'What', 'Status'],
            'alert_titles' => ['red' => 'Needs attention now', 'yellow' => 'Keep an eye on'],
            'conditions' => self::CONDITIONS,
            'locations'  => $this->locationOptions($tools),
            'custodians' => $this->custodianOptions(),
            // overview needs these two from the borrowing side as well
            'overdue_count' => count($overdue),
        ];
    }

    private function borrowingData(): array
    {
        $tools = [];
        foreach ($this->sportsTools() as $t) $tools[$t['id']] = $t;
        $allTools = [];
        foreach ((new ToolsModel())->where('category', self::CATEGORY)->findAll() as $t) $allTools[$t['id']] = $t;

        $borrows = $allTools ? (new BorrowModel())->whereIn('tool_id', array_keys($allTools))->where('is_archived', 0)->orderBy('id', 'DESC')->findAll() : [];
        $returns = [];
        foreach ((new ReturnModel())->orderBy('id', 'DESC')->findAll() as $r) $returns[$r['borrow_id']] ??= $r;

        $today = date('Y-m-d');
        $month = date('Y-m');
        $name = fn($b) => ($allTools[$b['tool_id']]['asset_code'] ? $allTools[$b['tool_id']]['asset_code'] . ' — ' : '') . $allTools[$b['tool_id']]['asset_name'];
        $isOver = fn($b) => $b['status'] === 'Borrowed' && !empty($b['expected_return']) && $b['expected_return'] < $today;

        $open = array_values(array_filter($borrows, fn($b) => $b['status'] === 'Borrowed'));
        $over = array_values(array_filter($open, $isOver));
        $monthBorrowed = array_values(array_filter($borrows, fn($b) => substr((string) $b['borrowed_date'], 0, 7) === $month));
        $backMonth = array_values(array_filter($borrows, fn($b) => $b['status'] === 'Returned' && isset($returns[$b['id']]) && substr((string) $returns[$b['id']]['return_date'], 0, 7) === $month));

        $openCols = ['Equipment', 'Borrower', 'Department', 'Borrowed', 'Due Back', 'Status'];
        $openRow = fn($b) => [$name($b), $b['borrower'] ?: '—', $b['department'] ?: '—', $this->fmtDate($b['borrowed_date']), $this->fmtDate($b['expected_return']), $isOver($b) ? 'Overdue' : 'Borrowed'];
        $histCols = ['Equipment', 'Borrower', 'Department', 'Borrowed', 'Due Back', 'Returned', 'Condition on Return', 'Status'];
        $histRow = fn($b) => [
            $name($b), $b['borrower'] ?: '—', $b['department'] ?: '—', $this->fmtDate($b['borrowed_date']), $this->fmtDate($b['expected_return']),
            isset($returns[$b['id']]) ? $this->fmtDate($returns[$b['id']]['return_date']) : '—', isset($returns[$b['id']]) ? ($returns[$b['id']]['condition_status'] ?: '—') : '—',
            $isOver($b) ? 'Overdue' : $b['status'],
        ];
        $plainCols = ['Equipment', 'Borrower', 'Department', 'Borrowed', 'Due Back', 'Status'];
        $plainRow = fn($b) => [$name($b), $b['borrower'] ?: '—', $b['department'] ?: '—', $this->fmtDate($b['borrowed_date']), $this->fmtDate($b['expected_return']), $isOver($b) ? 'Overdue' : $b['status']];

        $alerts = [];
        foreach ($open as $b) {
            if ($isOver($b)) $alerts[] = ['cols' => [$name($b), $b['borrower'] ?: '—', 'Overdue'], 'level' => 'red'];
            elseif (!empty($b['expected_return']) && $b['expected_return'] <= date('Y-m-d', strtotime('+2 days'))) $alerts[] = ['cols' => [$name($b), $b['borrower'] ?: '—', 'Due Soon'], 'level' => 'yellow'];
        }

        return [
            'status' => [
                ['key' => 'b_out', 'label' => 'Currently Borrowed', 'value' => count($open), 'icon' => 'bi-box-arrow-up-right', 'tone' => 'gold'],
                ['key' => 'b_over', 'label' => 'Overdue Returns', 'value' => count($over), 'icon' => 'bi-alarm-fill', 'tone' => 'red'],
                ['key' => 'b_month', 'label' => 'Borrowed This Month', 'value' => count($monthBorrowed), 'icon' => 'bi-calendar-month', 'tone' => 'maroon'],
                ['key' => 'b_back', 'label' => 'Returned This Month', 'value' => count($backMonth), 'icon' => 'bi-arrow-return-left', 'tone' => 'blue'],
            ],
            'stat_detail' => [
                'b_out'   => ['title' => 'Currently Borrowed', 'columns' => $plainCols, 'rows' => array_map($plainRow, $open)],
                'b_over'  => ['title' => 'Overdue Returns', 'columns' => $plainCols, 'rows' => array_map($plainRow, $over)],
                'b_month' => ['title' => 'Borrowed This Month', 'columns' => $plainCols, 'rows' => array_map($plainRow, $monthBorrowed)],
                'b_back'  => ['title' => 'Returned This Month', 'columns' => $plainCols, 'rows' => array_map($plainRow, $backMonth)],
            ],
            'tabs' => [
                ['key' => 'out', 'label' => 'Currently Borrowed', 'columns' => $openCols, 'rows' => array_map($openRow, $open), 'empty' => 'Nothing is out on loan right now.'],
                ['key' => 'history', 'label' => 'Borrowing History', 'columns' => $histCols, 'rows' => array_map($histRow, $borrows), 'empty' => 'No borrowing recorded yet.'],
            ],
            'alerts_json'  => $this->jsonForScript($alerts),
            'alert_cols'   => ['Equipment', 'Borrower', 'Status'],
            'alert_titles' => ['red' => 'Overdue returns', 'yellow' => 'Due back within 2 days'],
        ];
    }

    // --------------------------------------------------------- saving

    public function storeEquipment()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $name = trim((string) $this->request->getPost('asset_name'));
        $code = trim((string) $this->request->getPost('asset_code'));
        $cond = (string) $this->request->getPost('condition_status');
        $location = trim((string) $this->request->getPost('location'));
        if ($location === '__other') $location = trim((string) $this->request->getPost('location_other'));
        if ($name === '' || $code === '') return $this->back('equipment', 'error', 'Equipment name and code are required.');

        $model = new ToolsModel();
        if ($model->where('asset_code', $code)->countAllResults() > 0) return $this->back('equipment', 'error', "Code {$code} is already in use.");

        $model->insert([
            'asset_name'       => $name,
            'asset_code'       => $code,
            'category'         => self::CATEGORY,
            'location'         => $location ?: null,
            'custodian'        => trim((string) $this->request->getPost('custodian')) ?: null,
            'condition_status' => in_array($cond, self::CONDITIONS, true) ? $cond : 'Good',
            'availability'     => 'Available',
            'unit'             => 'pcs',
            'is_facilities'    => 0,
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->back('equipment', 'success', "{$name} added.");
    }
}
