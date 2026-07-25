<?php
namespace App\Models;
use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'category', 'description', 'recipient', 'priority', 'status', 'channel', 'is_read', 'read_at', 'created_at'
    ];

    public function getAllSorted()
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }

    public function getUnreadCount()
    {
        return $this->where('status', 'Unread')->countAllResults();
    }

    public function markAllRead()
    {
        $this->where('status', 'Unread')->set(['status' => 'Read', 'is_read' => 1])->update();
    }
}
