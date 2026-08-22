<?php

namespace App\Models;

use App\Libraries\MonitoringStatus;
use CodeIgniter\Model;

class PersonnelDocumentModel extends Model
{
    protected $table         = 'personnel_documents';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'personnel_id', 'document_type_id', 'document_number', 'issue_date',
        'expiration_date', 'file_path', 'verification_status', 'remarks',
        'uploaded_at', 'verified_at',
    ];

    public function getForPersonnel(int $personnelId): array
    {
        $db = $this->db;
        $rows = $db->table('personnel_documents pd')
            ->select('pd.*, drt.name as document_type_name')
            ->join('document_requirement_types drt', 'drt.id = pd.document_type_id', 'left')
            ->where('pd.personnel_id', $personnelId)
            ->orderBy('pd.id', 'DESC')
            ->get()->getResultArray();

        return array_map([$this, 'withDerivedStatus'], $rows);
    }

    // Documents only have two states worth auto-deriving: a VERIFIED
    // document silently lapses into EXPIRED once its expiration date
    // passes. There is no ACTIVE/EXPIRING_SOON concept here (unlike Job
    // Orders/Contracts) — a still-valid VERIFIED document stays VERIFIED.
    // PENDING/REJECTED are reviewer-set workflow states and never auto-change.
    public function withDerivedStatus(array $row): array
    {
        if ($row['verification_status'] === 'VERIFIED'
            && !empty($row['expiration_date'])
            && MonitoringStatus::daysRemaining($row['expiration_date']) < 0
        ) {
            $row['verification_status'] = 'EXPIRED';
        }
        return $row;
    }

    /**
     * Completeness against the configured required document types:
     * verified (non-expired), incomplete/missing, plus a raw count.
     */
    public function completenessForPersonnel(int $personnelId, array $requiredTypes): array
    {
        $docs = $this->getForPersonnel($personnelId);
        $verifiedTypeIds = [];
        foreach ($docs as $d) {
            if ($d['verification_status'] === 'VERIFIED') {
                $verifiedTypeIds[(int) $d['document_type_id']] = true;
            }
        }

        $requiredCount = count($requiredTypes);
        $verifiedCount = 0;
        foreach ($requiredTypes as $type) {
            if (isset($verifiedTypeIds[(int) $type['id']])) {
                $verifiedCount++;
            }
        }

        return [
            'required' => $requiredCount,
            'verified' => $verifiedCount,
            'complete' => $requiredCount > 0 && $verifiedCount >= $requiredCount,
        ];
    }
}
