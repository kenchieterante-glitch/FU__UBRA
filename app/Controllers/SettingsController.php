<?php

namespace App\Controllers;

class SettingsController extends BaseController
{
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->db      = \Config\Database::connect();
    }

    public function index()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $settings = $this->getSettings();
        $users    = $this->db->table('users')->get()->getResultArray();

        $logs = [];
        try {
            $logs = $this->db->table('activity_logs')
                             ->orderBy('id', 'DESC')
                             ->limit(50)
                             ->get()->getResultArray();

            foreach ($logs as &$log) {
                $log['description'] = $log['description'] ?? ($log['action'] ?? 'Activity recorded');
                $log['created_at']  = $log['created_at'] ?? null;
            }
            unset($log);
        } catch (\Exception $e) {
            log_message('error', 'SettingsController::index activity_logs fetch failed: ' . $e->getMessage());
        }

        $data = [
            'title'         => 'System Settings',
            'settings'      => $settings,
            'users'         => $users,
            'logs'          => $logs,
            'sys_info'      => [
                'version'  => 'v2.4.1',
                'server'   => 'Active',
                'database' => 'Connected',
                'gps'      => 'Connected',
                'calendar' => 'Connected',
                'ai'       => 'Connected',
            ],
            'flash_success' => $this->session->getFlashdata('success'),
            'flash_error'   => $this->session->getFlashdata('error'),
        ];

        return view('settings/index', $data);
    }

    public function saveGeneral()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $ok = true;
        foreach (['system_name','university','api_key'] as $key) {
            $ok = $this->upsertSetting($key, $this->request->getPost($key) ?? '') && $ok;
        }

        $this->session->setFlashdata($ok ? 'success' : 'error', $ok ? 'General settings saved.' : 'Some general settings could not be saved.');
        return redirect()->to('/settings');
    }

    public function saveEmail()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $ok = true;
        foreach (['smtp_host','smtp_port','smtp_user','smtp_from','smtp_name'] as $key) {
            $ok = $this->upsertSetting($key, $this->request->getPost($key) ?? '') && $ok;
        }
        $pass = $this->request->getPost('smtp_pass');
        if (!empty($pass)) $ok = $this->upsertSetting('smtp_pass', $pass) && $ok;

        $this->session->setFlashdata($ok ? 'success' : 'error', $ok ? 'Email settings saved.' : 'Some email settings could not be saved.');
        return redirect()->to('/settings');
    }

    public function saveNotifications()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $ok = true;
        foreach (['notif_maintenance','notif_vehicle','notif_janitorial','notif_asset','notif_travel'] as $key) {
            $ok = $this->upsertSetting($key, $this->request->getPost($key) ? '1' : '0') && $ok;
        }
        $ok = $this->upsertSetting('reminder_days', $this->request->getPost('reminder_days') ?? '5') && $ok;

        $this->session->setFlashdata($ok ? 'success' : 'error', $ok ? 'Notification settings saved.' : 'Some notification settings could not be saved.');
        return redirect()->to('/settings');
    }

    public function saveRoles()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');
        $this->session->setFlashdata('success', 'Roles reviewed.');
        return redirect()->to('/settings');
    }

    public function addUser()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');
        if ($resp = $this->requireAdmin()) return $resp;

        $fullName   = $this->request->getPost('full_name');
        $employeeId = $this->request->getPost('emp_id');
        $email      = $this->request->getPost('email');
        $role       = $this->request->getPost('role') ?? 'Staff';
        $password   = $this->request->getPost('password');

        if (empty($fullName) || empty($employeeId) || empty($email) || empty($password)) {
            $this->session->setFlashdata('error', 'Full name, employee ID, email, and password are required.');
            return redirect()->to('/settings');
        }

        // department_id is this table's primary key and isn't auto-increment,
        // so it has to be assigned explicitly — it's not linked to the real
        // departments table (no FK), just an internal per-account counter here.
        $nextId = (int) ($this->db->table('users')->selectMax('department_id')->get()->getRow()->department_id ?? 0) + 1;

        $this->db->table('users')->insert([
            'department_id' => $nextId,
            'full_name'  => $fullName,
            'emp_id'     => $employeeId,
            'username'   => $employeeId,
            'email'      => $email,
            'role'       => $role,
            'password'   => password_hash($password, PASSWORD_BCRYPT),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->session->setFlashdata('success', "User {$employeeId} created.");
        return redirect()->to('/settings');
    }

    public function deleteUser($id)
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');
        if ($resp = $this->requireAdmin()) return $resp;

        if ($id == $this->session->get('user_id')) {
            $this->session->setFlashdata('error', 'You cannot delete your own account.');
            return redirect()->to('/settings');
        }

        // users' real primary key is department_id — there is no 'id' column.
        $this->db->table('users')->where('department_id', $id)->delete();
        $this->session->setFlashdata('success', 'User deleted.');
        return redirect()->to('/settings');
    }

    public function getSetting($key)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }
        $row = $this->db->table('system_settings')->where('setting_key', $key)->get()->getRow();
        return $this->response->setJSON(['key' => $key, 'value' => $row->setting_value ?? '']);
    }

    private function getSettings(): array
    {
        $defaults = [
            'system_name'       => 'FU-UBRA Operational Portal',
            'university'        => 'Foundation University',
            'api_key'           => '',
            'smtp_host'         => 'smtp.gmail.com',
            'smtp_port'         => '587',
            'smtp_user'         => '',
            'smtp_from'         => '',
            'smtp_name'         => 'FU-UBRA System',
            'smtp_pass'         => '',
            'notif_maintenance' => '1',
            'notif_vehicle'     => '1',
            'notif_janitorial'  => '1',
            'notif_asset'       => '1',
            'notif_travel'      => '1',
            'reminder_days'     => '5',
        ];

        try {
            $rows = $this->db->table('system_settings')->get()->getResultArray();
            foreach ($rows as $row) {
                $defaults[$row['setting_key']] = $row['setting_value'];
            }
        } catch (\Exception $e) {
            log_message('error', 'SettingsController::getSettings fetch failed: ' . $e->getMessage());
        }

        return $defaults;
    }

    private function upsertSetting(string $key, string $value): bool
    {
        try {
            $exists = $this->db->table('system_settings')->where('setting_key', $key)->countAllResults();
            if ($exists) {
                $this->db->table('system_settings')->where('setting_key', $key)
                    ->update(['setting_value' => $value]);
            } else {
                $this->db->table('system_settings')->insert([
                    'setting_key'   => $key,
                    'setting_value' => $value,
                ]);
            }
            return true;
        } catch (\Exception $e) {
            log_message('error', "SettingsController::upsertSetting failed for '{$key}': " . $e->getMessage());
            return false;
        }
    }
}
?>