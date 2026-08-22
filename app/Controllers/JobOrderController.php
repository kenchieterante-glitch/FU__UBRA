<?php

namespace App\Controllers;

use App\Models\JobOrderModel;
use App\Models\PersonnelModel;
use App\Models\PersonnelAssignmentModel;
use App\Models\PersonnelContractModel;
use App\Models\DepartmentModel;

class JobOrderController extends BaseController
{
    protected $jobOrderModel;
    protected $assignmentModel;
    protected $contractModel;
    protected $personnelModel;
    protected $session;

    public function __construct()
    {
        $this->jobOrderModel  = new JobOrderModel();
        $this->assignmentModel = new PersonnelAssignmentModel();
        $this->contractModel  = new PersonnelContractModel();
        $this->personnelModel = new PersonnelModel();
        $this->session        = \Config\Services::session();
    }

    public function index()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        return view('personnel/job_orders', [
            'title'     => 'Job Orders',
            'pageCss'   => 'personnel.css',
            'jobOrders' => $this->jobOrderModel->getAllWithDetails(),
        ]);
    }

    public function view($id)
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $jobOrder = $this->jobOrderModel->getWithDetails((int) $id);
        if (!$jobOrder) {
            return redirect()->to('/personnel/job-orders')->with('error', 'Job Order not found.');
        }

        $assignedIds = array_column($this->assignmentModel->getForJobOrder((int) $id), 'personnel_id');
        $availablePersonnel = array_values(array_filter(
            $this->personnelModel->where('is_archived', 0)->findAll(),
            fn($p) => !in_array((int) $p['id'], array_map('intval', $assignedIds), true)
        ));

        return view('personnel/job_order_view', [
            'title'       => $jobOrder['job_order_number'],
            'pageCss'     => 'personnel.css',
            'jobOrder'    => $jobOrder,
            'assignments' => $this->assignmentModel->getForJobOrder((int) $id),
            'availablePersonnel' => $availablePersonnel,
        ]);
    }

    public function add()
    {
        if ($resp = $this->requireJobOrderManager()) return $resp;

        $number = trim((string) $this->request->getPost('job_order_number')) ?: $this->jobOrderModel->nextJobOrderNumber();
        if ($this->jobOrderModel->isNumberTaken($number)) {
            return redirect()->back()->withInput()->with('error', 'Job Order number already exists.');
        }

        $startDate = $this->request->getPost('start_date');
        $endDate   = $this->request->getPost('end_date');
        if ($startDate && $endDate && $endDate < $startDate) {
            return redirect()->back()->withInput()->with('error', 'End date cannot be before start date.');
        }

        $this->jobOrderModel->insert([
            'job_order_number'    => $number,
            'job_order_title'     => $this->request->getPost('job_order_title'),
            'project_name'        => $this->request->getPost('project_name'),
            'position'            => $this->request->getPost('position'),
            'assignment_location' => $this->request->getPost('assignment_location'),
            'personnel_required'  => (int) $this->request->getPost('personnel_required') ?: 1,
            'supervisor'          => $this->request->getPost('supervisor'),
            'start_date'          => $startDate ?: null,
            'end_date'            => $endDate ?: null,
            'status'              => $this->request->getPost('status') ?? 'ACTIVE',
            'description'         => $this->request->getPost('description'),
            'remarks'             => $this->request->getPost('remarks'),
        ]);

        $this->logActivity('Personnel Monitoring', "Created Job Order {$number}");

        return redirect()->to('/personnel/job-orders')->with('success', 'Job Order created successfully.');
    }

    public function edit($id)
    {
        if ($resp = $this->requireJobOrderManager()) return $resp;

        $startDate = $this->request->getPost('start_date');
        $endDate   = $this->request->getPost('end_date');
        if ($startDate && $endDate && $endDate < $startDate) {
            return redirect()->back()->withInput()->with('error', 'End date cannot be before start date.');
        }

        $before = $this->jobOrderModel->find($id);

        $this->jobOrderModel->update($id, [
            'job_order_title'     => $this->request->getPost('job_order_title'),
            'project_name'        => $this->request->getPost('project_name'),
            'position'            => $this->request->getPost('position'),
            'assignment_location' => $this->request->getPost('assignment_location'),
            'personnel_required'  => (int) $this->request->getPost('personnel_required') ?: 1,
            'supervisor'          => $this->request->getPost('supervisor'),
            'start_date'          => $startDate ?: null,
            'end_date'            => $endDate ?: null,
            'status'              => $this->request->getPost('status'),
            'description'         => $this->request->getPost('description'),
            'remarks'             => $this->request->getPost('remarks'),
        ]);

        if ($before && $before['end_date'] !== $endDate) {
            $this->logActivity('Personnel Monitoring', "Updated Job Order #{$id} end date: " . ($before['end_date'] ?? '—') . ' -> ' . ($endDate ?: '—'));
        } else {
            $this->logActivity('Personnel Monitoring', "Updated Job Order #{$id}");
        }

        return redirect()->to('/personnel/job-orders')->with('success', 'Job Order updated successfully.');
    }

    public function archive($id)
    {
        if ($resp = $this->requireJobOrderManager()) return $resp;

        $this->jobOrderModel->update($id, ['is_archived' => 1]);
        $this->logActivity('Personnel Monitoring', "Archived Job Order #{$id}");

        return redirect()->to('/personnel/job-orders')->with('success', 'Job Order archived.');
    }

    public function assignPersonnel($id)
    {
        if ($resp = $this->requireJobOrderManager()) return $resp;

        $personnelId = (int) $this->request->getPost('personnel_id');
        $result = (new \App\Libraries\JobOrderAssignmentService())->assign($personnelId, (int) $id, $this->request->getPost());

        if (!$result['ok']) {
            return redirect()->back()->with('error', $result['error']);
        }

        $this->logActivity('Personnel Monitoring', "Assigned personnel #{$personnelId} to Job Order {$result['jobOrder']['job_order_number']}");

        return redirect()->to('/personnel/job-orders/view/' . $id)->with('success', 'Personnel assigned successfully.');
    }

    public function endAssignment($assignmentId)
    {
        if ($resp = $this->requireJobOrderManager()) return $resp;

        $assignment = $this->assignmentModel->find($assignmentId);
        if (!$assignment) {
            return redirect()->back()->with('error', 'Assignment not found.');
        }

        $newStatus = in_array($this->request->getPost('status'), ['ENDED', 'TERMINATED', 'TRANSFERRED'], true)
            ? $this->request->getPost('status') : 'ENDED';

        $this->assignmentModel->update($assignmentId, [
            'status'              => $newStatus,
            'assignment_end_date' => date('Y-m-d'),
        ]);

        $this->contractModel->where('personnel_id', $assignment['personnel_id'])
            ->where('job_order_id', $assignment['job_order_id'])
            ->whereIn('contract_status', ['ACTIVE', 'EXPIRING_SOON'])
            ->set(['contract_status' => 'TERMINATED'])
            ->update();

        if (!$this->assignmentModel->hasActiveAssignment((int) $assignment['personnel_id'])) {
            $this->personnelModel->update($assignment['personnel_id'], [
                'employment_type' => 'Regular',
            ]);
        }

        $this->logActivity('Personnel Monitoring', "Ended assignment #{$assignmentId} ({$newStatus})");

        return redirect()->to('/personnel/job-orders/view/' . $assignment['job_order_id'])->with('success', 'Assignment updated.');
    }
}
