<?php

namespace App\Controllers;

use App\Models\NotificationModel;

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
        ];

        return view('notifications/index', $data);
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
        $this->notifModel->update($id, ['status' => $act, 'is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
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
        if (!$this->session->get('isLoggedIn')) return $this->response->setJSON(['count' => 0]);
        $role = (string) $this->session->get('role');
        return $this->response->setJSON(['count' => $this->notifModel->getUnreadCountForRole($role)]);
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
