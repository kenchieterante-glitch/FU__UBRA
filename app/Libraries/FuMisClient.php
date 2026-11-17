<?php

namespace App\Libraries;

/**
 * Client for the Foundation University MIS API. The API key and base URL
 * come from .env (FU_MIS_API_URL / FU_MIS_API_KEY) — never hardcoded. The
 * key is IP-restricted on the MIS side, so calls only succeed from an IP
 * registered in the API Sandbox.
 *
 * Two ways to get an access token:
 *  - appToken(): UBRA's own token from the API key alone (tokens/generate →
 *    tokens/refresh), cached until it expires. Used for employee lookups.
 *  - login(): an employee's FU ID + password (employees/login), used to let
 *    real FU employees sign in to UBRA with their FU password.
 */
class FuMisClient
{
    private const TIMEOUT_SECONDS = 6;
    private const DOWN_CACHE_KEY  = 'fu_mis_unreachable';
    private const DOWN_CACHE_TTL  = 60;
    private const TOKEN_CACHE_KEY = 'fu_mis_app_token';

    public function isConfigured(): bool
    {
        return env('FU_MIS_API_URL') && env('FU_MIS_API_KEY');
    }

    /**
     * POST /v1/employees/login. On success returns
     * ['ok' => true, 'employee' => array, 'access_token' => string, 'refresh_token' => ?string, 'expires_at' => ?string].
     * On failure returns ['ok' => false, 'status' => int, 'error' => string] where status 0 means
     * the MIS could not be reached (network down, timeout, or not configured).
     */
    public function login(string $employeeId, string $password): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'status' => 0, 'error' => 'FU MIS API is not configured.'];
        }
        if (cache()->get(self::DOWN_CACHE_KEY)) {
            return ['ok' => false, 'status' => 0, 'error' => 'FU MIS is unreachable right now.'];
        }

        $res = $this->request('POST', '/v1/employees/login', [
            'API-Key: ' . env('FU_MIS_API_KEY'),
        ], ['employee_id' => $employeeId, 'password' => $password]);

        // The tokens may sit at the top level (as documented) or inside "data".
        $body   = $res['body'] ?? [];
        $tokens = !empty($body['access_token']) ? $body : (is_array($body['data'] ?? null) ? $body['data'] : []);

        if ($res['status'] !== 200 || empty($tokens['access_token'])) {
            return ['ok' => false, 'status' => $res['status'], 'error' => $this->errorMessage($res)];
        }

        return [
            'ok'            => true,
            'employee'      => is_array($tokens['data'] ?? $body['data'] ?? null) ? ($tokens['data'] ?? $body['data']) : [],
            'access_token'  => (string) $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'] ?? null,
            'expires_at'    => $tokens['expires_at'] ?? null,
        ];
    }

    /**
     * One employee by FU employee ID, normalised to
     * ['employee_id','first_name','middle_name','last_name','full_name','position','department_code','department_name'],
     * or null when the MIS has no such employee. Returns
     * ['ok' => bool, 'employee' => ?array, 'status' => int, 'error' => ?string].
     */
    public function findEmployee(string $employeeId): array
    {
        $res = $this->authorizedGet('/v1/employees/' . rawurlencode($employeeId));
        if ($res['status'] !== 200) {
            return ['ok' => false, 'employee' => null, 'status' => $res['status'], 'error' => $this->errorMessage($res)];
        }

        $rows = $this->rows($res['body']);

        return ['ok' => true, 'employee' => $rows ? $this->normalizeEmployee($rows[0]) : null, 'status' => 200, 'error' => null];
    }

    /**
     * A page of employees, optionally filtered by search text or department code.
     * Returns ['ok', 'status', 'error', 'employees' => list, 'current_page', 'max_page'].
     */
    public function searchEmployees(array $query = []): array
    {
        $res = $this->authorizedGet('/v1/employees', $query);
        if ($res['status'] !== 200) {
            return ['ok' => false, 'status' => $res['status'], 'error' => $this->errorMessage($res), 'employees' => []];
        }

        return [
            'ok'           => true,
            'status'       => 200,
            'error'        => null,
            'employees'    => array_map([$this, 'normalizeEmployee'], $this->rows($res['body'])),
            'current_page' => (int) ($res['body']['data']['current_page'] ?? 1),
            'max_page'     => (int) ($res['body']['data']['max_page'] ?? 1),
        ];
    }

    /**
     * Every employee matching the filters, walking all pages at the MIS
     * maximum of 100 per page. Cached for 10 minutes per filter combination.
     * Returns ['ok', 'status', 'error', 'employees' => list, 'total' => int].
     */
    public function allEmployees(array $query = []): array
    {
        $query    = array_filter(['search' => $query['search'] ?? null, 'department' => $query['department'] ?? null], static fn($v) => $v !== null && $v !== '');
        $cacheKey = 'fu_mis_all_' . md5(json_encode($query));
        if ($cached = cache()->get($cacheKey)) {
            return $cached;
        }

        $employees = [];
        $page      = 1;
        do {
            $res = $this->searchEmployees($query + ['limit' => 100, 'page' => $page]);
            if (!$res['ok']) {
                return $res + ['total' => 0];
            }
            array_push($employees, ...$res['employees']);
        } while (++$page <= $res['max_page'] && $page <= 100); // safety stop at 10,000 rows

        $result = ['ok' => true, 'status' => 200, 'error' => null, 'employees' => $employees, 'total' => count($employees)];
        cache()->save($cacheKey, $result, 600);

        return $result;
    }

    /** A page of departments (['code', 'name'] rows). */
    public function departments(array $query = []): array
    {
        $res = $this->authorizedGet('/v1/employees/departments', $query);
        if ($res['status'] !== 200) {
            return ['ok' => false, 'status' => $res['status'], 'error' => $this->errorMessage($res), 'departments' => []];
        }

        return ['ok' => true, 'status' => 200, 'error' => null, 'departments' => $this->rows($res['body'])];
    }

    /**
     * UBRA's own access token: tokens/generate (API key) → tokens/refresh.
     * Cached until shortly before it expires; null when the MIS is unreachable
     * or rejects the key.
     */
    public function appToken(bool $forceNew = false): ?string
    {
        if (!$this->isConfigured() || cache()->get(self::DOWN_CACHE_KEY)) {
            return null;
        }
        if (!$forceNew && ($cached = cache()->get(self::TOKEN_CACHE_KEY))) {
            return $cached;
        }

        $apiKey   = 'API-Key: ' . env('FU_MIS_API_KEY');
        $generate = $this->request('POST', '/tokens/generate', [$apiKey]);
        $refresh  = $generate['body']['data']['refresh_token'] ?? null;
        if ($generate['status'] !== 200 || !$refresh) {
            $this->noteFailure($generate, 'tokens/generate');
            return null;
        }

        $pair  = $this->request('POST', '/tokens/refresh', [$apiKey, 'Authorization: Bearer ' . $refresh]);
        $token = $pair['body']['data']['access_token'] ?? null;
        if ($pair['status'] !== 200 || !$token) {
            $this->noteFailure($pair, 'tokens/refresh');
            return null;
        }

        $expiresAt = strtotime((string) ($pair['body']['data']['expires_at'] ?? '')) ?: time() + 600;
        cache()->save(self::TOKEN_CACHE_KEY, $token, max(60, $expiresAt - time() - 60));

        return $token;
    }

    /**
     * The MIS answers 401 for an invalid API key / token ({"error": …}) and
     * 403 for an unrecognized employee login ({"status":"error","message": …});
     * its own wording is shown when present.
     */
    public function errorMessage(array $res): string
    {
        $misText = $res['body']['message'] ?? $res['body']['error'] ?? null;

        return match (true) {
            $res['status'] === 0   => 'Could not reach FU MIS. Check the internet connection, or that this network\'s IP address is allowed in the API Sandbox.',
            $res['status'] === 429 => 'Too many requests to FU MIS. Please wait a moment.',
            is_string($misText)    => 'FU MIS: ' . $misText,
            $res['status'] === 401 => 'FU MIS rejected the API key or the access token has expired.',
            $res['status'] === 403 => 'FU MIS did not recognize the account.',
            $res['status'] === 404 => 'FU MIS could not find that record.',
            default                => 'FU MIS returned an error (HTTP ' . $res['status'] . ').',
        };
    }

    /** GET with UBRA's app token; on 401/403 (the MIS uses both for a bad or expired token) gets a fresh one and retries once. */
    private function authorizedGet(string $path, array $query = []): array
    {
        $token = $this->appToken();
        if ($token === null) {
            return ['status' => 0, 'body' => null];
        }

        $query = array_filter($query, static fn($v) => $v !== null && $v !== '');
        if ($query) {
            $path .= '?' . http_build_query($query);
        }

        $res = $this->request('GET', $path, ['Authorization: Bearer ' . $token]);
        if (in_array($res['status'], [401, 403], true) && ($token = $this->appToken(true))) {
            $res = $this->request('GET', $path, ['Authorization: Bearer ' . $token]);
        }

        return $res;
    }

    /** The MIS wraps lists as {"data": {"data": [...], "limit", "current_page", "max_page"}}. */
    private function rows(?array $body): array
    {
        $rows = $body['data']['data'] ?? [];
        if (!is_array($rows)) {
            return [];
        }

        return array_is_list($rows) ? $rows : [$rows];
    }

    private function normalizeEmployee(array $row): array
    {
        $first  = trim((string) ($row['first_name'] ?? ''));
        $middle = trim((string) ($row['middle_name'] ?? ''));
        $last   = trim((string) ($row['last_name'] ?? ''));

        return [
            'employee_id'     => (string) ($row['employee_id'] ?? ''),
            'first_name'      => $first,
            'middle_name'     => $middle,
            'last_name'       => $last,
            'full_name'       => trim(preg_replace('/\s+/', ' ', "{$first} {$middle} {$last}")),
            'position'        => trim((string) ($row['position'] ?? '')),
            'department_code' => (string) ($row['department_code'] ?? ''),
            'department_name' => trim((string) ($row['department_name'] ?? '')),
        ];
    }

    private function noteFailure(array $res, string $step): void
    {
        if ($res['status'] === 0) {
            cache()->save(self::DOWN_CACHE_KEY, 1, self::DOWN_CACHE_TTL);
        }
        log_message('error', 'FU MIS {step} failed: HTTP {status}, {message}', [
            'step'    => $step,
            'status'  => $res['status'],
            'message' => (string) ($res['body']['message'] ?? $res['body']['error'] ?? 'no message'),
        ]);
    }

    /**
     * FU_MIS_API_URL may be written as …/sandbox/api or …/sandbox/api/v1;
     * the tokens endpoints live under …/api, the employee ones under …/api/v1.
     */
    private function baseUrl(): string
    {
        return preg_replace('#/v1/?$#', '', rtrim((string) env('FU_MIS_API_URL'), '/'));
    }

    /** @return array{status:int, body:?array} status 0 = no response at all */
    private function request(string $method, string $path, array $headers, ?array $json = null): array
    {
        $headers[] = 'Accept: application/json';
        $http = [
            'method'        => $method,
            'timeout'       => self::TIMEOUT_SECONDS,
            'ignore_errors' => true,
        ];
        if ($json !== null) {
            $headers[]       = 'Content-Type: application/json';
            $http['content'] = json_encode($json);
        }
        $http['header'] = implode("\r\n", $headers) . "\r\n";

        $body = @file_get_contents($this->baseUrl() . $path, false, stream_context_create(['http' => $http]));
        if ($body === false) {
            return ['status' => 0, 'body' => null];
        }

        $responseHeaders = function_exists('http_get_last_response_headers')
            ? (http_get_last_response_headers() ?? [])
            : ($http_response_header ?? []);
        $status = isset($responseHeaders[0]) && preg_match('#\s(\d{3})(\s|$)#', $responseHeaders[0], $m) ? (int) $m[1] : 0;

        $decoded = json_decode($body, true);

        return ['status' => $status, 'body' => is_array($decoded) ? $decoded : null];
    }
}
