<?php

namespace App\Controllers;

use App\Models\VehicleModel;
use App\Models\PersonnelModel;
use App\Models\ToolsModel;

class UbraController extends BaseController
{
    protected $session;

    public function __construct()
    {
        $this->session = \Config\Services::session();
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

        $context = $this->buildContext();
        $apiKey  = $this->getApiKey();

        if (empty($apiKey)) {
            return $this->response->setJSON([
                'reply' => "⚠️ No API key configured. Please add your Anthropic API key in **Settings → General → API Integration Keys** to activate Mr. UBRA AI.",
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

        $payload = json_encode([
            'model'      => 'claude-sonnet-4-6',
            'max_tokens' => 1000,
            'system'     => $this->buildSystemPrompt($context),
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

        if ($httpCode !== 200 || !$response) {
            return $this->response->setJSON([
                'reply' => "I'm having trouble connecting right now. Please try again.",
                'role'  => 'assistant',
            ]);
        }

        $data  = json_decode($response, true);
        $reply = $data['content'][0]['text'] ?? "I couldn't generate a response.";

        return $this->response->setJSON(['reply' => $reply, 'role' => 'assistant']);
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
        return "You are Mr. UBRA, the Intelligent Operations Assistant for FU-UBRA — Foundation University's Buildings and Grounds Integrated Management System.\n\n"
            . "CURRENT SYSTEM SNAPSHOT ({$ctx['current_date']} {$ctx['current_time']}):\n"
            . "- Vehicles: " . ($ctx['total_vehicles'] ?? 0) . " total | " . ($ctx['available_vehicles'] ?? 0) . " available\n"
            . "- Personnel: " . ($ctx['total_personnel'] ?? 0) . " total | " . ($ctx['on_duty'] ?? 0) . " on duty\n"
            . "- Assets: " . ($ctx['total_assets'] ?? 0) . "\n\n"
            . "Be concise, professional, and action-oriented. Use bullet points and bold for key figures. Stay focused on FU-UBRA operations only.";
    }

    private function getApiKey(): string
    {
        try {
            $db  = \Config\Database::connect();
            $row = $db->table('system_settings')->where('setting_key', 'api_key')->get()->getRow();
            return trim($row->setting_value ?? '');
        } catch (\Exception $e) {
            return '';
        }
    }
}
?>