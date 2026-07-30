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
        return $this->where('is_read', 0)->countAllResults();
    }

    public function markAllRead()
    {
        $this->where('is_read', 0)->set(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')])->update();
    }
}
