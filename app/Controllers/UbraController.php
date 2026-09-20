<?php

namespace App\Controllers;

use App\Models\VehicleModel;
use App\Models\PersonnelModel;
use App\Models\ToolsModel;
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

        // A plain text-in/text-out AI API call has no way to actually produce
        // a file — that's why it was replying "I cannot attach a PDF" instead
        // of just doing it. Report requests are handled here in PHP instead,
        // deterministically, using the same export engine Records, Archiving
        // & Reports and the Calendar's "Generate Weekly Summary" already use,
        // so Mr. UBRA hands back a real, working download link.
        $reportRequest = $this->detectReportRequest($userMessage);
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
            return $this->response->setJSON([
                'reply' => "I'm having trouble connecting right now. Please try again.",
                'role'  => 'assistant',
            ]);
        }

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
        $_POST['message'] = $message;
        $_POST['history'] = '[]';

        return $this->chat();
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
            'vehicle'    => ['vehicle', 'vehicles', 'fleet', 'driver', 'drivers', 'trip ticket'],
            'tools'      => ['tool', 'tools', 'equipment', 'borrow', 'borrowing'],
            'safety'     => ['safety', 'maintenance', 'fire extinguisher', 'aircon', 'work order'],
            'janitorial' => ['janitorial', 'janitor', 'cleaning'],
            'personnel'  => ['personnel', 'staff', 'employee', 'job order'],
        ];
        $module = '';
        foreach ($moduleMap as $slug => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($m, $kw)) { $module = $slug; break 2; }
            }
        }

        $today = new \DateTime();
        $months = ['january','february','march','april','may','june','july','august','september','october','november','december'];
        $namedMonth = null;
        foreach ($months as $idx => $monthName) {
            if (preg_match('/\b' . $monthName . '\b/', $m)) {
                $namedMonth = $idx + 1;
                break;
            }
        }

        if ($namedMonth !== null) {
            // "the month of august" / "august 2025" — a year in the message
            // wins; otherwise assume the current year, whether that month
            // has already passed or not, since naming a month at all is a
            // more specific signal than any relative default.
            $year = (int) date('Y');
            if (preg_match('/\b(20\d{2})\b/', $m, $ym)) {
                $year = (int) $ym[1];
            }
            $from = new \DateTime(sprintf('%04d-%02d-01', $year, $namedMonth));
            $to   = (clone $from)->modify('last day of this month');
        } elseif (preg_match('/\btoday\b/', $m)) {
            $from = $to = clone $today;
        } elseif (preg_match('/\b(this|last)\s+month\b/', $m)) {
            $from = (clone $today)->modify('first day of this month');
            $to   = clone $today;
        } elseif (preg_match('/\blast\s+week\b/', $m)) {
            $from = (clone $today)->modify('-13 days');
            $to   = (clone $today)->modify('-7 days');
        } else {
            // Default span when no timeframe is mentioned — matches the
            // Calendar's "Generate Weekly Summary" default.
            $from = (clone $today)->modify('-6 days');
            $to   = clone $today;
        }

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
        $url = base_url('records/export/' . $req['format']) . '?' . http_build_query([
            'module'    => $req['module'],
            'date_from' => $req['date_from'],
            'date_to'   => $req['date_to'],
        ]);

        $moduleLabel = $req['module'] !== '' ? ucfirst($req['module']) : 'All modules';
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

    private function buildContext(): array
    {
        $ctx = [];
        try {
            $ctx['total_vehicles']    = (new VehicleModel())->countAll();
            $ctx['available_vehicles']= (new VehicleModel())->where('availability','Available')->countAllResults();
            $ctx['total_personnel']   = (new PersonnelModel())->countAll();
            $ctx['on_duty']           = (new PersonnelModel())->where('status','Active')->countAllResults();
            $ctx['total_assets']      = (new ToolsModel())->countAll();
        } catch (\Exception $e) {
            $ctx = ['error' => 'Some context unavailable'];
        }
        $ctx['current_date'] = date('l, F j, Y');
        $ctx['current_time'] = date('h:i A');
        return $ctx;
    }

    private function buildSystemPrompt(array $ctx): string
    {
        return "You are Mr. UBRA, the Intelligent Operations Assistant for UBRA — Foundation University's Buildings and Grounds Integrated Management System.\n\n"
            . "CURRENT SYSTEM SNAPSHOT ({$ctx['current_date']} {$ctx['current_time']}):\n"
            . "- Vehicles: " . ($ctx['total_vehicles'] ?? 0) . " total | " . ($ctx['available_vehicles'] ?? 0) . " available\n"
            . "- Personnel: " . ($ctx['total_personnel'] ?? 0) . " total | " . ($ctx['on_duty'] ?? 0) . " on duty\n"
            . "- Assets: " . ($ctx['total_assets'] ?? 0) . "\n\n"
            . "HOW TO FOLLOW INSTRUCTIONS:\n"
            . "- Read the user's message literally and do exactly what it asks — don't substitute a similar-sounding request, don't add unrequested extras, and don't skip a stated detail (a date range, a module, a format, a name).\n"
            . "- If a request is genuinely ambiguous (could reasonably mean two different things), ask a short clarifying question instead of guessing.\n"
            . "- If asked to redo or correct something, treat the correction as replacing your prior approach, not adding to it.\n\n"
            . "FILE REPORTS: You can never generate or attach a file yourself — that's handled separately, before your reply is even generated, whenever a message names a format (pdf/excel/csv/spreadsheet) together with 'report' or 'summary'. If you are ever the one responding to a file/report/export request, that means the automatic handler didn't recognize it — do NOT say you cannot attach files. Instead, tell the user plainly: mention a format together with the word 'report' or 'summary' (e.g. 'generate a pdf report for tools this month') and a real downloadable link will be produced instead of this reply.\n\n"
            . "Be concise, professional, and action-oriented. Use bullet points and bold for key figures. Stay focused on UBRA operations only.";
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