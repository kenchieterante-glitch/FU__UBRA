<?php
namespace App\Models;
use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'category', 'title', 'description', 'recipient_id',
        'recipient_type', 'priority', 'status'
    ];

    public function getUnreadCount()
    {
        return $this->where('status', 'Unread')->countAllResults();
    }

    public function markAllRead()
    {
        $this->where('status', 'Unread')->set('status', 'Read')->update();
    }
}
