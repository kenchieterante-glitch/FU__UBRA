<?php

namespace App\Controllers;

use App\Models\JobOrderModel;
use App\Models\PersonnelAssignmentModel;
use App\Models\PersonnelContractModel;
use App\Models\PersonnelDocumentModel;
use App\Models\DocumentRequirementTypeModel;
use App\Models\PersonnelModel;
use App\Models\NotificationModel;

class PersonnelMonitoringController extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $jobOrderModel   = new JobOrderModel();
        $assignmentModel = new PersonnelAssignmentModel();
        $contractModel   = new PersonnelContractModel();
        $documentModel   = new PersonnelDocumentModel();
        $docTypeModel    = new DocumentRequirementTypeModel();
        $personnelModel  = new PersonnelModel();

        $jobOrders  = $jobOrderModel->getAllWithDetails();
        $contracts  = $contractModel->getAllWithDetails();
        $requiredDocTypes = $docTypeModel->getRequired();
        $jobOrderPersonnel = $personnelModel->getJobOrderPersonnel();

        $this->generateAlerts($jobOrders, $contracts, $jobOrderPersonnel, $documentModel, $requiredDocTypes);

        // Personnel monitoring counts (job-order personnel only)
        $activeAssignments = 0;
        $inactiveAssignments = 0;
        foreach ($jobOrderPersonnel as $p) {
            if ($assignmentModel->hasActiveAssignment((int) $p['id'])) {
                $activeAssignments++;
            } else {
                $inactiveAssignments++;
            }
        }

        $contractCounts = ['ACTIVE' => 0, 'EXPIRING_SOON' => 0, 'EXPIRED' => 0];
        foreach ($contracts as $c) {
            if (isset($contractCounts[$c['contract_status']])) {
                $contractCounts[$c['contract_status']]++;
            }
        }

        $jobOrderCounts = ['ACTIVE' => 0, 'EXPIRING_SOON' => 0, 'EXPIRED' => 0];
        foreach ($jobOrders as $jo) {
            if (isset($jobOrderCounts[$jo['status']])) {
                $jobOrderCounts[$jo['status']]++;
            }
        }

        // Most recent contract per personnel (contracts already come back
        // newest-first from getAllWithDetails), used to attach a contract
        // status/days-remaining to each personnel row below.
        $contractByPersonnel = [];
        foreach ($contracts as $c) {
            if (!isset($contractByPersonnel[$c['personnel_id']])) {
                $contractByPersonnel[$c['personnel_id']] = $c;
            }
        }

        $completeDocs = 0;
        $incompleteDocs = 0;
        $expiredDocsCount = 0;
        // One row per Job Order personnel carrying every facet a stat card
        // might filter by (assignment status, contract status, document
        // completeness) — this single table is what every clickable card on
        // the dashboard actually filters into, so "click a number, see the
        // records behind it" works for the whole Personnel + Document side.
        $personnelDetail = [];
        foreach ($jobOrderPersonnel as $p) {
            $activeAssignment = $assignmentModel->getActiveForPersonnel((int) $p['id']);
            $contract = $contractByPersonnel[$p['id']] ?? null;
            $completeness = $documentModel->completenessForPersonnel((int) $p['id'], $requiredDocTypes);
            $completeness['complete'] ? $completeDocs++ : $incompleteDocs++;

            $hasExpiredDoc = false;
            foreach ($documentModel->getForPersonnel((int) $p['id']) as $d) {
                if ($d['verification_status'] === 'EXPIRED') {
                    $hasExpiredDoc = true;
                    $expiredDocsCount++;
                }
            }

            $personnelDetail[] = [
                'id'                    => $p['id'],
                'full_name'             => $p['full_name'],
                'emp_id'                => $p['emp_id'],
                'job_order_number'      => $activeAssignment['job_order_number'] ?? null,
                'job_order_title'       => $activeAssignment['job_order_title'] ?? null,
                'assignment_status'     => $activeAssignment['status'] ?? 'INACTIVE',
                'contract_status'       => $contract['contract_status'] ?? null,
                'contract_days_remaining' => $contract['days_remaining'] ?? null,
                'doc_verified'          => $completeness['verified'],
                'doc_required'          => $completeness['required'],
                'doc_complete'          => $completeness['complete'],
                'has_expired_doc'       => $hasExpiredDoc,
            ];
        }

        return view('personnel/monitoring', [
            'title'   => 'Job Order Personnel Monitoring',
            'pageCss' => 'personnel.css',
            'full_name'    => session()->get('full_name'),
            'last_updated' => date('l, F j, Y — g:i A'),

            'total_jo_personnel'   => count($jobOrderPersonnel),
            'active_assignments'   => $activeAssignments,
            'inactive_assignments' => $inactiveAssignments,
            'contract_counts'      => $contractCounts,
            'job_order_counts'     => $jobOrderCounts,
            'total_job_orders'     => count($jobOrders),
            'complete_docs'        => $completeDocs,
            'incomplete_docs'      => $incompleteDocs,
            'expired_docs'         => $expiredDocsCount,

            'personnelDetail' => $personnelDetail,
            'jobOrders'       => $jobOrders,
        ]);
    }

    private function generateAlerts(array $jobOrders, array $contracts, array $jobOrderPersonnel, PersonnelDocumentModel $documentModel, array $requiredDocTypes): void
    {
        $notif = new NotificationModel();

        foreach ($jobOrders as $jo) {
            if ($jo['status'] === 'EXPIRING_SOON') {
                $this->notifyOnce($notif, 'Job Order Expiring Soon', $jo['job_order_number'],
                    "Job Order {$jo['job_order_number']} ({$jo['job_order_title']}) will expire in {$jo['days_remaining']} day(s).",
                    'Head of Facilities', 'MODERATE');
            } elseif ($jo['status'] === 'EXPIRED') {
                $this->notifyOnce($notif, 'Job Order Expired', $jo['job_order_number'],
                    "Job Order {$jo['job_order_number']} ({$jo['job_order_title']}) has expired.",
                    'Head of Facilities', 'CRITICAL');
            }
        }

        foreach ($contracts as $c) {
            $who = $c['full_name'] ?? 'Personnel #' . $c['personnel_id'];
            if ($c['contract_status'] === 'EXPIRING_SOON') {
                $this->notifyOnce($notif, 'Contract Expiring Soon', (string) $c['id'],
                    "{$who}'s Job Order contract (ID {$c['id']}) will expire in {$c['days_remaining']} day(s).",
                    'Head of Facilities', 'MODERATE');
            } elseif ($c['contract_status'] === 'EXPIRED') {
                $this->notifyOnce($notif, 'Contract Expired', (string) $c['id'],
                    "{$who}'s Job Order contract (ID {$c['id']}) has expired.",
                    'Head of Facilities', 'CRITICAL');
            }
        }

        foreach ($jobOrderPersonnel as $p) {
            $completeness = $documentModel->completenessForPersonnel((int) $p['id'], $requiredDocTypes);
            if (!$completeness['complete']) {
                $this->notifyOnce($notif, 'Personnel Document Incomplete', $p['full_name'],
                    "{$p['full_name']} has incomplete requirements ({$completeness['verified']}/{$completeness['required']} documents verified).",
                    'Head of Facilities', 'MODERATE');
            }
        }
    }

    // Same dedup convention as SafetyController::notifyStatusAlerts — one
    // unread notification per entity while its alert condition holds,
    // matched by category + the entity's unique identifier appearing in the
    // description, so reloading the dashboard doesn't spam duplicates.
    private function notifyOnce(NotificationModel $notif, string $category, string $identifier, string $description, string $recipient, string $priority): void
    {
        $already = $notif->where('category', $category)
            ->where('is_read', 0)
            ->like('description', $identifier)
            ->countAllResults() > 0;
        if ($already) return;

        $notif->insert([
            'category'    => $category,
            'description' => $description,
            'recipient'   => $recipient,
            'priority'    => $priority,
            'status'      => 'Pending',
            'is_read'     => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}
