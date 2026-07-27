<?php

namespace App\Controllers;

use App\Models\PersonnelModel;
use App\Models\DepartmentModel;
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

        $data = [
            'title'       => 'Personnel Management',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->getAllWithDetails(),
            'departments' => $this->departmentModel->findAll(),
        ];

        return view('personnel/index', $data);
    }

    public function drivers()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'title'       => 'Drivers',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->getDrivers(),
            'departments' => $this->departmentModel->findAll(),
        ];

        return view('personnel/index', $data);
    }

    public function janitors()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'title'       => 'Janitors',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->getByPositionKeyword('Cleaning') ?: [],
            'departments' => $this->departmentModel->findAll(),
        ];

        return view('personnel/index', $data);
    }

    public function carpentries()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'title'       => 'Carpentries',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->getByPositionKeyword('Carpenter') ?: [],
            'departments' => $this->departmentModel->findAll(),
        ];

        return view('personnel/index', $data);
    }

    public function maintenance()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'title'       => 'Maintenance',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->like('position', 'Maintenance')
                                                  ->orLike('position', 'Physical Plant')
                                                  ->findAll() ?: [],
            'departments' => $this->departmentModel->findAll(),
        ];

        return view('personnel/index', $data);
    }

    public function add()
    {
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
        $this->personnelModel->delete($id);
        return redirect()->to('/personnel')->with('success', 'Personnel removed.');
    }
}
