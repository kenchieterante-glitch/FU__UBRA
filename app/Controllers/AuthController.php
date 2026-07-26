<?php
namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class AuthController extends BaseController
{
    protected $userModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->userModel = new UserModel();
    }

    public function attemptLogin()
    {
        $employeeId = trim((string) ($this->request->getPost('employee_id') ?? $this->request->getPost('emp_id') ?? $this->request->getPost('username') ?? ''));
        $password = $this->request->getPost('password') ?? '';

        if ($employeeId === '' || $password === '') {
            return redirect()->back()->with('error', 'Employee ID and password are required.');
        }

        $user = $this->userModel->getByEmployeeId($employeeId);

        if (!$user) {
            return redirect()->back()->with('error', 'Invalid employee ID or password.');
        }

        $storedHash = $user['password_hash'] ?? null;
        $legacyPassword = $user['password'] ?? null;
        $isValid = false;

        if (!empty($storedHash)) {
            $isValid = password_verify($password, $storedHash);
        } elseif (!empty($legacyPassword)) {
            $isValid = password_verify($password, $legacyPassword);
        }

        if ($isValid) {
            $userId = $user['id'] ?? $user['user_id'] ?? $user['userid'] ?? null;
            $fullName = $user['full_name'] ?? $user['name'] ?? $user['emp_id'] ?? 'System Admin';
            $role = $user['role'] ?? 'Operations';
            $departmentId = $user['department_id'] ?? null;

            session()->set([
                'user_id'       => $userId,
                'emp_id'        => $employeeId,
                'full_name'     => $fullName,
                'role'          => $role,
                'department_id' => $departmentId,
                'isLoggedIn'    => true,
            ]);

            return redirect()->to('/dashboard');
        }

        return redirect()->back()->with('error', 'Invalid employee ID or password.');
    }

    public function login()
    {
        return view('auth/login');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
