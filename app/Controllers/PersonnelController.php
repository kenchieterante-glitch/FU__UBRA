<?php

namespace App\Controllers;

use App\Models\PersonnelModel;
use App\Models\DepartmentModel;
use App\Models\JobOrderModel;
use App\Libraries\JobOrderAssignmentService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class PersonnelController extends BaseController
{
    protected $personnelModel;
    protected $departmentModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->personnelModel = new PersonnelModel();
        $this->departmentModel = new DepartmentModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = array_merge([
            'title'       => 'Personnel Management',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->getAllWithDetails(),
            'departments' => $this->departmentModel->findAll(),
        ], $this->getStatCounts());

        return view('personnel/index', $data);
    }

    public function drivers()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = array_merge([
            'title'       => 'Drivers',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->getDrivers(),
            'departments' => $this->departmentModel->findAll(),
        ], $this->getStatCounts());

        return view('personnel/index', $data);
    }

    public function janitors()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = array_merge([
            'title'       => 'Janitors',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->groupStart()
                                                  ->like('position', 'Janitor')
                                                  ->orLike('position', 'Cleaning')
                                                  ->groupEnd()
                                                  ->where('is_archived', 0)
                                                  ->findAll() ?: [],
            'departments' => $this->departmentModel->findAll(),
        ], $this->getStatCounts());

        return view('personnel/index', $data);
    }

    public function carpentries()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = array_merge([
            'title'       => 'Carpentries Shop',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->getByPositionKeyword('Carpenter') ?: [],
            'departments' => $this->departmentModel->findAll(),
        ], $this->getStatCounts());

        return view('personnel/index', $data);
    }

    public function constructionWorkers()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = array_merge([
            'title'       => 'Construction Workers',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->getByPositionKeyword('Construction') ?: [],
            'departments' => $this->departmentModel->findAll(),
        ], $this->getStatCounts());

        return view('personnel/index', $data);
    }

    public function maintenance()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = array_merge([
            'title'       => 'Maintenance',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->where('is_archived', 0)
                                                  ->groupStart()
                                                      ->like('position', 'Maintenance')
                                                      ->orLike('position', 'Physical Plant')
                                                  ->groupEnd()
                                                  ->findAll() ?: [],
            'departments' => $this->departmentModel->findAll(),
        ], $this->getStatCounts());

        return view('personnel/index', $data);
    }

    // Mirrors drivers()/janitors()/etc — a filtered tab, but keyed on
    // employment_type instead of a position keyword.
    public function jobOrder()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = array_merge([
            'title'       => 'Job Order Personnel',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->getJobOrderPersonnel(),
            'departments' => $this->departmentModel->findAll(),
        ], $this->getStatCounts());

        return view('personnel/index', $data);
    }

    // Lets a Job Order be assigned starting from a Personnel row, instead of
    // only reachable from inside a specific Job Order's page — same
    // underlying assignment as JobOrderController::assignPersonnel(), just
    // entered from the other side.
    public function assignJobOrder($id)
    {
        if ($resp = $this->requireJobOrderManager()) return $resp;

        $jobOrderId = (int) $this->request->getPost('job_order_id');
        $result = (new JobOrderAssignmentService())->assign((int) $id, $jobOrderId, $this->request->getPost());

        if (!$result['ok']) {
            return redirect()->back()->with('error', $result['error']);
        }

        $person = $this->personnelModel->find($id);
        $this->logActivity('Personnel Monitoring', "Assigned {$person['full_name']} to Job Order {$result['jobOrder']['job_order_number']}");

        return redirect()->to('/personnel')->with('success', 'Assigned to Job Order successfully.');
    }

    public function view($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $person = $this->personnelModel->getWithDetails((int) $id);
        if (!$person) {
            return redirect()->to('/personnel')->with('error', 'Personnel record not found.');
        }

        $assignmentModel = new \App\Models\PersonnelAssignmentModel();
        $contractModel   = new \App\Models\PersonnelContractModel();
        $documentModel   = new \App\Models\PersonnelDocumentModel();
        $docTypeModel    = new \App\Models\DocumentRequirementTypeModel();

        $requiredDocTypes = $docTypeModel->getRequired();
        $documents        = $documentModel->getForPersonnel((int) $id);
        $completeness     = $documentModel->completenessForPersonnel((int) $id, $requiredDocTypes);

        // The assignment and its contract are always created together
        // (JobOrderAssignmentService) with matching job_order_id + start
        // date — merge them here so the profile shows one "Job Order
        // Assignment" section instead of a separate Assignment card and
        // Contract table repeating the same Job Order/period info.
        $contracts = $contractModel->getForPersonnel((int) $id);
        $findContract = function (?array $assignment) use ($contracts) {
            if (!$assignment) return null;
            foreach ($contracts as $c) {
                if ((int) $c['job_order_id'] === (int) $assignment['job_order_id']
                    && $c['contract_start_date'] === $assignment['assignment_start_date']) {
                    return $c;
                }
            }
            return null;
        };

        $activeAssignment = $assignmentModel->getActiveForPersonnel((int) $id);
        // Past assignments only — the current one already has its own card
        // above, so it isn't repeated here too.
        $assignmentHistory = array_map(function ($h) use ($findContract) {
            $h['contract'] = $findContract($h);
            return $h;
        }, array_values(array_filter(
            $assignmentModel->getHistoryForPersonnel((int) $id),
            fn($h) => $h['status'] !== 'ACTIVE'
        )));

        return view('personnel/view', [
            'title'             => $person['full_name'],
            'pageCss'           => 'personnel.css',
            'person'            => $person,
            'activeAssignment'  => $activeAssignment,
            'activeContract'    => $findContract($activeAssignment),
            'assignmentHistory' => $assignmentHistory,
            'documents'         => $documents,
            'documentTypes'     => $docTypeModel->getAllOrdered(),
            'completeness'      => $completeness,
        ]);
    }

    private function getStatCounts(): array
    {
        return [
            'total_personnel_count' => $this->personnelModel->where('is_archived', 0)->countAllResults(),
            'drivers_count'         => $this->personnelModel->where('is_archived', 0)->like('position', 'Driver')->countAllResults(),
            'janitors_count'        => $this->personnelModel->where('is_archived', 0)
                                                              ->groupStart()
                                                                  ->like('position', 'Janitor')
                                                                  ->orLike('position', 'Cleaning')
                                                              ->groupEnd()
                                                              ->countAllResults(),
            'carpentries_count'     => $this->personnelModel->where('is_archived', 0)->like('position', 'Carpenter')->countAllResults(),
            'maintenance_count'     => $this->personnelModel->where('is_archived', 0)
                                                              ->groupStart()
                                                                  ->like('position', 'Maintenance')
                                                                  ->orLike('position', 'Physical Plant')
                                                              ->groupEnd()
                                                              ->countAllResults(),
            'construction_count'    => $this->personnelModel->where('is_archived', 0)->like('position', 'Construction')->countAllResults(),
            'job_order_count'       => $this->personnelModel->where('is_archived', 0)->where('employment_type', 'JobOrder')->countAllResults(),
            'active_count'          => $this->personnelModel->where('is_archived', 0)->where('status', 'Active')->countAllResults(),
            'on_leave_count'        => $this->personnelModel->where('is_archived', 0)->where('status', 'On Leave')->countAllResults(),
            'jobOrders'             => (new JobOrderModel())->getForDropdown(),
        ];
    }

    public function add()
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $empId = trim((string) $this->request->getPost('emp_id'));

        if ($empId !== '' && $this->personnelModel->isEmpIdTaken($empId)) {
            return redirect()->back()->withInput()->with('error', 'Employee ID already exists.');
        }

        try {
            $this->personnelModel->insert([
                'emp_id'        => $empId,
                'full_name'     => $this->request->getPost('full_name'),
                'email'         => $this->request->getPost('email'),
                'department_id' => $this->request->getPost('department_id'),
                'position'      => $this->request->getPost('position'),
                'assigned_task' => $this->request->getPost('assigned_task'),
                'status'        => $this->request->getPost('status') ?? 'Active',
            ]);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            return redirect()->back()->withInput()->with('error', 'Could not save personnel because the employee ID is already in use.');
        }

        return redirect()->to('/personnel')->with('success', 'Personnel added successfully.');
    }

    public function edit($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $empId = trim((string) $this->request->getPost('emp_id'));

        if ($empId !== '' && $this->personnelModel->isEmpIdTaken($empId, $id)) {
            return redirect()->back()->withInput()->with('error', 'Employee ID already exists.');
        }

        try {
            $this->personnelModel->update($id, [
                'emp_id'        => $empId,
                'full_name'     => $this->request->getPost('full_name'),
                'email'         => $this->request->getPost('email'),
                'department_id' => $this->request->getPost('department_id'),
                'position'      => $this->request->getPost('position'),
                'assigned_task' => $this->request->getPost('assigned_task'),
                'status'        => $this->request->getPost('status'),
            ]);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            return redirect()->back()->withInput()->with('error', 'Could not save personnel because the employee ID is already in use.');
        }

        return redirect()->to('/personnel')->with('success', 'Personnel updated successfully.');
    }

    public function delete($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $this->personnelModel->update($id, [
            'is_archived' => 1,
            'archived_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/personnel')->with('success', 'Personnel archived.');
    }
}
