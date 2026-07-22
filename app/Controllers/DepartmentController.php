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
            return redirect()->to('/auth/login');
        }

        $data = [
            'title'       => 'Departments',
            'departments' => $this->departmentModel->findAll(),
        ];

        return view('departments/index', $data);
    }

    public function add()
    {
        $this->departmentModel->insert([
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ]);

        return redirect()->to('/departments')->with('success', 'Department added successfully.');
    }

    public function edit($id)
    {
        $this->departmentModel->update($id, [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ]);

        return redirect()->to('/departments')->with('success', 'Department updated successfully.');
    }

    public function delete($id)
    {
        $this->departmentModel->delete($id);
        return redirect()->to('/departments')->with('success', 'Department removed.');
    }
}
