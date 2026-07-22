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

        $all = $this->notifModel->getAllSorted();

        $data = [
            'title'          => 'Notification Center',
            'notifications'  => $all,
            'unread_count'   => count(array_filter($all, fn($n) => ($n['status'] ?? '') === 'Unread')),
            'today_count'    => count($all),
            'upcoming_count' => count(array_filter($all, fn($n) => strtolower($n['priority'] ?? '') === 'routine')),
            'critical_count' => count(array_filter($all, fn($n) => strtolower($n['priority'] ?? '') === 'critical')),
            'email_count'    => 0,
            'flash_success'  => $this->session->getFlashdata('success'),
        ];

        return view('notifications/index', $data);
    }

    public function markRead($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }
        $this->notifModel->update($id, ['status' => 'Read']);
        return $this->response->setJSON(['success' => true]);
    }

    public function markAllRead()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');
        $this->notifModel->where('status', 'Unread')->set(['status' => 'Read'])->update();
        $this->session->setFlashdata('success', 'All notifications marked as read.');
        return redirect()->to('/notifications');
    }

    public function dismiss($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }
        $this->notifModel->update($id, ['status' => 'Dismissed']);
        return $this->response->setJSON(['success' => true]);
    }

    public function action($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }
        $act = ucfirst($this->request->getPost('action') ?? 'read');
        $this->notifModel->update($id, ['status' => $act]);
        return $this->response->setJSON(['success' => true, 'new_status' => $act]);
    }

    public function unreadCount()
    {
        if (!$this->session->get('isLoggedIn')) return $this->response->setJSON(['count' => 0]);
        $count = $this->notifModel->where('status', 'Unread')->countAllResults();
        return $this->response->setJSON(['count' => $count]);
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
