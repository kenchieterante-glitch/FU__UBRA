<?php

namespace App\Controllers\Api;

use App\Models\NotificationModel;

class NotificationController extends BaseApiController
{
    protected NotificationModel $notifModel;

    public function __construct()
    {
        $this->notifModel = new NotificationModel();
    }

    public function index()
    {
        $notifications = $this->notifModel->getAllSorted();
        $today = date('Y-m-d');

        return $this->ok([
            'notifications' => $notifications,
            'unread_count'  => $this->notifModel->getUnreadCount(),
            'today_count'   => count(array_filter($notifications, fn ($n) => substr($n['created_at'], 0, 10) === $today)),
        ]);
    }

    public function markRead($id = null)
    {
        $this->notifModel->update($id, ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
        return $this->ok();
    }

    public function markAllRead()
    {
        $this->notifModel->markAllRead();
        return $this->ok();
    }

    public function dismiss($id = null)
    {
        $this->notifModel->update($id, ['status' => 'Dismissed', 'is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
        return $this->ok();
    }

    public function action($id = null)
    {
        $act = ucfirst((string) ($this->request->getPost('action') ?: 'reviewed'));
        $this->notifModel->update($id, ['status' => $act, 'is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
        return $this->ok(['new_status' => $act]);
    }

    public function unreadCount()
    {
        return $this->ok(['count' => $this->notifModel->getUnreadCount()]);
    }
}
