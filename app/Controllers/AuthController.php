<?php
namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class AuthController extends BaseController
{
    protected $userModel;

    // Which account roles may sign in through each department portal. Administrators may use any portal.
    private const PORTAL_ROLES = [
        'facilities' => ['facilities', 'janitorial'],
        'safety'     => ['security'],
        'asset'      => ['assets', 'tools'],
        'iysp'       => ['sports'],
    ];

    private const PORTAL_NAMES = [
        'facilities' => 'Facilities Administration and General Services',
        'safety'     => 'Safety and Security Department',
        'asset'      => 'Asset Acquisition and Monitoring Department',
        'iysp'       => 'Sports Equipment Monitoring',
    ];

    private function loginUrl(string $portal): string
    {
        return isset(self::PORTAL_NAMES[$portal]) ? base_url('login?portal=' . $portal) : base_url('login');
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->userModel = new UserModel();
    }

    public function attemptLogin()
    {
        if ($this->isAuthenticated()) {
            $this->setNoStoreHeaders();

            return redirect()->to($this->roleLandingUrl());
        }

        $portal = (string) $this->request->getPost('portal');
        $employeeId = trim((string) ($this->request->getPost('employee_id') ?? $this->request->getPost('emp_id') ?? $this->request->getPost('username') ?? ''));
        $password = $this->request->getPost('password') ?? '';

        if ($employeeId === '' || $password === '') {
            return redirect()->to($this->loginUrl($portal))->with('error', 'Employee ID and password are required.');
        }

        $user = $this->userModel->getByEmployeeId($employeeId);

        if (!$user) {
            return redirect()->to($this->loginUrl($portal))->with('error', 'Invalid employee ID or password.');
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
            // The users table's real primary key is department_id (no 'id' column exists),
            // so that's the identifier that actually resolves for session['user_id'].
            $userId = $user['id'] ?? $user['user_id'] ?? $user['userid'] ?? $user['department_id'] ?? null;
            $fullName = $user['full_name'] ?? $user['name'] ?? $user['emp_id'] ?? 'System Admin';
            $role = $user['role'] ?? 'Operations';

            // Signing in through a department portal only works for that department's own accounts.
            if (isset(self::PORTAL_ROLES[$portal]) && strtolower($role) !== 'administrator' && !in_array(strtolower($role), self::PORTAL_ROLES[$portal], true)) {
                return redirect()->to($this->loginUrl($portal))->with('error', 'This account does not belong to the ' . self::PORTAL_NAMES[$portal] . ' portal. Please sign in through your own department portal.');
            }

            $departmentId = $user['department_id'] ?? null;

            $session = service('session');
            $session->regenerate(true);
            $session->set([
                'user_id'       => $userId,
                'emp_id'        => $employeeId,
                'full_name'     => $fullName,
                'role'          => $role,
                'department_id' => $departmentId,
                'photo'         => $user['photo'] ?? null,
                'isLoggedIn'    => true,
            ]);

            $this->setNoStoreHeaders();

            return redirect()->to($this->roleLandingUrl());
        }

        return redirect()->to($this->loginUrl($portal))->with('error', 'Invalid employee ID or password.');
    }

    public function login()
    {
        if ($this->isAuthenticated()) {
            $this->setNoStoreHeaders();

            return redirect()->to($this->roleLandingUrl());
        }

        $this->setNoStoreHeaders();

        $portals = [
            'facilities' => 'Facilities Administration and General Services',
            'safety'     => 'Safety and Security Department',
            'asset'      => 'Asset Acquisition and Monitoring Department',
            'iysp'       => 'Sports Equipment Monitoring',
        ];
        $portalKey = (string) $this->request->getGet('portal');

        return view('auth/login', [
            'selectedPortal'    => $portals[$portalKey] ?? null,
            'selectedPortalKey' => isset($portals[$portalKey]) ? $portalKey : '',
        ]);
    }

    public function logout()
    {
        $session = service('session');
        $session->destroy();
        $this->setNoStoreHeaders();

        return redirect()->to('/login');
    }
}
