<?php

namespace App\Controllers\Api;

use App\Models\NotificationModel;
use App\Libraries\ApiAuth;

class NotificationController extends BaseApiController
{
    protected NotificationModel $notifModel;

    public function __construct()
    {
        $this->notifModel = new NotificationModel();
    }

    // Same role field ApiAuthFilter resolved onto the bearer token's user —
    // scoping here has to match the web side (NotificationController::index()
    // / getUnreadCountForRole()) exactly, or a Facilities/Security-restricted
    // account sees a different unread count on mobile than on web for the
    // same underlying rows.
    private function role(): string
    {
        return (string) (ApiAuth::user()['role'] ?? '');
    }

    public function index()
    {
        $notifications = NotificationModel::scopeToRole($this->notifModel->getAllSorted(), $this->role());
        $today = date('Y-m-d');

        return $this->ok([
            'notifications' => $notifications,
            'unread_count'  => count(array_filter($notifications, fn ($n) => (int) ($n['is_read'] ?? 0) === 0)),
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
        return $this->ok(['count' => $this->notifModel->getUnreadCountForRole($this->role())]);
    }
}
