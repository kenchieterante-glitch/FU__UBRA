<?php

namespace App\Models;

use CodeIgniter\Model;

class UbraChatLogModel extends Model
{
    protected $table         = 'ubra_chat_logs';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['emp_id', 'role', 'message', 'created_at'];

    // Most-recent-first is what a history panel wants to show; the chat
    // itself re-reverses this to chronological order before rendering.
    public function getForUser(string $empId, int $limit = 100): array
    {
        return $this->where('emp_id', $empId)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function logTurn(string $empId, string $role, string $message): void
    {
        $this->insert([
            'emp_id'     => $empId,
            'role'       => $role,
            'message'    => $message,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function clearForUser(string $empId): void
    {
        $this->where('emp_id', $empId)->delete();
    }
}
