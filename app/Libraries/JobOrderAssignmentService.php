<?php

namespace App\Libraries;

use App\Models\JobOrderModel;
use App\Models\PersonnelAssignmentModel;
use App\Models\PersonnelContractModel;
use App\Models\PersonnelModel;

/**
 * Creates a Job Order assignment + its matching contract in one step, and
 * flips the personnel record's employment_type. Shared by both entry points
 * that can start this flow — the Job Order page ("Assign Personnel", picks
 * a person) and the Personnel page ("Assign to Job Order", picks a Job
 * Order) — so the validation rules (no double-active-assignment, no
 * assigning to an expired Job Order without override, date sanity) live in
 * exactly one place.
 */
class JobOrderAssignmentService
{
    public function assign(int $personnelId, int $jobOrderId, array $input): array
    {
        $jobOrderModel   = new JobOrderModel();
        $assignmentModel = new PersonnelAssignmentModel();
        $contractModel   = new PersonnelContractModel();
        $personnelModel  = new PersonnelModel();

        $jobOrder = $jobOrderModel->getWithDetails($jobOrderId);
        if (!$jobOrder) {
            return ['ok' => false, 'error' => 'Job Order not found.'];
        }

        if ($jobOrder['status'] === 'EXPIRED' && empty($input['override_expired'])) {
            return ['ok' => false, 'error' => 'This Job Order has expired. Check "Assign anyway" to override.'];
        }

        if (!$personnelId) {
            return ['ok' => false, 'error' => 'Select a personnel record to assign.'];
        }
        if ($assignmentModel->hasActiveAssignment($personnelId)) {
            return ['ok' => false, 'error' => 'This personnel already has an active Job Order assignment. End it first.'];
        }

        $startDate = ($input['assignment_start_date'] ?? '') ?: date('Y-m-d');
        $endDate   = ($input['assignment_end_date'] ?? '') ?: $jobOrder['end_date'];
        if ($endDate && $endDate < $startDate) {
            return ['ok' => false, 'error' => 'Assignment end date cannot be before start date.'];
        }

        $assignmentModel->insert([
            'personnel_id'          => $personnelId,
            'job_order_id'          => $jobOrderId,
            'position'              => ($input['position'] ?? '') ?: $jobOrder['position'],
            'assignment_location'   => ($input['assignment_location'] ?? '') ?: $jobOrder['assignment_location'],
            'supervisor'            => ($input['supervisor'] ?? '') ?: $jobOrder['supervisor'],
            'assignment_start_date' => $startDate,
            'assignment_end_date'   => $endDate ?: null,
            'status'                => 'ACTIVE',
        ]);

        $contractModel->insert([
            'personnel_id'        => $personnelId,
            'job_order_id'        => $jobOrderId,
            'contract_number'     => trim((string) ($input['contract_number'] ?? '')) ?: null,
            'contract_start_date' => $startDate,
            'contract_end_date'   => $endDate ?: null,
            'contract_status'     => 'ACTIVE',
        ]);

        $personnelModel->update($personnelId, ['employment_type' => 'JobOrder']);

        return ['ok' => true, 'jobOrder' => $jobOrder];
    }
}
