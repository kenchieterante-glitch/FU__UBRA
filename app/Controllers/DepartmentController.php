<?php
namespace App\Controllers;

use App\Models\DepartmentModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class DepartmentController extends BaseController
{
    protected $departmentModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->departmentModel = new DepartmentModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'title'       => 'Departments',
            'departments' => $this->departmentModel->findAll(),
        ];

        return view('departments/index', $data);
    }

    public function add()
    {
        $name = $this->request->getPost('name');
        $this->departmentModel->insert([
            'name'        => $name,
            'description' => $this->request->getPost('description'),
        ]);
        $this->logActivity('Personnel', "Added department {$name}");

        return redirect()->to('/departments')->with('success', 'Department added successfully.');
    }

    public function edit($id)
    {
        $name = $this->request->getPost('name');
        $this->departmentModel->update($id, [
            'name'        => $name,
            'description' => $this->request->getPost('description'),
        ]);
        $this->logActivity('Personnel', "Updated department {$name} (#{$id})");

        return redirect()->to('/departments')->with('success', 'Department updated successfully.');
    }

    public function delete($id)
    {
        $dept = $this->departmentModel->find($id);
        $this->departmentModel->delete($id);
        $this->logActivity('Personnel', 'Removed department ' . ($dept['name'] ?? "#{$id}"));
        return redirect()->to('/departments')->with('success', 'Department removed.');
    }
}
