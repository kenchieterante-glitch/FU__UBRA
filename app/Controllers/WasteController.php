<?php

namespace App\Controllers;

use App\Models\FireExtinguisherModel;
use App\Models\NotificationModel;

// Waste Watch: the supervisor weighs the segregated waste (non-biodegradable, biodegradable, hazardous)
// at every campus waste station at the end of the day, and each weigh-in is logged here.
class WasteController extends BaseController
{
    public const HAZARD_LIMIT_KG = 3.0; // one day's hazardous waste at a station above this raises an alert

    private function me(): string
    {
        return (string) (session()->get('full_name') ?? 'Supervisor');
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $db = db_connect();
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $weekFrom = date('Y-m-d', strtotime('-6 days'));
        $monthFrom = date('Y-m-d', strtotime('-29 days'));

        $stations = $db->table('waste_stations')->where('is_archived', 0)->orderBy('building', 'ASC')->get()->getResultArray();
        $logs = $db->table('waste_logs')->where('log_date >=', $monthFrom)->orderBy('log_date', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();
        // A year of weigh-ins, for the Segregation Summary's Date / Month filter.
        $allLogs = $db->table('waste_logs')->where('log_date >=', date('Y-m-d', strtotime('-365 days')))->orderBy('log_date', 'DESC')->get()->getResultArray();

        $byStation = [];
        foreach ($logs as $l) $byStation[$l['station_id']][] = $l;
        $stationNames = [];
        foreach ($stations as $s) $stationNames[$s['id']] = $s;

        $rows = [];
        $totals = ['today' => 0.0, 'haz_today' => 0.0, 'logged' => 0, 'pending' => 0, 'overdue' => 0];
        foreach ($stations as $s) {
            $list = $byStation[$s['id']] ?? [];
            $todayLog = null; $last = null;
            $wk = ['nb' => 0.0, 'b' => 0.0, 'h' => 0.0];
            $mo = ['nb' => 0.0, 'b' => 0.0, 'h' => 0.0];
            foreach ($list as $l) {
                if ($l['log_date'] === $today) $todayLog = $l;
                if ($last === null) $last = $l;
                $mo['nb'] += (float) $l['non_bio_kg']; $mo['b'] += (float) $l['bio_kg']; $mo['h'] += (float) $l['hazardous_kg'];
                if ($l['log_date'] >= $weekFrom) { $wk['nb'] += (float) $l['non_bio_kg']; $wk['b'] += (float) $l['bio_kg']; $wk['h'] += (float) $l['hazardous_kg']; }
            }
            if ($todayLog) {
                $status = 'Logged';
                $totals['logged']++;
                $totals['today'] += (float) $todayLog['non_bio_kg'] + (float) $todayLog['bio_kg'] + (float) $todayLog['hazardous_kg'];
                $totals['haz_today'] += (float) $todayLog['hazardous_kg'];
            } elseif ($last === null || $last['log_date'] < $yesterday) {
                $status = 'Overdue';
                $totals['overdue']++;
            } else {
                $status = 'Pending';
                $totals['pending']++;
            }
            $rows[] = [
                'id'       => (int) $s['id'],
                'building' => $s['building'],
                'location' => $s['location'] ?: '—',
                'status'   => $status,
                'high_haz' => $todayLog && (float) $todayLog['hazardous_kg'] >= self::HAZARD_LIMIT_KG,
                'today'    => $todayLog ? ['nb' => (float) $todayLog['non_bio_kg'], 'b' => (float) $todayLog['bio_kg'], 'h' => (float) $todayLog['hazardous_kg'], 'by' => $todayLog['checked_by'], 'notes' => $todayLog['notes']] : null,
                'last'     => $last ? $last['log_date'] : null,
                'week'     => $wk,
                'month'    => $mo,
            ];
        }

        $logRows = array_map(fn($l) => [
            'date'     => $l['log_date'],
            'building' => $stationNames[$l['station_id']]['building'] ?? '—',
            'nb'       => (float) $l['non_bio_kg'],
            'b'        => (float) $l['bio_kg'],
            'h'        => (float) $l['hazardous_kg'],
            'by'       => $l['checked_by'] ?: '—',
            'notes'    => $l['notes'] ?: '—',
        ], $logs);

        $role = strtolower((string) session()->get('role'));
        $org = $role === 'facilities' ? 'Facilities Administration and General Services' : 'Unified Buildings Resources Administration';

        return view('facilities/waste', [
            'title'     => 'Waste Watch',
            'pageCss'   => 'safety.css',
            'org'       => $org,
            'today'     => $today,
            'rows'      => $rows,
            'totals'    => $totals,
            'log_rows'  => $logRows,
            'all_logs'  => array_map(fn($l) => [$l['log_date'], (int) $l['station_id'], (float) $l['non_bio_kg'], (float) $l['bio_kg'], (float) $l['hazardous_kg']], $allLogs),
            'station_names' => array_column($stationNames, 'building', 'id'),
            'buildings' => FireExtinguisherModel::BUILDINGS,
            'limit'     => self::HAZARD_LIMIT_KG,
            'flash_success' => session()->getFlashdata('success'),
            'flash_error'   => session()->getFlashdata('error'),
        ]);
    }

    // Saves (or corrects) one station's end-of-day weigh-in.
    public function log()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $db = db_connect();
        $stationId = (int) $this->request->getPost('station_id');
        $date = (string) $this->request->getPost('log_date') ?: date('Y-m-d');
        $station = $db->table('waste_stations')->where('id', $stationId)->get()->getRowArray();

        if (!$station || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date > date('Y-m-d')) {
            return redirect()->to('/facilities/waste')->with('error', 'Pick a station and a date that is today or earlier.');
        }

        $num = fn($k) => max(0, round((float) $this->request->getPost($k), 2));
        $nb = $num('non_bio_kg'); $b = $num('bio_kg'); $h = $num('hazardous_kg');
        $data = [
            'non_bio_kg' => $nb, 'bio_kg' => $b, 'hazardous_kg' => $h,
            'checked_by' => $this->me(),
            'notes'      => trim((string) $this->request->getPost('notes')) ?: null,
        ];

        $existing = $db->table('waste_logs')->where('station_id', $stationId)->where('log_date', $date)->get()->getRowArray();
        if ($existing) {
            $db->table('waste_logs')->where('id', $existing['id'])->update($data);
        } else {
            $db->table('waste_logs')->insert($data + ['station_id' => $stationId, 'log_date' => $date, 'created_at' => date('Y-m-d H:i:s')]);
        }

        if ($h >= self::HAZARD_LIMIT_KG) {
            (new NotificationModel())->insert([
                'category'    => 'Hazardous Waste Alert',
                'description' => "{$station['building']}: {$h} kg of hazardous waste weighed on " . date('M d, Y', strtotime($date)) . ' — schedule a pick-up.',
                'recipient'   => 'Facilities',
                'priority'    => 'CRITICAL',
                'status'      => 'Notified',
                'channel'     => 'system',
                'is_read'     => 0,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to('/facilities/waste')->with('success', "Weigh-in saved for {$station['building']}.");
    }
}
