<?php
namespace App\Models;
use CodeIgniter\Model;

class JanitorialAssignmentModel extends Model
{
    protected $table         = 'janitorial_assignments';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'staff_name', 'assigned_zone', 'shift_start', 'shift_end',
        'date_assigned', 'status', 'priority',
    ];

    public function getTodayAssignments()
    {
        return $this->where('date_assigned', date('Y-m-d'))->findAll();
    }

    // Single source of truth for "how many zones are cleaned" — a zone
    // counts as cleaned as soon as ANY one staff member assigned to it has
    // finished all of their own tasks (a zone with several staff, e.g. CCS
    // Building, doesn't wait on everyone). Used by the main Dashboard's
    // "Cleaning Completion" card and the mobile API's summary so neither
    // can drift out of sync with Janitorial Monitoring's own count, which
    // computes this same rule inline (it also needs the richer per-zone
    // checklist detail this method doesn't return).
    public function getZoneCleanCounts(): array
    {
        $tasksByAssignment = [];
        foreach ((new JanitorialTaskModel())->findAll() as $t) {
            $tasksByAssignment[$t['assignment_id']][] = $t;
        }

        $zoneShifts = [];
        foreach ($this->findAll() as $a) {
            $myTasks = $tasksByAssignment[$a['id']] ?? [];
            $done    = count(array_filter($myTasks, fn ($t) => (int) $t['is_done'] === 1));
            $total   = count($myTasks);
            $zoneShifts[$a['assigned_zone']][] = ['done' => $done, 'total' => $total];
        }

        $totalZones   = count($zoneShifts);
        $cleanedZones = count(array_filter($zoneShifts, function ($shifts) {
            foreach ($shifts as $s) {
                if ($s['total'] > 0 && $s['done'] === $s['total']) return true;
            }
            return false;
        }));

        return ['total' => $totalZones, 'cleaned' => $cleanedZones, 'pending' => $totalZones - $cleanedZones];
    }
}
