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

        $all   = $this->notifModel->getAllSorted();
        $today = date('Y-m-d');
        $todaysAlerts = array_filter($all, fn($n) => substr($n['created_at'] ?? '', 0, 10) === $today);

        $data = [
            'title'          => 'Notification Center',
            'notifications'  => $all,
            'unread_count'   => count(array_filter($all, fn($n) => (int) ($n['is_read'] ?? 0) === 0)),
            'today_count'    => count($todaysAlerts),
            'today_done_count' => count(array_filter($todaysAlerts, fn($n) => ($n['status'] ?? 'Pending') !== 'Pending')),
            'upcoming_count' => count(array_filter($all, fn($n) => strtolower($n['priority'] ?? '') === 'routine')),
            'draft_count'    => 0,
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

    public function unreadCount()
    {
        if (!$this->session->get('isLoggedIn')) return $this->response->setJSON(['count' => 0]);
        return $this->response->setJSON(['count' => $this->notifModel->getUnreadCount()]);
    }

    public function export()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');
        $all = $this->notifModel->getAllSorted();
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
