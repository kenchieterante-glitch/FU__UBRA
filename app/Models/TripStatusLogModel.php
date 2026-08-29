<?php
namespace App\Models;
use CodeIgniter\Model;

// Insert-only by design — no update()/delete() calls are ever made against
// this table anywhere in the app. Every trip status change gets its own
// permanent row here so the ticket's status history can never be edited
// or backdated after the fact.
class TripStatusLogModel extends Model
{
    protected $table         = 'trip_status_log';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'travel_request_id', 'status', 'changed_by', 'changed_at', 'notes',
    ];

    public function getForTrip(int $tripId): array
    {
        return $this->where('travel_request_id', $tripId)->orderBy('changed_at', 'ASC')->orderBy('id', 'ASC')->findAll();
    }
}
