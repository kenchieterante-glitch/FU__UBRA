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

    public function login()
    {
        if ($this->request->getMethod() === 'post') {
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');

            $user = $this->userModel->getByUsername($username);

            if ($user && password_verify($password, $user['password_hash'])) {
                session()->set([
                    'user_id'      => $user['id'],
                    'full_name'    => $user['full_name'],
                    'role'         => $user['role'],
                    'department_id'=> $user['department_id'],
                    'isLoggedIn'   => true,
                ]);
                return redirect()->to('/dashboard');
            } else {
                return redirect()->back()->with('error', 'Invalid username or password.');
            }
        }

        return view('auth/login');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login');
    }
}
