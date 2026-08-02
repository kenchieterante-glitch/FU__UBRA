<?php

namespace App\Controllers\Api;

use App\Models\DepartmentModel;
use App\Models\PersonnelModel;

class PersonnelController extends BaseApiController
{
    protected PersonnelModel $personnelModel;
    protected DepartmentModel $departmentModel;

    public function __construct()
    {
        $this->personnelModel  = new PersonnelModel();
        $this->departmentModel = new DepartmentModel();
    }

    public function index()
    {
        return $this->ok([
            'personnel'   => $this->personnelModel->getAllWithDetails(),
            'departments' => $this->departmentModel->findAll(),
            'stats'       => $this->getStatCounts(),
        ]);
    }

    // GET /api/personnel/lookup/(:any) — used by ID-scan flows (tools borrow, guard
    // key desk, vehicle trip tickets) to resolve a scanned emp_id to a person.
    public function lookup($empId = null)
    {
        $person = $this->personnelModel->getByEmpId($empId);
        if (!$person) {
            return $this->fail('No personnel record found for that ID.', 404);
        }

        $dept = $person['department_id'] ? $this->departmentModel->find($person['department_id']) : null;
        $person['department_name'] = $dept['name'] ?? null;

        return $this->ok(['personnel' => $person]);
    }

    public function drivers()
    {
        return $this->ok(['personnel' => $this->personnelModel->getDrivers()]);
    }

    public function janitors()
    {
        $rows = $this->personnelModel
            ->groupStart()->like('position', 'Janitor')->orLike('position', 'Cleaning')->groupEnd()
            ->findAll();
        return $this->ok(['personnel' => $rows]);
    }

    public function carpentries()
    {
        return $this->ok(['personnel' => $this->personnelModel->getByPositionKeyword('Carpenter')]);
    }

    public function constructionWorkers()
    {
        return $this->ok(['personnel' => $this->personnelModel->getByPositionKeyword('Construction')]);
    }

    public function maintenance()
    {
        $rows = $this->personnelModel->like('position', 'Maintenance')->orLike('position', 'Physical Plant')->findAll();
        return $this->ok(['personnel' => $rows]);
    }

    public function add()
    {
        if ($resp = $this->requireAdminOrFail()) return $resp;

        $empId = trim((string) $this->request->getPost('emp_id'));
        if ($empId === '' || $this->personnelModel->isEmpIdTaken($empId)) {
            return $this->fail('Employee ID is required and must be unique.', 422);
        }

        $id = $this->personnelModel->insert([
            'emp_id'        => $empId,
            'full_name'     => $this->request->getPost('full_name'),
            'email'         => $this->request->getPost('email'),
            'department_id' => $this->request->getPost('department_id') ?: null,
            'position'      => $this->request->getPost('position'),
            'assigned_task' => $this->request->getPost('assigned_task'),
            'status'        => $this->request->getPost('status') ?: 'Active',
        ], true);

        return $this->ok(['id' => $id], 201);
    }

    public function edit($id = null)
    {
        if ($resp = $this->requireAdminOrFail()) return $resp;

        $empId = trim((string) $this->request->getPost('emp_id'));
        if ($empId === '' || $this->personnelModel->isEmpIdTaken($empId, (int) $id)) {
            return $this->fail('Employee ID is required and must be unique.', 422);
        }

        $this->personnelModel->update($id, [
            'emp_id'        => $empId,
            'full_name'     => $this->request->getPost('full_name'),
            'email'         => $this->request->getPost('email'),
            'department_id' => $this->request->getPost('department_id') ?: null,
            'position'      => $this->request->getPost('position'),
            'assigned_task' => $this->request->getPost('assigned_task'),
            'status'        => $this->request->getPost('status'),
        ]);

        return $this->ok();
    }

    public function delete($id = null)
    {
        if ($resp = $this->requireAdminOrFail()) return $resp;

        try {
            $this->personnelModel->delete($id);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            return $this->fail('Cannot delete: this person is referenced by other records.', 409);
        }

        return $this->ok();
    }

    private function getStatCounts(): array
    {
        $m = $this->personnelModel;
        return [
            'total_personnel_count' => (clone $m)->countAllResults(false),
            'drivers_count'         => (clone $m)->like('position', 'Driver')->countAllResults(false),
            'janitors_count'        => (clone $m)->groupStart()->like('position', 'Janitor')->orLike('position', 'Cleaning')->groupEnd()->countAllResults(false),
            'carpentries_count'     => (clone $m)->like('position', 'Carpenter')->countAllResults(false),
            'maintenance_count'     => (clone $m)->groupStart()->like('position', 'Maintenance')->orLike('position', 'Physical Plant')->groupEnd()->countAllResults(false),
            'construction_count'    => (clone $m)->like('position', 'Construction')->countAllResults(false),
            'active_count'          => (clone $m)->where('status', 'Active')->countAllResults(false),
            'on_leave_count'        => (clone $m)->where('status', 'On Leave')->countAllResults(false),
        ];
    }
}
