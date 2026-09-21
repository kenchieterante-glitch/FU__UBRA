<?php

namespace App\Models;

use CodeIgniter\Model;

class FacilityKeyModel extends Model
{
    protected $table         = 'facility_keys';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['key_name', 'location', 'nfc_uid', 'status'];

    public function findByNfcUid(string $uid): ?array
    {
        return $this->where('nfc_uid', $uid)->first();
    }
}
