<?php

namespace App\Libraries;

/**
 * Read-only client for the Traccar GPS server the Sinotrack trackers report
 * to. Connection details come from .env (TRACCAR_URL / TRACCAR_USER /
 * TRACCAR_PASS) — never hardcoded.
 */
class TraccarClient
{
    private const TIMEOUT_SECONDS   = 3;
    private const DOWN_CACHE_KEY    = 'traccar_unreachable';
    private const DOWN_CACHE_TTL    = 60;

    public function isConfigured(): bool
    {
        return env('TRACCAR_URL') && env('TRACCAR_USER') && env('TRACCAR_PASS');
    }

    /**
     * Latest known state of every tracker this account can see, keyed by the
     * tracker's Traccar identifier (uniqueId — what vehicles.gps_device_id
     * stores). Two HTTP calls total no matter how big the fleet is.
     *
     * Returns [] when Traccar isn't configured or can't be reached. After a
     * failure it stays quiet for a minute so a downed server doesn't add a
     * multi-second wait to every page load.
     *
     * @return array<string,array{identifier:string,online:bool,latitude:?float,longitude:?float,fix_time:?string,speed_kmh:float,course:?float,ignition:?bool,motion:?bool,odometer_km:?float}>
     */
    public function latestByIdentifier(): array
    {
        if (!$this->isConfigured() || cache()->get(self::DOWN_CACHE_KEY)) {
            return [];
        }

        $devices = $this->get('/api/devices');
        if ($devices === null) {
            cache()->save(self::DOWN_CACHE_KEY, 1, self::DOWN_CACHE_TTL);
            return [];
        }
        $positions = $this->get('/api/positions') ?? [];

        $positionByDevice = [];
        foreach ($positions as $p) {
            $positionByDevice[$p['deviceId']] = $p;
        }

        $result = [];
        foreach ($devices as $d) {
            $p     = $positionByDevice[$d['id']] ?? null;
            $attrs = $p['attributes'] ?? [];

            $result[$d['uniqueId']] = [
                'identifier'  => $d['uniqueId'],
                'online'      => ($d['status'] ?? '') === 'online',
                'latitude'    => $p ? (float) $p['latitude'] : null,
                'longitude'   => $p ? (float) $p['longitude'] : null,
                'fix_time'    => $p['fixTime'] ?? null,
                'speed_kmh'   => $p ? round(((float) $p['speed']) * 1.852, 1) : 0.0, // Traccar reports knots
                'course'      => $p['course'] ?? null,
                // Decoded by Traccar from the tracker's own protocol — not
                // guessed from the device's undocumented raw I/O fields.
                'ignition'    => array_key_exists('ignition', $attrs) ? (bool) $attrs['ignition'] : null,
                'motion'      => array_key_exists('motion', $attrs) ? (bool) $attrs['motion'] : null,
                'odometer_km' => isset($attrs['totalDistance']) ? round(((float) $attrs['totalDistance']) / 1000, 1) : null,
            ];
        }

        return $result;
    }

    /**
     * Every position Traccar recorded for one tracker between two unix
     * timestamps, oldest first. Traccar keeps the full history; the local
     * gps_logs table only gets the latest fix each time a sync runs.
     *
     * @return array<int,array{lat:float,lng:float,loggedAt:string,speed:float}>|null null when Traccar is unreachable / the tracker is unknown
     */
    public function routeHistory(string $identifier, int $fromTs, int $toTs): ?array
    {
        if (!$this->isConfigured()) return null;

        $devices = $this->get('/api/devices?uniqueId=' . rawurlencode($identifier));
        if (!$devices || !isset($devices[0]['id'])) return null;

        $rows = $this->get('/api/reports/route?deviceId=' . (int) $devices[0]['id']
            . '&from=' . gmdate('Y-m-d\TH:i:s\Z', $fromTs)
            . '&to=' . gmdate('Y-m-d\TH:i:s\Z', $toTs), 20);
        if ($rows === null) return null;

        return array_map(fn($p) => [
            'lat'      => (float) $p['latitude'],
            'lng'      => (float) $p['longitude'],
            // Traccar is UTC; render in the app's timezone like TraccarSync does.
            'loggedAt' => date('Y-m-d H:i:s', strtotime($p['fixTime'])),
            'speed'    => round(((float) ($p['speed'] ?? 0)) * 1.852, 1),
        ], $rows);
    }

    /** GET a Traccar API path; decoded JSON on HTTP 200, otherwise null. */
    private function get(string $path, int $timeout = self::TIMEOUT_SECONDS): ?array
    {
        $auth    = base64_encode(env('TRACCAR_USER') . ':' . env('TRACCAR_PASS'));
        $context = stream_context_create([
            'http' => [
                'method'        => 'GET',
                'header'        => "Authorization: Basic {$auth}\r\nAccept: application/json\r\n",
                'timeout'       => $timeout,
                'ignore_errors' => true,
            ],
        ]);

        $body = @file_get_contents(rtrim(env('TRACCAR_URL'), '/') . $path, false, $context);
        if ($body === false) {
            return null;
        }

        $headers = function_exists('http_get_last_response_headers')
            ? (http_get_last_response_headers() ?? [])
            : ($http_response_header ?? []);
        if (!isset($headers[0]) || !preg_match('#\s200(\s|$)#', $headers[0])) {
            return null;
        }

        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : null;
    }
}
