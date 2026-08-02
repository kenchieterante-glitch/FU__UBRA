<?php

namespace App\Controllers\Api;

use App\Libraries\ApiAuth;
use App\Models\ApiTokenModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class AuthController extends BaseApiController
{
    protected $userModel;
    protected $tokenModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->userModel  = new UserModel();
        $this->tokenModel = new ApiTokenModel();
    }

    // POST /api/login — no auth filter, this issues the token
    public function login()
    {
        $employeeId = trim((string) ($this->request->getPost('employee_id') ?? $this->request->getVar('employee_id') ?? ''));
        $password   = (string) ($this->request->getPost('password') ?? $this->request->getVar('password') ?? '');
        $deviceName = (string) ($this->request->getPost('device_name') ?? $this->request->getVar('device_name') ?? 'Mobile App');

        if ($employeeId === '' || $password === '') {
            return $this->fail('Employee ID and password are required.', 422);
        }

        $user = $this->userModel->getByEmployeeId($employeeId);
        if (!$user) {
            return $this->fail('Invalid employee ID or password.', 401);
        }

        $storedHash     = $user['password_hash'] ?? null;
        $legacyPassword = $user['password'] ?? null;
        $isValid        = false;

        if (!empty($storedHash)) {
            $isValid = password_verify($password, $storedHash);
        } elseif (!empty($legacyPassword)) {
            $isValid = password_verify($password, $legacyPassword);
        }

        if (!$isValid) {
            return $this->fail('Invalid employee ID or password.', 401);
        }

        $token = $this->tokenModel->issueToken((int) $user['department_id'], $deviceName);
        unset($user['password']);

        return $this->ok([
            'token' => $token,
            'user'  => $user,
        ]);
    }

    // POST /api/logout — requires bearer token
    public function logout()
    {
        $header = $this->request->getHeaderLine('Authorization');
        if (preg_match('/^Bearer\s+(.+)$/i', trim($header), $matches)) {
            $this->tokenModel->revokeByPlainToken(trim($matches[1]));
        }

        return $this->ok(['message' => 'Logged out.']);
    }

    // GET /api/me — requires bearer token
    public function me()
    {
        $user = ApiAuth::user();
        if (!$user) {
            return $this->fail('Unauthorized', 401);
        }

        return $this->ok(['user' => $user]);
    }
}
