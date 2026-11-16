<?php

namespace App\Controllers;

use App\Models\NotificationModel;
use App\Models\PersonnelModel;
use App\Models\FireExtinguisherModel;

class NotificationController extends BaseController
{
    protected $notifModel;
    protected $session;

    public function __construct()
    {
        $this->notifModel = new NotificationModel();
        $this->session    = \Config\Services::session();
    }

    public function index()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $role   = (string) $this->session->get('role');
        $all    = NotificationModel::scopeToRole($this->notifModel->getAllSorted(), $role);
        $drafts = NotificationModel::scopeToRole($this->notifModel->getDrafts(), $role);
        $today  = date('Y-m-d');
        $todaysAlerts = array_filter($all, fn($n) => substr($n['created_at'] ?? '', 0, 10) === $today);

        foreach ($all as &$n) { $n['_kind'] = 'live'; }
        unset($n);
        foreach ($drafts as &$n) { $n['_kind'] = 'draft'; }
        unset($n);
        $rows = array_merge($all, $drafts);
        usort($rows, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        $supervisorList = $this->getCoSupervisors();
        $driverList     = $this->getPersonnelByPosition('Driver');
        $maintList      = $this->getPersonnelByPosition('Maintenance');
        $janitorList    = $this->getPersonnelByPosition(['Janitor', 'Cleaning']);
        // Everyone else on the active list (office staff, guards, ...) so any person can be picked as a recipient.
        $listed = array_flip(array_map('strtolower', array_merge(
            array_column($supervisorList, 'name'), array_column($driverList, 'name'),
            array_column($maintList, 'name'), array_column($janitorList, 'name')
        )));
        $otherStaff = array_values(array_filter(
            array_map(fn($p) => ['name' => $p['full_name'], 'position' => $p['position'], 'contactNumber' => $p['contact_number'] ?? ''],
                (new PersonnelModel())->where('is_archived', 0)->where('status !=', 'Inactive')->orderBy('full_name', 'ASC')->findAll()),
            fn($p) => !isset($listed[strtolower($p['name'])])
        ));

        $data = [
            'title'          => 'Notification Center',
            'notifications'  => $rows,
            'unread_count'   => count(array_filter($all, fn($n) => (int) ($n['is_read'] ?? 0) === 0)),
            'today_count'    => count($todaysAlerts),
            'today_done_count' => count(array_filter($todaysAlerts, fn($n) => ($n['status'] ?? 'Pending') !== 'Pending')),
            'upcoming_count' => count(array_filter($all, fn($n) => strtolower($n['priority'] ?? '') === 'routine')),
            'draft_count'    => count($drafts),
            'supervisors'    => $supervisorList,
            'otherStaff'     => $otherStaff,
            'fireInspectors' => $this->getFireExtinguisherInspectors(),
            'drivers'        => $driverList,
            'maintenanceStaff' => $maintList,
            'janitors'       => $janitorList,
        ];

        return view('notifications/index', $data);
    }

    // "Co-supervisors" — anyone holding a supervisory-level position (chiefs,
    // heads, and the physical plant supervisors), for the Recipient dropdown.
    private function getCoSupervisors(): array
    {
        $personnel = new PersonnelModel();
        $rows = $personnel->where('is_archived', 0)
            ->groupStart()
                ->like('position', 'Supr')
                ->orLike('position', 'Chief')
                ->orLike('position', 'Head')
            ->groupEnd()
            ->orderBy('full_name', 'ASC')
            ->findAll();

        return array_map(fn($p) => ['name' => $p['full_name'], 'position' => $p['position']], $rows);
    }

    // Drivers, Maintenance, and Janitorial staff — used for the "New Message"
    // Recipient dropdown so a message can actually be sent to one of them by
    // name, not just to supervisors/fire inspectors. Accepts one position
    // keyword or several (e.g. Janitor/Cleaning Operative are both
    // "janitorial" for this purpose).
    private function getPersonnelByPosition($positionLike): array
    {
        $keywords = (array) $positionLike;
        $personnel = new PersonnelModel();
        $query = $personnel->where('is_archived', 0)->groupStart();
        foreach ($keywords as $i => $kw) {
            $i === 0 ? $query->like('position', $kw) : $query->orLike('position', $kw);
        }
        $rows = $query->groupEnd()->orderBy('full_name', 'ASC')->findAll();

        return array_map(fn($p) => [
            'name'          => $p['full_name'],
            'position'      => $p['position'],
            'contactNumber' => $p['contact_number'] ?? '',
        ], $rows);
    }

    // The specific person assigned to check each fire extinguisher unit —
    // pulled from fire_extinguishers.assigned_guard so the recipient is tied
    // to an actual unit/location, not just a generic "guard" pick.
    private function getFireExtinguisherInspectors(): array
    {
        $rows = (new FireExtinguisherModel())
            ->select('assigned_guard, unit_id, location')
            ->where('assigned_guard IS NOT NULL')
            ->where('assigned_guard !=', '')
            ->orderBy('assigned_guard', 'ASC')
            ->findAll();

        $seen = [];
        $out  = [];
        foreach ($rows as $r) {
            $key = $r['assigned_guard'] . '|' . $r['unit_id'];
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $out[] = ['name' => $r['assigned_guard'], 'unit_id' => $r['unit_id'], 'location' => $r['location']];
        }
        return $out;
    }

    public function markRead($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }
        $this->notifModel->update($id, ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['success' => true]);
    }

    public function markAllRead()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');
        $this->notifModel->markAllRead();
        $this->session->setFlashdata('success', 'All notifications marked as read.');
        return redirect()->to('/notifications');
    }

    public function dismiss($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }
        $this->notifModel->update($id, ['status' => 'Dismissed', 'is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['success' => true]);
    }

    public function action($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }
        $act = ucfirst($this->request->getPost('action') ?? 'reviewed');
        $notif = $this->notifModel->find($id);
        $this->notifModel->update($id, ['status' => $act, 'is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
        $module = ucfirst(NotificationModel::CATEGORY_MODULE[$notif['category'] ?? ''] ?? 'safety');
        $this->logActivity($module, "{$act} notification: " . ($notif['category'] ?? "#{$id}"));
        return $this->response->setJSON(['success' => true, 'new_status' => $act]);
    }

    public function saveDraft()
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }
        $description = trim((string) $this->request->getPost('description'));
        if ($description === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Message details are required.']);
        }
        $priority = strtoupper((string) $this->request->getPost('priority'));
        if (!in_array($priority, ['CRITICAL', 'MODERATE', 'ROUTINE'], true)) {
            $priority = 'ROUTINE';
        }
        $this->notifModel->insert([
            'category'    => trim((string) $this->request->getPost('category')) ?: 'General Message',
            'description' => $description,
            'recipient'   => trim((string) $this->request->getPost('recipient')) ?: 'Operations Team',
            'priority'    => $priority,
            'status'      => 'Draft',
            'channel'     => $this->request->getPost('text_sms') === '1' ? 'sms' : 'system', // 'sms' = also text the person on send
            'is_read'     => 1,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        $this->logActivity('Notifications', "Drafted message to " . (trim((string) $this->request->getPost('recipient')) ?: 'Operations Team'));
        return $this->response->setJSON(['success' => true]);
    }

    public function sendDraft($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }
        $row = $this->notifModel->find($id);
        if (!$row || $row['status'] !== 'Draft') {
            return $this->response->setJSON(['success' => false, 'message' => 'Draft not found.']);
        }
        $this->notifModel->update($id, [
            'status'     => 'Pending',
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->logActivity('Notifications', 'Sent message to ' . ($row['recipient'] ?? 'recipient'));

        // "Also text this person": only when a single person was picked (a group such as Operations Team has no phone).
        $smsNote = null;
        if (($row['channel'] ?? '') === 'sms') {
            $person = (new PersonnelModel())->where('full_name', $row['recipient'])->where('is_archived', 0)->first();
            if ($person) {
                $text = 'FU-UBRA' . (($row['category'] ?? '') !== '' ? ' - ' . $row['category'] : '') . ': ' . $row['description'];
                $r = (new \App\Libraries\SmsGateway())->deliver((int) $person['id'], $person['full_name'], $person['contact_number'] ?? '', $text, 'Notification message');
                $smsNote = $r['message'];
                // Real delivery: Telegram, when the bot is set up and the person has linked it.
                $bot = new \App\Libraries\TelegramBot();
                if ($bot->isConfigured()) {
                    $t = $bot->notifyPerson($person, $text);
                    $smsNote .= ' Telegram: ' . $t['message'];
                }
            } else {
                $smsNote = 'No text made: "' . $row['recipient'] . '" is a group, not one person.';
            }
        }
        return $this->response->setJSON(['success' => true, 'sms' => $smsNote]);
    }

    public function deleteDraft($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }
        $row = $this->notifModel->find($id);
        if ($row && $row['status'] === 'Draft') {
            $this->notifModel->delete($id);
        }
        return $this->response->setJSON(['success' => true]);
    }

    /**
     * GET /notifications/pulse - one cheap change marker covering the operational
     * tables (borrows, returns, keys, trips, tools, vehicles, safety, janitorial,
     * personnel...). The layout polls it every couple of seconds and refreshes
     * the current page when it changes. Lives under /notifications so every role
     * is allowed to call it. Chatty tables (GPS pings, logs, notifications
     * themselves) are left out on purpose so they don't cause constant refreshes.
     */
    public function pulse()
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $watched = [
            'aircon_checklist_items', 'aircon_inspection_entries', 'aircon_inspection_logs', 'aircon_units',
            'borrow_records', 'return_records', 'consumable_inventory', 'consumable_stock_reports', 'disposal_logs',
            'equipment_maintenance_entries', 'equipment_maintenance_logs', 'facility_checklist_items', 'facility_checklists',
            'facility_keys', 'key_borrow_logs', 'fire_extinguishers', 'fuel_logs',
            'janitorial', 'janitorial_assignments', 'janitorial_inspections', 'janitorial_task_history', 'janitorial_tasks',
            'job_orders', 'mechanical_equipment', 'motorpool_wo_history', 'motorpool_work_orders',
            'personnel', 'personnel_assignments', 'personnel_contracts', 'personnel_documents',
            'refill_log', 'reports', 'restroom_checklist_entries', 'restroom_checklists',
            'safety_equipment', 'safety_inspections', 'safety_reports', 'safety_work_orders',
            'tools', 'tools_refill_log', 'travel_requests', 'trip_status_log',
            'vehicle_inspection_checklists', 'vehicle_inspection_items', 'vehicle_maintenance', 'vehicles', 'work_orders',
        ];

        // Shared across everyone for a second, so many open pages cost one checksum, not many.
        if (($cached = cache('pulse_marker')) !== null) {
            return $this->response->setHeader('Cache-Control', 'no-store')->setJSON(['v' => $cached]);
        }

        $db = db_connect();
        $existing = array_map('current', $db->query('SHOW TABLES')->getResultArray());
        $tables = array_values(array_intersect($watched, $existing));
        if (!$tables) {
            return $this->response->setJSON(['v' => '']);
        }

        $list = implode(', ', array_map(fn($t) => $db->escapeIdentifiers($t), $tables));
        $sums = array_map(fn($r) => ($r['Table'] ?? '') . ':' . ($r['Checksum'] ?? ''), $db->query('CHECKSUM TABLE ' . $list)->getResultArray());

        $marker = md5(implode('|', $sums));
        cache()->save('pulse_marker', $marker, 1);

        return $this->response->setHeader('Cache-Control', 'no-store')->setJSON(['v' => $marker]);
    }

    public function unreadCount()
    {
        if (!$this->session->get('isLoggedIn')) return $this->response->setJSON(['count' => 0, 'latest' => []]);
        $role = (string) $this->session->get('role');
        $unread = array_filter(
            NotificationModel::scopeToRole($this->notifModel->getAllSorted(), $role),
            fn($n) => (int) ($n['is_read'] ?? 0) === 0
        );

        // Newest-first, capped small — the frontend only needs enough to pop
        // a toast per notification it hasn't shown yet, not the full list.
        $latest = array_slice(array_values($unread), 0, 10);
        // Missed = CRITICAL and still not acted on (Pending / Notified). Opening or reading one does NOT silence the
        // voice alarm; only an action (Verify, Approve, Assign, Dismiss...) does. Capped, newest first.
        $critical = array_slice(array_values(array_filter(
            NotificationModel::scopeToRole($this->notifModel->getAllSorted(), $role),
            fn($n) => strtoupper($n['priority'] ?? '') === 'CRITICAL'
                && in_array(strtolower((string) ($n['status'] ?? '')), ['pending', 'notified', ''], true)
        )), 0, 20);
        $shape = fn($n) => [
            'id'          => (int) $n['id'],
            'category'    => $n['category'] ?? 'Notification',
            'description' => $n['description'] ?? '',
            'priority'    => strtoupper($n['priority'] ?? 'ROUTINE'),
            'created_at'  => $n['created_at'] ?? null,
        ];

        return $this->response->setJSON([
            'count'  => count($unread),
            'critical' => array_map($shape, $critical),
            'latest' => array_map(fn($n) => [
                'id'          => (int) $n['id'],
                'category'    => $n['category'] ?? 'Notification',
                'description' => $n['description'] ?? '',
                'priority'    => strtoupper($n['priority'] ?? 'ROUTINE'),
                'created_at'  => $n['created_at'] ?? null,
            ], $latest),
        ]);
    }

    public function export()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');
        $role = (string) $this->session->get('role');
        $all  = NotificationModel::scopeToRole($this->notifModel->getAllSorted(), $role);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="notifications_' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID','Category','Description','Recipient','Priority','Status']);
        foreach ($all as $n) {
            fputcsv($out, [$n['id'], $n['category'], $n['description'], $n['recipient'], $n['priority'], $n['status']]);
        }
        fclose($out);
        exit;
    }

    // GET /notifications/sms-outbox - every text the system sent or would send (administrator only).
    public function smsOutbox()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');
        if (strtolower((string) $this->session->get('role')) !== 'administrator') return redirect()->to('/notifications');

        $rows = db_connect()->table('sms_outbox')->orderBy('created_at', 'DESC')->orderBy('id', 'DESC')->limit(200)->get()->getResultArray();
        return view('notifications/sms_outbox', [
            'title'      => 'SMS Outbox',
            'pageCss'    => 'notifications.css',
            'showTopbar' => true,
            'rows'       => $rows,
            'live'       => (new \App\Libraries\SmsGateway())->isConfigured(),
        ]);
    }
}
