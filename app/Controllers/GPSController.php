<?php

namespace App\Controllers;

use App\Libraries\TraccarClient;
use App\Libraries\TraccarSync;
use App\Models\GPSModel;
use App\Models\VehicleModel;

class GPSController extends BaseController
{
    protected $gpsModel;
    protected $vehicleModel;
    protected $session;

    public function __construct()
    {
        $this->gpsModel     = new GPSModel();
        $this->vehicleModel = new VehicleModel();
        $this->session      = \Config\Services::session();
    }

    public function index()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        // Pull fresh data from the physical trackers (via Traccar) into
        // gps_logs / vehicles.gps_status first, so everything below reads current values.
        $live = (new TraccarSync())->run();

        // gps_status lives on the vehicles table itself — that's the single source of
        // truth for online/offline, matching what Vehicle Management shows for the same field.
        $vehicles = $this->vehicleModel->where('is_archived', 0)->findAll();
        $gpsLogs  = $this->gpsModel->getLatestPerVehicle();

        $gpsMap = [];
        foreach ($gpsLogs as $log) {
            $gpsMap[$log['vehicle_id']] = $log;
        }

        $fleet = [];
        foreach ($vehicles as $v) {
            $gps     = $gpsMap[$v['id']] ?? null;
            $fleet[] = array_merge($v, [
                'gps_status'    => $v['gps_status']        ?? 'Offline',
                'latitude'      => $gps['latitude']        ?? null,
                'longitude'     => $gps['longitude']       ?? null,
                // Speed isn't stored in gps_logs — only known while Traccar is answering.
                'speed'         => $live[(int) $v['id']]['speed_kmh'] ?? 0,
                'signal'        => $gps['signal_strength'] ?? '—',
                'last_location' => '—',
                'logged_at'     => $gps['logged_at']       ?? null,
                'device_id'     => $gps['device_id']       ?? null,
                // map vehicle fields to expected view keys
                'model'         => $v['vehicle_name']      ?? '—',
                'driver_name'   => $v['driver']            ?? 'Unassigned',
                'inspection_status' => $v['inspection_status'] ?? 'Pending',
            ]);
        }

        $online  = count(array_filter($fleet, fn($v) => $v['gps_status'] === 'Online'));
        $offline = count($fleet) - $online;

        $data = [
            'title'         => 'GPS Tracker',
            'fleet'         => $fleet,
            'online_count'  => $online,
            'offline_count' => $offline,
            'transit_count' => count(array_filter($fleet, fn($v) => ($v['availability'] ?? '') === 'In Use')),
            'maint_count'   => count(array_filter($fleet, fn($v) => ($v['availability'] ?? '') === 'Maintenance')),
            'total'         => count($fleet),
            'vehicles'      => $vehicles,
            'flash_success' => $this->session->getFlashdata('success'),
        ];

        return view('gps/index', $data);
    }

    /**
     * Live positions of the whole fleet, for the GPS Tracker page's map
     * (polled every few seconds while the map is open). Syncs from Traccar
     * first, then prefers the tracker's own latest fix over the last
     * logged one, falling back to gps_logs for vehicles with no tracker.
     */
    public function fleet()
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $live = (new TraccarSync())->run();

        $logs = [];
        foreach ($this->gpsModel->getLatestPerVehicle() as $log) {
            $logs[$log['vehicle_id']] = $log;
        }

        $out = [];
        foreach ($this->vehicleModel->where('is_archived', 0)->findAll() as $v) {
            $p   = $live[(int) $v['id']] ?? null;
            $log = $logs[$v['id']] ?? null;

            $lat = $p['latitude']  ?? ($log['latitude']  ?? null);
            $lng = $p['longitude'] ?? ($log['longitude'] ?? null);

            $out[] = [
                'id'           => (int) $v['id'],
                'plate'        => $v['plate_no'] ?? '—',
                'name'         => $v['vehicle_name'] ?? '—',
                'availability' => $v['availability'] ?? 'Available',
                'online'       => ($v['gps_status'] ?? 'Offline') === 'Online',
                'lat'          => $lat !== null ? (float) $lat : null,
                'lng'          => $lng !== null ? (float) $lng : null,
                'speed'        => $p['speed_kmh'] ?? 0,
                'updated'      => $p['fix_time'] ?? ($log['logged_at'] ?? null),
            ];
        }

        return $this->response->setJSON(['vehicles' => $out, 'server_time' => date('c')]);
    }

    public function getVehicle($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $live = (new TraccarSync())->run((int) $id);

        // Joined the same way as Vehicle Management's own list so this popup
        // agrees with it (driver_name/department_name resolved from their
        // FK ids, not read off columns that don't exist on vehicles).
        $vehicle = $this->vehicleModel
            ->select('vehicles.*, d.name as department_name, p.full_name as driver_name')
            ->join('departments d', 'd.id = vehicles.department_id', 'left')
            ->join('personnel p', 'p.id = vehicles.driver_id', 'left')
            ->where('vehicles.id', $id)
            ->first();
        if (!$vehicle) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);

        $latestGPS = $this->gpsModel->where('vehicle_id', $id)->orderBy('id', 'DESC')->first();
        $recentPings = array_map(fn($p) => [
            'loggedAt' => $p['logged_at'],
            'coords'   => ($p['latitude'] !== null && $p['longitude'] !== null) ? "{$p['latitude']}, {$p['longitude']}" : '—',
            'signal'   => $p['signal_strength'] ?? '—',
            'status'   => $p['status'] ?? '—',
        ], $this->gpsModel->getHistory((int) $id, 5));

        return $this->response->setJSON(array_merge($vehicle, [
            'gps_status'    => $vehicle['gps_status']        ?? 'Offline',
            'latitude'      => $latestGPS['latitude']        ?? null,
            'longitude'     => $latestGPS['longitude']       ?? null,
            'speed'         => $live[(int) $id]['speed_kmh'] ?? 0,
            'signal'        => $latestGPS['signal_strength'] ?? 0,
            'last_location' => '—',
            'logged_at'     => $latestGPS['logged_at']       ?? null,
            'device_id'     => $latestGPS['device_id']       ?? 'N/A',
            'model'         => $vehicle['vehicle_name']      ?? '—',
            'driver_name'   => $vehicle['driver_name']       ?? 'Unassigned',
            'recent_pings'  => $recentPings,
        ]));
    }

    /**
     * The vehicle's travel route for a date range — every logged fix,
     * oldest first, for drawing as a path on the map (unlike getVehicle()'s
     * "Recent Pings", which is just the latest 5 for a quick-glance table).
     * ?from=YYYY-MM-DD&to=YYYY-MM-DD (defaults to the last 24 hours).
     */
    public function getRoute($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $vehicle = $this->vehicleModel->find($id);
        if (!$vehicle) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);

        $fromInput = $this->request->getGet('from');
        $toInput   = $this->request->getGet('to');
        // A plain "YYYY-MM-DD" from a <input type="date"> means the whole
        // day — start of day for "from", end of day for "to" — not the
        // literal midnight instant a bare date would otherwise compare as.
        // A value with a time part ("YYYY-MM-DDTHH:MM" from a datetime-local
        // input) is used as the exact instant instead.
        $hasTime = fn($s) => (bool) preg_match('/\d{1,2}:\d{2}/', (string) $s);
        $from = $fromInput
            ? ($hasTime($fromInput) ? date('Y-m-d H:i:00', strtotime($fromInput)) : date('Y-m-d 00:00:00', strtotime($fromInput)))
            : date('Y-m-d H:i:s', strtotime('-24 hours'));
        $to = $toInput
            ? ($hasTime($toInput) ? date('Y-m-d H:i:59', strtotime($toInput)) : date('Y-m-d 23:59:59', strtotime($toInput)))
            : date('Y-m-d H:i:s');

        // Prefer the tracker's full history from Traccar when the vehicle is
        // linked to one; gps_logs only holds the latest fix per sync.
        $traccarPoints = null;
        $totalPoints   = 0;
        if (!empty($vehicle['gps_device_id'])) {
            // A range that has fully passed never changes, so its (slow to
            // fetch) result is cached; ranges reaching "now" are always live.
            $cacheable = strtotime($to) < time() - 3600;
            $cacheKey  = 'route_' . md5($vehicle['gps_device_id'] . '|' . $from . '|' . $to);
            $cached    = $cacheable ? cache($cacheKey) : null;

            if (is_array($cached)) {
                [$traccarPoints, $totalPoints] = $cached;
            } else {
                $raw = (new \App\Libraries\TraccarClient())
                    ->routeHistory($vehicle['gps_device_id'], strtotime($from), strtotime($to));
                if ($raw !== null) {
                    $totalPoints   = count($raw);
                    $traccarPoints = $this->thinRoute($raw);
                    if ($cacheable) {
                        cache()->save($cacheKey, [$traccarPoints, $totalPoints], 86400);
                    }
                }
            }
        }

        if ($traccarPoints !== null) {
            $points = array_map(fn($p) => $p + ['signal' => '—', 'status' => $p['speed'] . ' km/h'], $traccarPoints);
            return $this->response->setJSON([
                'vehicle_id' => (int) $id,
                'plate_no'   => $vehicle['plate_no'] ?? '—',
                'from'       => $from,
                'to'         => $to,
                'count'      => count($points),
                'total'      => $totalPoints,
                'points'     => $points,
            ]);
        }

        // Why the tracker's own history wasn't used — shown when the range comes back empty.
        $client = new TraccarClient();
        $note = empty($vehicle['gps_device_id'])
            ? 'This vehicle is not linked to a GPS tracker yet, so only locally saved pings can be shown. Set its GPS Device ID in Vehicle Management.'
            : (!$client->isConfigured()
                ? 'The Traccar connection is not set up on this server (TRACCAR_URL / TRACCAR_USER / TRACCAR_PASS in .env), so the tracker history cannot be loaded.'
                : 'Traccar did not answer, so the tracker history cannot be loaded right now.');

        $logs = $this->gpsModel->getHistoryInRange((int) $id, $from, $to);

        $points = array_values(array_filter(array_map(fn($p) => [
            'lat'      => $p['latitude']  !== null ? (float) $p['latitude']  : null,
            'lng'      => $p['longitude'] !== null ? (float) $p['longitude'] : null,
            'loggedAt' => $p['logged_at'],
            'signal'   => $p['signal_strength'] ?? '—',
            'status'   => $p['status'] ?? '—',
        ], $logs), fn($p) => $p['lat'] !== null && $p['lng'] !== null));

        return $this->response->setJSON([
            'vehicle_id' => (int) $id,
            'plate_no'   => $vehicle['plate_no'] ?? '—',
            'from'       => $from,
            'to'         => $to,
            'count'      => count($points),
            'points'     => $points,
            'note'       => $note,
        ]);
    }

    private const ROUTE_MIN_GAP_METERS = 40;
    private const ROUTE_MAX_POINTS     = 250;

    /**
     * Thins a long position history for drawing: drops fixes within
     * ROUTE_MIN_GAP_METERS of the last kept one (a parked vehicle pings the
     * same spot over and over), then evenly samples down to
     * ROUTE_MAX_POINTS. The first and last fix are always kept, so the
     * path's start/end and shape are unchanged — only the redundant points go.
     */
    private function thinRoute(array $points): array
    {
        $n = count($points);
        if ($n <= 2) return $points;

        $kept = [$points[0]];
        for ($i = 1; $i < $n - 1; $i++) {
            $last = end($kept);
            if ($this->metersBetween($last['lat'], $last['lng'], $points[$i]['lat'], $points[$i]['lng']) >= self::ROUTE_MIN_GAP_METERS) {
                $kept[] = $points[$i];
            }
        }
        $kept[] = $points[$n - 1];

        $k = count($kept);
        if ($k > self::ROUTE_MAX_POINTS) {
            $sampled = [];
            $step = ($k - 1) / (self::ROUTE_MAX_POINTS - 1);
            for ($j = 0; $j < self::ROUTE_MAX_POINTS; $j++) {
                $sampled[] = $kept[(int) round($j * $step)];
            }
            $kept = $sampled;
        }

        return $kept;
    }

    private function metersBetween(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return 2 * $r * asin(min(1, sqrt($a)));
    }

    public function sync($vehicleId)
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $live = (new TraccarSync())->run((int) $vehicleId);

        return $this->response->setJSON([
            'success'    => true,
            'synced_at'  => date('Y-m-d H:i:s'),
            'vehicle_id' => $vehicleId,
            // false when the vehicle has no tracker linked, or Traccar didn't answer
            'live'       => isset($live[(int) $vehicleId]),
        ]);
    }

    public function logPing()
    {
        $data = [
            'vehicle_id' => $this->request->getPost('vehicle_id'),
            'latitude'   => $this->request->getPost('latitude'),
            'longitude'  => $this->request->getPost('longitude'),
            'status'     => 'Online',
            'device_id'  => $this->request->getPost('device_id') ?? '',
        ];
        if (empty($data['vehicle_id'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing vehicle_id']);
        }
        $this->gpsModel->insert($data);
        return $this->response->setJSON(['success' => true]);
    }
}
