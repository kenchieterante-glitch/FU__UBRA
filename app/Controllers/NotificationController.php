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

        $data = [
            'title'          => 'Notification Center',
            'notifications'  => $rows,
            'unread_count'   => count(array_filter($all, fn($n) => (int) ($n['is_read'] ?? 0) === 0)),
            'today_count'    => count($todaysAlerts),
            'today_done_count' => count(array_filter($todaysAlerts, fn($n) => ($n['status'] ?? 'Pending') !== 'Pending')),
            'upcoming_count' => count(array_filter($all, fn($n) => strtolower($n['priority'] ?? '') === 'routine')),
            'draft_count'    => count($drafts),
            'supervisors'    => $this->getCoSupervisors(),
            'fireInspectors' => $this->getFireExtinguisherInspectors(),
            'drivers'        => $this->getPersonnelByPosition('Driver'),
            'maintenanceStaff' => $this->getPersonnelByPosition('Maintenance'),
            'janitors'       => $this->getPersonnelByPosition(['Janitor', 'Cleaning']),
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
            'channel'     => 'system',
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
        return $this->response->setJSON(['success' => true]);
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

        return $this->response->setJSON([
            'count'  => count($unread),
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
}
