<?php

namespace App\Controllers;

use App\Models\VehicleModel;
use App\Models\PersonnelModel;
use App\Models\ToolsModel;
use App\Libraries\DepartmentScope;
use App\Libraries\UbraScope;
use App\Models\UbraChatLogModel;

class UbraController extends BaseController
{
    protected $session;
    protected $chatLogModel;

    public function __construct()
    {
        $this->session      = \Config\Services::session();
        $this->chatLogModel = new UbraChatLogModel();
    }

    // Chat logs are keyed by employee ID, not the session's numeric user_id
    // (some seeded accounts share/lack one) — emp_id is what's guaranteed
    // unique and present for every account.
    private function currentEmpId(): string
    {
        return (string) ($this->session->get('emp_id') ?? '');
    }

    public function index()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $data = [
            'title'        => 'Mr. UBRA Assistant',
            'flash_success' => $this->session->getFlashdata('success'),
            'quick_actions' => UbraScope::quickActions((string) $this->session->get('role')),
            'greet_name'   => (string) ($this->session->get('full_name') ?? 'there'),
        ];

        return view('ubra/index', $data);
    }

    public function chat()
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $userMessage = $this->request->getPost('message');
        $history     = json_decode($this->request->getPost('history') ?? '[]', true);

        if (empty(trim($userMessage ?? ''))) {
            return $this->response->setJSON(['error' => 'Message is required.']);
        }

        // Department accounts only get answers about their own department. A question that is clearly about another
        // department is turned down here, before any data is read or any AI call is made.
        $role = (string) $this->session->get('role');
        $refusal = UbraScope::refusalFor($role, (string) $userMessage);
        if ($refusal !== null) {
            $empId = $this->currentEmpId();
            if ($empId !== '') {
                $this->chatLogModel->logTurn($empId, 'user', $userMessage);
                $this->chatLogModel->logTurn($empId, 'assistant', $refusal);
            }
            return $this->response->setJSON(['reply' => $refusal, 'role' => 'assistant']);
        }

        // A plain text-in/text-out AI API call has no way to actually produce
        // a file — that's why it was replying "I cannot attach a PDF" instead
        // of just doing it. Report requests are handled here in PHP instead,
        // deterministically, using the same export engine Records, Archiving
        // & Reports and the Calendar's "Generate Weekly Summary" already use,
        // so Mr. UBRA hands back a real, working download link.
        $reportRequest = $this->detectReportRequest($userMessage);
        $reportRequest = $this->carryOverReport($reportRequest, (string) $userMessage, (array) $history);
        if ($reportRequest !== null) {
            return $this->replyWithReport($reportRequest, $userMessage);
        }

        $context = $this->buildContext();
        $apiKey  = $this->getApiKey();

        if (empty($apiKey)) {
            return $this->response->setJSON([
                'reply' => "⚠️ No API key configured. Please add your AI API key in **Settings → AI Configuration** to activate Mr. UBRA AI.",
                'role'  => 'assistant',
            ]);
        }

        $messages = [];
        foreach ((array)$history as $turn) {
            if (!empty($turn['role']) && !empty($turn['content'])) {
                $messages[] = ['role' => $turn['role'], 'content' => $turn['content']];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $systemPrompt = $this->buildSystemPrompt($context);

        // Four supported key formats, detected by their distinctive prefix:
        // Anthropic (sk-ant-...), Groq (gsk_...), OpenRouter (sk-or-...), and
        // Gemini (AIzaSy... — the documented format — or AQ. — an alternate
        // format Google issues for some accounts; confirmed working directly
        // against the Gemini API before adding it here). Groq and OpenRouter
        // are both OpenAI-compatible, so they share a request/response shape
        // that differs from Anthropic's Messages API and from Gemini's own.
        if (str_starts_with($apiKey, 'sk-ant-')) {
            [$httpCode, $response] = $this->callAnthropic($apiKey, $systemPrompt, $messages);
            $reply = null;
            if ($httpCode === 200 && $response) {
                $data  = json_decode($response, true);
                $reply = $data['content'][0]['text'] ?? null;
            }
        } elseif (str_starts_with($apiKey, 'gsk_')) {
            [$httpCode, $response] = $this->callGroq($apiKey, $systemPrompt, $messages);
            $reply = null;
            if ($httpCode === 200 && $response) {
                $data  = json_decode($response, true);
                $reply = $data['choices'][0]['message']['content'] ?? null;
            }
        } elseif (str_starts_with($apiKey, 'sk-or-')) {
            [$httpCode, $response] = $this->callOpenRouter($apiKey, $systemPrompt, $messages);
            $reply = null;
            if ($httpCode === 200 && $response) {
                $data  = json_decode($response, true);
                $reply = $data['choices'][0]['message']['content'] ?? null;
            }
        } elseif (str_starts_with($apiKey, 'AIzaSy') || str_starts_with($apiKey, 'AQ.')) {
            [$httpCode, $response] = $this->callGemini($apiKey, $systemPrompt, $messages);
            $reply = null;
            if ($httpCode === 200 && $response) {
                $data  = json_decode($response, true);
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }
        } else {
            return $this->response->setJSON([
                'reply' => "⚠️ That API key doesn't match a supported format (Anthropic keys start with sk-ant-, Groq keys start with gsk_, OpenRouter keys start with sk-or-, Gemini keys start with AIzaSy). Please check the key in Settings → AI Configuration.",
                'role'  => 'assistant',
            ]);
        }

        if ($reply === null) {
            log_message('error', "UbraController::chat — AI request failed, HTTP {$httpCode}: " . substr((string) $response, 0, 500));

            // A 429 here means the AI PROVIDER's own quota/rate limit was
            // hit (e.g. Gemini's free tier: 20 requests/day) — telling the
            // user "trouble connecting" for this is actively misleading,
            // since retrying immediately just fails again for the same
            // reason. Say what actually happened instead.
            $reply = $httpCode === 429
                ? "⚠️ The AI provider's usage limit has been reached for now (this happens fast on a free-tier key, e.g. Gemini's 20-requests/day cap). Please wait a while and try again, or add a different/paid API key in **Settings → AI Configuration**."
                : "I'm having trouble connecting right now. Please try again.";

            return $this->response->setJSON([
                'reply' => $reply,
                'role'  => 'assistant',
            ]);
        }

        // Drop the filler word the panel flagged, in case the model still uses it.
        $reply = preg_replace('/\s*\bshortly\b/i', '', (string) $reply);

        $empId = $this->currentEmpId();
        if ($empId !== '') {
            $this->chatLogModel->logTurn($empId, 'user', $userMessage);
            $this->chatLogModel->logTurn($empId, 'assistant', $reply);
        }

        return $this->response->setJSON(['reply' => $reply, 'role' => 'assistant']);
    }

    // Recent history for the logged-in user, oldest first (ready to render
    // top-to-bottom as-is) — powers both the full /ubra page and the
    // floating chat widget on every other page.
    public function history()
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $empId = $this->currentEmpId();
        $rows  = $empId !== '' ? $this->chatLogModel->getForUser($empId, 100) : [];
        $rows  = array_reverse($rows);

        return $this->response->setJSON(['history' => $rows]);
    }

    public function clearHistory()
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $empId = $this->currentEmpId();
        if ($empId !== '') {
            $this->chatLogModel->clearForUser($empId);
        }

        return $this->response->setJSON(['success' => true]);
    }

    private function callAnthropic(string $apiKey, string $systemPrompt, array $messages): array
    {
        $payload = json_encode([
            'model'      => 'claude-sonnet-4-6',
            'max_tokens' => 1000,
            'system'     => $systemPrompt,
            'messages'   => $messages,
        ]);

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$httpCode, $response];
    }

    private function callGroq(string $apiKey, string $systemPrompt, array $messages): array
    {
        $payload = json_encode([
            'model'      => 'openai/gpt-oss-120b',
            'max_tokens' => 1000,
            'messages'   => array_merge(
                [['role' => 'system', 'content' => $systemPrompt]],
                $messages
            ),
        ]);

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$httpCode, $response];
    }

    private function callOpenRouter(string $apiKey, string $systemPrompt, array $messages): array
    {
        // A free-tier model — OpenRouter periodically retires/renames its
        // free models, so if this one starts 404ing, swap in whatever's
        // current at openrouter.ai/models?max_price=0. Verified working
        // (real 200 response) as of this writing.
        $payload = json_encode([
            'model'      => 'minimax/minimax-m3:free',
            'max_tokens' => 1000,
            'messages'   => array_merge(
                [['role' => 'system', 'content' => $systemPrompt]],
                $messages
            ),
        ]);

        $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
                // Optional per OpenRouter's docs, but they ask for these to
                // attribute traffic on their end — harmless to send.
                'HTTP-Referer: ' . base_url(),
                'X-Title: UBRA',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$httpCode, $response];
    }

    private function callGemini(string $apiKey, string $systemPrompt, array $messages): array
    {
        // Gemini's own message shape: role is "user"/"model" (never
        // "assistant"), and the system prompt is a separate top-level field
        // rather than a message in the list.
        $contents = array_map(fn($m) => [
            'role'  => $m['role'] === 'assistant' ? 'model' : 'user',
            'parts' => [['text' => $m['content']]],
        ], $messages);

        $payload = json_encode([
            'contents'          => $contents,
            'systemInstruction' => ['parts' => [['text' => $systemPrompt]]],
            'generationConfig'  => ['maxOutputTokens' => 1000],
        ]);

        // gemini-2.0-flash (an earlier default) has since been retired by
        // Google in favor of this one — verified working (real 200 response)
        // directly against the endpoint before adding it here.
        $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=' . urlencode($apiKey));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$httpCode, $response];
    }

    public function quickAction()
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $action = $this->request->getPost('action');
        $prompts = [
            'daily_summary'   => 'Give me a concise daily operations summary for today based on the current system context.',
            'vehicle_health'  => 'Summarize the current vehicle fleet health, GPS status, and any vehicles needing attention.',
            'maintenance_due' => 'What maintenance tasks are coming up or overdue? List them with recommended actions.',
            'weekly_report'   => 'Generate a brief weekly operations report covering assets, personnel, and alerts.',
            'staff_summary'   => 'Give me a personnel summary — who is on duty today and any unassigned staff.',
        ];

        $message = $prompts[$action] ?? 'Give me an operations overview.';
        if (UbraScope::departmentFor((string) $this->session->get('role')) !== null) {
            $message = 'Give me a concise overview of my department right now, based on the snapshot you have.';
        }
        $_POST['message'] = $message;
        $_POST['history'] = '[]';

        return $this->chat();
    }

    // Follow-ups on a report the assistant just made: "no, past 30 days", "make it excel", "last month instead".
    // The previous report's module and format are kept, and only what the user changed is replaced.
    private function carryOverReport(?array $req, string $message, array $history): ?array
    {
        $prev = null;
        for ($i = count($history) - 1; $i >= 0; $i--) {
            $t = $history[$i] ?? [];
            if (($t['role'] ?? '') === 'assistant') { $prev = (string) ($t['content'] ?? ''); break; }
        }
        if ($prev === null || !preg_match("/Here's your (PDF|Excel|CSV) report/", $prev, $f)) return $req;
        $prevFormat = strtolower($f[1]);
        $prevModule = '';
        if (preg_match('/\*\*Covers:\*\*\s*([^\n*]+)/', $prev, $c) && stripos($c[1], 'all modules') === false) {
            $prevModule = strtolower(str_replace(' ', '-', trim($c[1])));
        }

        $m = strtolower($message);
        if ($req !== null) {
            // A new report request that names no module keeps the previous report's module.
            if ($req['module'] === '') $req['module'] = $prevModule;
            // "excel please" names no period, so keep the dates of the report it is changing.
            [, , $named] = $this->parseDateRange($m);
            if (!$named && preg_match('/\*\*Date range:\*\*\s*(.+?) to (.+?)(?:
|$)/', $prev, $d) && strtotime($d[1]) && strtotime($d[2])) {
                $req['date_from'] = date('Y-m-d', strtotime($d[1]));
                $req['date_to']   = date('Y-m-d', strtotime($d[2]));
            }
            return $req;
        }
        // No format word: only a short message that names a period counts as "change the dates of that report".
        [$from, $to, $explicit] = $this->parseDateRange($m);
        $isQuestion = (bool) preg_match('/\b(how|what|which|who|when|where|why|is|are|any|show|list|tell|give)\b/', $m);
        $hasCue     = (bool) preg_match('/\b(no|nope|instead|change|actually|make it|how about|what about|i meant|i mean|rather|please)\b/', $m);
        if ($explicit && !$isQuestion && ($hasCue || str_word_count($m) <= 6) && str_word_count($m) <= 14) {
            return ['format' => $prevFormat, 'module' => $prevModule, 'date_from' => $from->format('Y-m-d'), 'date_to' => $to->format('Y-m-d')];
        }
        return null;
    }

    // Works out the date range a message asks for. Returns [from, to, explicit]; explicit is false when the message
    // named no period at all (a default of the last 7 days is used then).
    private function parseDateRange(string $m): array
    {
        $today = new \DateTime('today');
        $words = ['a' => 1, 'an' => 1, 'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6, 'seven' => 7, 'eight' => 8,
                  'nine' => 9, 'ten' => 10, 'twelve' => 12, 'fourteen' => 14, 'fifteen' => 15, 'twenty' => 20, 'thirty' => 30, 'sixty' => 60, 'ninety' => 90];
        $months = ['january','february','march','april','may','june','july','august','september','october','november','december'];

        // "past 30 days", "last 2 weeks", "previous three months", "30 days"
        if (preg_match('/\b(?:past|last|previous|within|over the last|for the last)?\s*(\d{1,3}|' . implode('|', array_keys($words)) . ')\s*(day|days|week|weeks|month|months|year|years)\b/', $m, $r)
            && (preg_match('/\b(past|last|previous|within)\b/', $m) || ctype_digit($r[1]))) {
            $n = ctype_digit($r[1]) ? (int) $r[1] : $words[$r[1]];
            $unit = rtrim($r[2], 's');
            $from = (clone $today)->modify('-' . ($unit === 'day' ? $n : ($unit === 'week' ? $n * 7 : ($unit === 'month' ? $n * 30 : $n * 365))) . ' days');
            return [$from, clone $today, true];
        }
        foreach ($months as $idx => $name) {
            if (preg_match('/\b' . $name . '\b/', $m)) {
                $year = preg_match('/\b(20\d{2})\b/', $m, $ym) ? (int) $ym[1] : (int) date('Y');
                $from = new \DateTime(sprintf('%04d-%02d-01', $year, $idx + 1));
                return [$from, (clone $from)->modify('last day of this month'), true];
            }
        }
        if (preg_match('/\byesterday\b/', $m)) { $d = (clone $today)->modify('-1 day'); return [$d, clone $d, true]; }
        if (preg_match('/\btoday\b/', $m)) return [clone $today, clone $today, true];
        if (preg_match('/\blast\s+month\b/', $m)) return [(clone $today)->modify('first day of last month'), (clone $today)->modify('last day of last month'), true];
        if (preg_match('/\b(this\s+month|monthly|month to date)\b/', $m)) return [(clone $today)->modify('first day of this month'), clone $today, true];
        if (preg_match('/\blast\s+year\b/', $m)) { $y = (int) date('Y') - 1; return [new \DateTime("$y-01-01"), new \DateTime("$y-12-31"), true]; }
        if (preg_match('/\b(this\s+year|year to date|yearly|annual)\b/', $m)) return [new \DateTime(date('Y') . '-01-01'), clone $today, true];
        if (preg_match('/\bthis\s+week\b/', $m)) return [(clone $today)->modify('monday this week'), clone $today, true];
        if (preg_match('/\blast\s+week\b/', $m)) return [(clone $today)->modify('monday last week'), (clone $today)->modify('sunday last week'), true];
        if (preg_match('/\b(past\s+week|weekly|week)\b/', $m)) return [(clone $today)->modify('-6 days'), clone $today, true];

        return [(clone $today)->modify('-6 days'), clone $today, false];
    }

    // Recognizes a request for a report/summary/export file — plain
    // keyword matching on purpose, no AI call involved, so it's instant,
    // free, and always correct rather than depending on a model to know
    // about this app's internal routes (which it never could). Returns
    // null when the message isn't actually asking for a file.
    private function detectReportRequest(string $message): ?array
    {
        $m = strtolower($message);

        // An explicit FILE FORMAT word (pdf/excel/csv/...) is, on its own,
        // an almost unambiguous signal that a real file is wanted — people
        // don't casually say "pdf" to an operations assistant otherwise —
        // so it alone is enough to trigger. Without one, still allow
        // "download"/"export"/"file" *combined with* "report"/"summary",
        // for phrasing that names no format ("can I download a report").
        // "generate a weekly report" alone (the existing quick-action
        // prompt, no format word at all) correctly falls through to the
        // AI's normal conversational summary instead.
        $hasExplicitFormat = (bool) preg_match('/\b(pdf|excel|spreadsheet|xls|csv)\b/', $m);
        $hasGenericFileWord = (bool) preg_match('/\b(download|export|file)\b/', $m) && (bool) preg_match('/\b(report|summary)\b/', $m);

        if (!$hasExplicitFormat && !$hasGenericFileWord) {
            return null;
        }

        $format = 'pdf';
        if (preg_match('/\b(excel|spreadsheet|xls)\b/', $m)) {
            $format = 'excel';
        } elseif (preg_match('/\bcsv\b/', $m)) {
            $format = 'csv';
        }

        // Same five modules Records, Archiving & Reports exports support.
        $moduleMap = [
            'waste-watch' => ['waste watch', 'wastewatch', 'waste', 'garbage', 'trash', 'segregation', 'hazardous'],
            'vehicle'    => ['vehicle', 'vehicles', 'fleet', 'driver', 'drivers', 'trip ticket'],
            'tools'      => ['tool', 'tools', 'equipment', 'borrow', 'borrowing'],
            'safety'     => ['safety', 'maintenance', 'fire extinguisher', 'aircon', 'work order'],
            'janitorial' => ['janitorial', 'janitor', 'cleaning'],
            'personnel'  => ['personnel', 'staff', 'employee', 'job order'],
            'motor-pool' => ['motor pool', 'mechanical equipment', 'generator'],
            'sports'     => ['sports', 'basketball', 'volleyball', 'racket'],
            'security'   => ['fire safety', 'smoke detector', 'guard', 'key log', 'keys'],
            'facilities' => ['repair request', 'building check', 'supplies'],
        ];
        $module = '';
        foreach ($moduleMap as $slug => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($m, $kw)) { $module = $slug; break 2; }
            }
        }

        [$from, $to] = $this->parseDateRange($m);

        return [
            'format'    => $format,
            'module'    => $module,
            'date_from' => $from->format('Y-m-d'),
            'date_to'   => $to->format('Y-m-d'),
        ];
    }

    // Builds the real download link (same records/export engine as Records,
    // Archiving & Reports and the Calendar) and replies with it directly —
    // no AI round-trip needed, and no more "I can't attach files" apology.
    private function replyWithReport(array $req, string $userMessage)
    {
        // An account can only export its own department's records.
        $allowed = DepartmentScope::modulesForRole((string) $this->session->get('role'));
        if ($allowed !== null && $req['module'] !== '') {
            $allowedSlugs = array_map(fn($m) => DepartmentScope::SLUGS[$m], $allowed);
            if (!in_array($req['module'], $allowedSlugs, true)) {
                $msg = "That report is outside your account's access. I can only prepare reports for your own department (" . implode(' and ', $allowed) . ').';
                return $this->response->setJSON(['reply' => $msg, 'role' => 'assistant']);
            }
        }

        $url = base_url('records/export/' . $req['format']) . '?' . http_build_query([
            'module'    => $req['module'],
            'date_from' => $req['date_from'],
            'date_to'   => $req['date_to'],
        ]);

        $moduleLabel = $req['module'] !== '' ? ucwords(str_replace('-', ' ', $req['module'])) : 'All modules';
        $formatLabel = ['pdf' => 'PDF', 'excel' => 'Excel', 'csv' => 'CSV'][$req['format']];
        $niceFrom    = date('M j, Y', strtotime($req['date_from']));
        $niceTo      = date('M j, Y', strtotime($req['date_to']));

        $reply = "Here's your {$formatLabel} report:\n\n"
            . "**Covers:** {$moduleLabel}\n**Date range:** {$niceFrom} to {$niceTo}\n\n"
            . "Tip: say something like \"generate an excel report for tools this month\" to change the format, module, or date range.";

        $empId = $this->currentEmpId();
        if ($empId !== '') {
            $this->chatLogModel->logTurn($empId, 'user', $userMessage);
            $this->chatLogModel->logTurn($empId, 'assistant', $reply);
        }

        return $this->response->setJSON([
            'reply'    => $reply,
            'role'     => 'assistant',
            'download' => ['url' => $url, 'label' => "Download {$formatLabel} report"],
        ]);
    }

    // Live Waste Watch numbers, as short lines the assistant can quote.
    private function wasteLines(): array
    {
        $db = \Config\Database::connect();
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');
        $limit = \App\Controllers\WasteController::HAZARD_LIMIT_KG;
        $stations = (int) $db->table('waste_stations')->where('is_archived', 0)->countAllResults();
        $doneToday = (int) $db->query("SELECT COUNT(DISTINCT station_id) n FROM waste_logs WHERE log_date = ?", [$today])->getRow()->n;
        $t = $db->query("SELECT COALESCE(SUM(non_bio_kg),0) nb, COALESCE(SUM(bio_kg),0) b, COALESCE(SUM(hazardous_kg),0) h FROM waste_logs WHERE log_date = ?", [$today])->getRow();
        $m = $db->query("SELECT COALESCE(SUM(non_bio_kg),0) nb, COALESCE(SUM(bio_kg),0) b, COALESCE(SUM(hazardous_kg),0) h, COUNT(*) n FROM waste_logs WHERE log_date >= ?", [$monthStart])->getRow();
        $top = $db->query("SELECT s.building, SUM(l.non_bio_kg + l.bio_kg + l.hazardous_kg) kg FROM waste_logs l JOIN waste_stations s ON s.id = l.station_id WHERE l.log_date >= ? GROUP BY s.building ORDER BY kg DESC LIMIT 3", [$monthStart])->getResultArray();
        $high = (int) $db->query("SELECT COUNT(*) n FROM waste_logs WHERE log_date >= ? AND hazardous_kg >= ?", [$monthStart, $limit])->getRow()->n;
        $lines = [
            "Waste Watch (Facilities): {$stations} waste stations (one per building). Each day the supervisor weighs 3 bins per building: non-biodegradable, biodegradable and hazardous. Today {$doneToday}/{$stations} stations are weighed (" . ($stations - $doneToday) . " still to do).",
            "Waste today: non-biodegradable " . round((float) $t->nb, 1) . " kg | biodegradable " . round((float) $t->b, 1) . " kg | hazardous " . round((float) $t->h, 1) . " kg.",
            "Waste this month so far (" . date('F Y') . "): {$m->n} weigh-ins | non-biodegradable " . round((float) $m->nb, 1) . " kg | biodegradable " . round((float) $m->b, 1) . " kg | hazardous " . round((float) $m->h, 1) . " kg. Days with hazardous waste at or above {$limit} kg at one station: {$high}.",
        ];
        if ($top) {
            $lines[] = 'Buildings with the most waste this month: ' . implode(', ', array_map(fn($r) => $r['building'] . ' (' . round((float) $r['kg'], 1) . ' kg)', $top)) . '.';
        }
        return $lines;
    }

    // System-wide numbers — only for the unrestricted (administrator) assistant.
    private function buildFullContext(): array
    {
        $ctx = [];
        try {
            $ctx['total_vehicles']     = (new VehicleModel())->countAll();
            $ctx['available_vehicles'] = (new VehicleModel())->where('availability', 'Available')->countAllResults();
            $ctx['total_personnel']    = (new PersonnelModel())->countAll();
            $ctx['on_duty']            = (new PersonnelModel())->where('status', 'Active')->countAllResults();
            $ctx['total_assets']       = (new ToolsModel())->countAll();
            $ctx['borrowed_tools']     = (new ToolsModel())->where('availability', 'Borrowed')->where('is_archived', 0)->countAllResults();

            $today = date('Y-m-d');
            $ctx['overdue_fire_extinguishers'] = (new \App\Models\FireExtinguisherModel())->where('next_due <', $today)->countAllResults();
            $ctx['open_work_orders']           = (new \App\Models\SafetyWorkOrderModel())->where('stage !=', 'Completed/Verified')->countAllResults();

            $zoneCounts = (new \App\Models\JanitorialAssignmentModel())->getZoneCleanCounts();
            $ctx['janitorial_zones_cleaned'] = $zoneCounts['cleaned'];
            $ctx['janitorial_zones_total']   = $zoneCounts['total'];

            $ctx['pending_trip_tickets'] = (new \App\Models\TravelModel())->whereIn('status', ['Submitted', 'Reviewed', 'Approved'])->countAllResults();
        } catch (\Exception $e) {
            $ctx['error'] = 'Some context unavailable';
        }
        // More live numbers so the assistant can answer about every module, not only the first six.
        $ctx['extra_lines'] = [];
        try {
            $today = date('Y-m-d');
            $db = \Config\Database::connect();
            $ctx['extra_lines'] = $this->wasteLines();
            $ctx['extra_lines'][] = 'Keys (Guard Monitoring): ' . (new \App\Models\FacilityKeyModel())->countAllResults() . ' registered keys | ' . (new \App\Models\KeyBorrowLogModel())->where('status', 'Active')->countAllResults() . ' borrowed right now | ' . (new \App\Models\KeyBorrowLogModel())->where('scan_in >=', $today . ' 00:00:00')->countAllResults() . ' key scans today. Only active staff in Personnel can borrow keys.';
            $ctx['extra_lines'][] = 'Aircon Care (Facilities): ' . (new \App\Models\AirconUnitModel())->countAllResults() . ' units | ' . (new \App\Models\AirconUnitModel())->where('next_schedule <=', date('Y-m-d', strtotime('+7 days')))->countAllResults() . ' due or overdue within 7 days.';
            $ctx['extra_lines'][] = 'Building Check (Facilities, ' . date('F Y') . '): ' . (int) $db->table('janitorial_inspections')->select('COUNT(DISTINCT building) AS n')->where('inspection_month', date('Y-m'))->get()->getRow()->n . ' buildings checked | ' . (new \App\Models\JanitorialInspectionModel())->where('inspection_month', date('Y-m'))->where('result', 'Urgent')->countAllResults() . ' marked Urgent.';
            $ctx['extra_lines'][] = 'Safety Inspection (Security, ' . date('F Y') . '): ' . (new \App\Models\SafetyInspectionModel())->where('inspection_month', date('Y-m'))->countAllResults() . ' inspections recorded this month.';
            $tm = new ToolsModel();
            $ctx['extra_lines'][] = 'Supplies and Materials (consumable stock): ' . $tm->where('category', 'Consumable')->where('is_archived', 0)->countAllResults() . ' items | ' . (new ToolsModel())->where('category', 'Consumable')->where('is_archived', 0)->where('current_stock <= reorder_threshold', null, false)->countAllResults() . ' low or out of stock. Each supply has a stock type such as liter, box, roll, pack, bottle or cartridge.';
            $ctx['extra_lines'][] = 'Sports equipment: ' . (new ToolsModel())->where('category', 'Sports Equipment')->where('is_archived', 0)->countAllResults() . ' items | ' . (new ToolsModel())->where('category', 'Sports Equipment')->where('availability', 'Borrowed')->countAllResults() . ' borrowed.';
            $v = new VehicleModel();
            $ctx['extra_lines'][] = 'Vehicles on GPS: ' . $v->where('is_archived', 0)->where('gps_status', 'Online')->countAllResults() . ' online. Trips today: ' . (new \App\Models\TravelModel())->where('travel_date', $today)->where('is_archived', 0)->countAllResults() . ' | in transit: ' . (new \App\Models\TravelModel())->where('status', 'In Transit')->countAllResults() . '.';
            $ctx['extra_lines'][] = 'Personnel status: ' . (new PersonnelModel())->where('status', 'Active')->countAllResults() . ' active | ' . (new PersonnelModel())->where('status', 'On Leave')->countAllResults() . ' on leave | ' . (new PersonnelModel())->where('status', 'Inactive')->countAllResults() . ' inactive.';
            $ctx['extra_lines'][] = 'Notifications: ' . (new \App\Models\NotificationModel())->where('is_read', 0)->where('priority', 'CRITICAL')->countAllResults() . ' unread critical alerts.';
        } catch (\Throwable $e) {
            $ctx['extra_lines'][] = '(some numbers are unavailable right now)';
        }
        $ctx['current_date'] = date('l, F j, Y');
        $ctx['current_time'] = date('h:i A');
        $ctx['role']         = (string) ($this->session->get('role') ?? '');
        return $ctx;
    }

    // The numbers a department account's assistant may see: its own department only.
    private function buildContext(): array
    {
        $dept = UbraScope::departmentFor((string) $this->session->get('role'));
        if ($dept === null) return $this->buildFullContext();

        $ctx = ['dept' => $dept, 'lines' => []];
        $today = date('Y-m-d');
        $month = date('Y-m');
        try {
            if ($dept === 'facilities') {
                $wo = new \App\Models\WorkOrderModel();
                $ctx['lines'][] = 'Repair requests: ' . (new \App\Models\WorkOrderModel())->where('status !=', 'Completed')->countAllResults() . ' open | ' . $wo->where('status !=', 'Completed')->where('priority', 'Urgent')->countAllResults() . ' urgent';
                $ctx['lines'][] = 'Aircon units due or overdue (next 7 days): ' . (new \App\Models\AirconUnitModel())->where('next_schedule <=', date('Y-m-d', strtotime('+7 days')))->countAllResults();
                $tasks = new \App\Models\JanitorialTaskModel();
                $ctx['lines'][] = 'Cleaning tasks done: ' . (new \App\Models\JanitorialTaskModel())->where('is_done', 1)->countAllResults() . ' / ' . $tasks->countAllResults();
                $ctx['lines'][] = 'Facilities supplies out of stock: ' . (new \App\Models\ConsumableInventoryModel())->where('department', 'Facilities')->where('current_stock <= 0', null, false)->countAllResults();
                foreach ($this->wasteLines() as $wl) $ctx['lines'][] = $wl;
                $ctx['lines'][] = 'Buildings checked this month: ' . (int) \Config\Database::connect()->table('janitorial_inspections')->select('COUNT(DISTINCT building) AS n')->where('inspection_month', $month)->get()->getRow()->n;
            } elseif ($dept === 'security') {
                $fe = new \App\Models\FireExtinguisherModel();
                $ctx['lines'][] = 'Fire extinguishers: ' . $fe->countAllResults() . ' total | ' . (new \App\Models\FireExtinguisherModel())->where('next_due <', $today)->countAllResults() . ' overdue for check | ' . (new \App\Models\FireExtinguisherModel())->where('expires_on <', $today)->countAllResults() . ' expired';
                $ctx['lines'][] = 'Other safety equipment (alarms, smoke detectors, exit signs): ' . (new \App\Models\SafetyEquipmentModel())->countAllResults() . ' total';
                $ctx['lines'][] = 'Keys currently borrowed: ' . (new \App\Models\KeyBorrowLogModel())->where('status', 'Active')->countAllResults();
                $vh = new VehicleModel();
                $ctx['lines'][] = 'Vehicles: ' . $vh->where('is_archived', 0)->countAllResults() . ' total | ' . (new VehicleModel())->where('is_archived', 0)->where('availability', 'Available')->countAllResults() . ' available | ' . (new VehicleModel())->where('is_archived', 0)->where('gps_status', 'Online')->countAllResults() . ' online on GPS';
                $ctx['lines'][] = 'Trips today: ' . (new \App\Models\TravelModel())->where('travel_date', $today)->where('is_archived', 0)->countAllResults() . ' | in transit: ' . (new \App\Models\TravelModel())->where('status', 'In Transit')->countAllResults() . ' | awaiting approval: ' . (new \App\Models\TravelModel())->whereIn('status', ['Submitted', 'Reviewed'])->countAllResults();
                $ctx['lines'][] = 'Safety inspections this month: ' . (new \App\Models\SafetyInspectionModel())->where('inspection_month', $month)->countAllResults() . ' recorded | ' . (new \App\Models\SafetyInspectionModel())->where('inspection_month', $month)->where('safety_status', 'Unsafe')->countAllResults() . ' marked unsafe';
            } elseif ($dept === 'assets') {
                $v = new VehicleModel();
                $ctx['lines'][] = 'Vehicles: ' . $v->where('is_archived', 0)->countAllResults() . ' total | ' . (new VehicleModel())->where('is_archived', 0)->where('availability', 'Available')->countAllResults() . ' available | ' . (new VehicleModel())->where('is_archived', 0)->whereIn('inspection_status', ['Due Soon', 'Expired'])->countAllResults() . ' with inspection due or expired';
                $mp = new \App\Models\MotorpoolWorkOrderModel();
                $ctx['lines'][] = 'Motor pool work orders: ' . $mp->whereIn('status', ['Pending', 'In Progress'])->countAllResults() . ' open | ' . (new \App\Models\MotorpoolWorkOrderModel())->whereIn('status', ['Pending', 'In Progress'])->where('priority', 'Urgent')->countAllResults() . ' urgent';
                $ctx['lines'][] = 'Mechanical equipment needing repair: ' . (new \App\Models\MechanicalEquipmentModel())->whereIn('status', ['Needs Repair', 'Out of Service'])->countAllResults();
                $t = new \App\Models\TravelModel();
                $ctx['lines'][] = 'Trips today: ' . $t->where('travel_date', $today)->where('is_archived', 0)->countAllResults() . ' | in transit: ' . (new \App\Models\TravelModel())->where('status', 'In Transit')->where('is_archived', 0)->countAllResults();
            } elseif ($dept === 'sports') {
                $tm = new ToolsModel();
                $ctx['lines'][] = 'Sports equipment: ' . $tm->where('category', 'Sports Equipment')->where('is_archived', 0)->countAllResults() . ' total | ' . (new ToolsModel())->where('category', 'Sports Equipment')->where('is_archived', 0)->where('availability', 'Available')->countAllResults() . ' available | ' . (new ToolsModel())->where('category', 'Sports Equipment')->where('is_archived', 0)->where('availability', 'Borrowed')->countAllResults() . ' borrowed';
                $ids = array_column((new ToolsModel())->where('category', 'Sports Equipment')->findAll(), 'id');
                $overdue = $ids ? (new \App\Models\BorrowModel())->whereIn('tool_id', $ids)->where('status', 'Borrowed')->where('expected_return <', $today)->countAllResults() : 0;
                $ctx['lines'][] = 'Overdue returns: ' . $overdue;
                $ctx['lines'][] = 'Sports equipment in poor condition: ' . (new ToolsModel())->where('category', 'Sports Equipment')->where('condition_status', 'Poor')->countAllResults();
            } elseif ($dept === 'janitorial') {
                $zc = (new \App\Models\JanitorialAssignmentModel())->getZoneCleanCounts();
                $ctx['lines'][] = 'Cleaning zones cleaned today: ' . $zc['cleaned'] . '/' . $zc['total'];
                $ctx['lines'][] = 'Cleaning tasks done: ' . (new \App\Models\JanitorialTaskModel())->where('is_done', 1)->countAllResults() . ' / ' . (new \App\Models\JanitorialTaskModel())->countAllResults();
            } elseif ($dept === 'tools') {
                $ctx['lines'][] = 'Tools and equipment: ' . (new ToolsModel())->where('is_archived', 0)->countAllResults() . ' total | ' . (new ToolsModel())->where('is_archived', 0)->where('availability', 'Borrowed')->countAllResults() . ' borrowed';
            }
        } catch (\Throwable $e) {
            $ctx['lines'][] = '(some numbers are unavailable right now)';
        }
        $ctx['current_date'] = date('l, F j, Y');
        $ctx['current_time'] = date('h:i A');
        $ctx['role']         = (string) ($this->session->get('role') ?? '');

        return $ctx;
    }

    private function buildSystemPrompt(array $ctx): string
    {
        $profile = UbraScope::profile($ctx['dept'] ?? null);
        if ($profile === null) return $this->buildFullSystemPrompt($ctx);

        return "You are Mr. UBRA, the assistant for UBRA — Foundation University's Buildings and Grounds management system. You are speaking with a {$ctx['role']} account of **{$profile['name']}**.\n\n"
            . "CURRENT SNAPSHOT FOR THIS DEPARTMENT ({$ctx['current_date']} {$ctx['current_time']}):\n- " . implode("\n- ", $ctx['lines']) . "\n\n"
            . "WHAT YOU CAN HELP WITH: {$profile['scope']}. Point people to the right page of their own portal when it helps.\n\n"
            . "STRICT SCOPE — read carefully:\n"
            . "- You only answer about {$profile['name']}. If the user asks about any other department, other staff accounts, other departments' records or numbers, system settings, API keys, passwords or user accounts, politely decline in one or two sentences and offer to help with their own department instead. Never guess, hint at, or reveal anything from outside this department.\n"
            . "- The snapshot above is the only data you have. Do not claim to know campus-wide figures.\n\n"
            . "HOW TO FOLLOW INSTRUCTIONS:\n"
            . "- Read the user's message literally and do exactly what it asks; don't substitute a similar request or add unrequested extras.\n"
            . "- If a request is genuinely ambiguous, ask one short clarifying question instead of guessing.\n\n"
            . "DON'T GUESS AT DATA: Only state a specific number, name, ID, or date if it is in the snapshot above or in the conversation itself. If you don't have a detail, say to check the relevant page of their portal instead of inventing one.\n\n"
            . "FILE REPORTS: You cannot attach files yourself. A real downloadable link is produced automatically when the user names a format (pdf/excel/csv) together with 'report' or 'summary' (e.g. 'generate a pdf report this month'). If asked for a file, tell them to phrase it that way.\n\n"
            . "Be concise, professional, and action-oriented. Use bullet points and bold for key figures. Never use filler or promise-style wording such as 'shortly', 'in a moment' or 'right away'; just give the answer.";
    }

    private function buildFullSystemPrompt(array $ctx): string
    {
        return "You are Mr. UBRA, the Intelligent Operations Assistant for UBRA — Foundation University's Buildings and Grounds Integrated Management System.\n\n"
            . "CURRENT SYSTEM SNAPSHOT ({$ctx['current_date']} {$ctx['current_time']}, viewer role: " . ($ctx['role'] ?: 'unknown') . "):\n"
            . "- Vehicles: " . ($ctx['total_vehicles'] ?? 0) . " total | " . ($ctx['available_vehicles'] ?? 0) . " available\n"
            . "- Personnel: " . ($ctx['total_personnel'] ?? 0) . " total | " . ($ctx['on_duty'] ?? 0) . " on duty\n"
            . "- Equipment: " . ($ctx['total_assets'] ?? 0) . " total | " . ($ctx['borrowed_tools'] ?? 0) . " currently borrowed\n"
            . "- Safety: " . ($ctx['overdue_fire_extinguishers'] ?? 0) . " overdue fire extinguisher inspections | " . ($ctx['open_work_orders'] ?? 0) . " open maintenance work orders\n"
            . "- Janitorial: " . ($ctx['janitorial_zones_cleaned'] ?? 0) . "/" . ($ctx['janitorial_zones_total'] ?? 0) . " zones cleaned today\n"
            . "- Travel: " . ($ctx['pending_trip_tickets'] ?? 0) . " trip tickets awaiting dispatch/approval\n"
            . (!empty($ctx['extra_lines']) ? "- " . implode("\n- ", $ctx['extra_lines']) . "\n" : '') . "\n"
            . "MODULES IN THIS SYSTEM (so you can point users to the right place):\n"
            . "- Facilities (sidebar group) — Aircon Care, Waste Watch (daily weighing of 3 bins per building: non-biodegradable, biodegradable, hazardous; supervisors weigh and log at the end of the day), Building Check (monthly result per building), Janitorial/cleaning.\n"
            . "- Safety and Security (sidebar group) — Fire Safety (extinguishers, smoke detectors, alarms, exit signs), Safety Inspection, Guard Monitoring (the guard lends and receives keys by scanning staff ID and key tag) and List of Keys with QR codes.\n"
            . "- Sports Equipment and Assets (vehicles, motor pool, trip tickets, GPS) department portals.\n"
            . "- SMS Outbox (Notifications) — every text made for staff; Simulated until an SMS gateway is connected. Voice reminders read urgent alerts aloud on the Notifications page.\n"
            . "- Dashboard — campus-wide KPI overview (pending requests, active borrowings, vehicles in use, maintenance due, cleaning completion).\n"
            . "- Personnel Management — staff records, by category (Drivers, Janitors, Carpentries Shop, Maintenance, Construction Workers), Job Order personnel, contracts.\n"
            . "- Vehicle Management — fleet records, fuel logs, fuel-need predictions; GPS Tracker (live status) and Trip Ticket (travel requests/dispatch) live under the same section.\n"
            . "- Equipment Management — all tools/equipment by category (Power Tools, Supplies & Materials), borrowing ledger.\n"
            . "- Maintenance (Safety) — fire extinguisher and aircon condition tracked on a campus map by building/floor, plus maintenance work orders; Guard page handles gate check-in/out.\n"
            . "- Janitorial Monitoring — cleaning zones by building (and now by floor within a building), staff shift assignments, consumable inventory/refill log.\n"
            . "- Calendar — scheduling for cleaning and maintenance.\n"
            . "- Notifications — system alerts, scoped by role (e.g. a Facilities or Security account only sees notifications relevant to their own modules).\n"
            . "- Reports — cross-system activity/records log and report exports (PDF/Excel/CSV).\n"
            . "- Settings — general/AI/email/notification configuration and user accounts (Administrator and Security Head roles only).\n\n"
            . "ROLES: Administrator sees everything. Security Head, Tools Head (\"Maintenance\"), Facilities Supervisor, and Janitorial Supervisor each have a restricted sidebar/dashboard scoped to their own area — if a user with one of these roles asks about a module they can't see, explain that it's outside their role's access rather than assuming something is broken.\n\n"
            . "HOW TO FOLLOW INSTRUCTIONS:\n"
            . "- Read the user's message literally and do exactly what it asks — don't substitute a similar-sounding request, don't add unrequested extras, and don't skip a stated detail (a date range, a module, a format, a name).\n"
            . "- If a request is genuinely ambiguous (could reasonably mean two different things), ask a short clarifying question instead of guessing.\n"
            . "- If asked to redo or correct something, treat the correction as replacing your prior approach, not adding to it.\n\n"
            . "DON'T GUESS AT DATA: Only state a specific number, name, ID, or date if it's in the snapshot above or the conversation itself. If asked for a detail you don't have (e.g. a specific unit's exact status, a particular person's schedule), say to check the relevant module page rather than inventing a plausible-sounding answer.\n\n"
            . "FILE REPORTS: You can never generate or attach a file yourself — that's handled separately, before your reply is even generated, whenever a message names a format (pdf/excel/csv/spreadsheet) together with 'report' or 'summary'. If you are ever the one responding to a file/report/export request, that means the automatic handler didn't recognize it — do NOT say you cannot attach files. Instead, tell the user plainly: mention a format together with the word 'report' or 'summary' (e.g. 'generate a pdf report for tools this month') and a real downloadable link will be produced instead of this reply.\n\n"
            . "Be concise, professional, and action-oriented. Use bullet points and bold for key figures. Never use filler or promise-style wording such as 'shortly', 'in a moment' or 'right away'; just give the answer. Stay focused on UBRA operations only.";
    }

    private function getApiKey(): string
    {
        // .env's AI_API_KEY takes priority — lets a key be pinned per
        // environment without going through the Settings UI. Falls back to
        // whatever's saved in Settings -> AI Configuration otherwise.
        $envKey = trim((string) (env('AI_API_KEY') ?? ''));
        if ($envKey !== '') {
            return $envKey;
        }

        try {
            $db = \Config\Database::connect();

            // Settings' AI Configuration tab saves here now — this is the
            // real lookup Mr. UBRA uses. 'api_key' is a different setting
            // entirely (the Google Calendar key on the General tab); the
            // other two are kept as fallbacks only because older saves
            // before this field existed may have landed in one of them.
            foreach (['ai_api_key', 'anthropic_api_key', 'openai_api_key'] as $key) {
                $row = $db->table('system_settings')->where('setting_key', $key)->get()->getRow();
                $value = trim($row->setting_value ?? '');
                if ($value !== '') {
                    return $value;
                }
            }
            return '';
        } catch (\Exception $e) {
            return '';
        }
    }
}
?>