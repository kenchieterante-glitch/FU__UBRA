<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiTokenModel extends Model
{
    protected $table         = 'api_tokens';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'user_id', 'token_hash', 'device_name', 'last_used_at', 'expires_at',
    ];

    public function issueToken(int $userId, ?string $deviceName = null): string
    {
        $plainToken = bin2hex(random_bytes(32));

        $this->insert([
            'user_id'      => $userId,
            'token_hash'   => hash('sha256', $plainToken),
            'device_name'  => $deviceName,
            'last_used_at' => date('Y-m-d H:i:s'),
            'expires_at'   => date('Y-m-d H:i:s', strtotime('+30 days')),
        ]);

        return $plainToken;
    }

    public function findByPlainToken(string $plainToken): ?array
    {
        return $this->where('token_hash', hash('sha256', $plainToken))
            ->where('(expires_at IS NULL OR expires_at > NOW())', null, false)
            ->first();
    }

    public function touch(int $tokenId): void
    {
        $this->update($tokenId, ['last_used_at' => date('Y-m-d H:i:s')]);
    }

    public function revokeByPlainToken(string $plainToken): void
    {
        $this->where('token_hash', hash('sha256', $plainToken))->delete();
    }
}
