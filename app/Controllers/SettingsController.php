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
        } catch (\Exception $e) {}

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

        foreach (['system_name','university','api_key','theme'] as $key) {
            $this->upsertSetting($key, $this->request->getPost($key) ?? '');
        }

        $this->session->setFlashdata('success', 'General settings saved.');
        return redirect()->to('/settings');
    }

    public function saveEmail()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        foreach (['smtp_host','smtp_port','smtp_user','smtp_from','smtp_name'] as $key) {
            $this->upsertSetting($key, $this->request->getPost($key) ?? '');
        }
        $pass = $this->request->getPost('smtp_pass');
        if (!empty($pass)) $this->upsertSetting('smtp_pass', $pass);

        $this->session->setFlashdata('success', 'Email settings saved.');
        return redirect()->to('/settings');
    }

    public function saveNotifications()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        foreach (['notif_maintenance','notif_vehicle','notif_janitorial','notif_asset','notif_travel'] as $key) {
            $this->upsertSetting($key, $this->request->getPost($key) ? '1' : '0');
        }
        $this->upsertSetting('reminder_days', $this->request->getPost('reminder_days') ?? '5');

        $this->session->setFlashdata('success', 'Notification settings saved.');
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

        $username = $this->request->getPost('username');
        $email    = $this->request->getPost('email');
        $role     = $this->request->getPost('role') ?? 'Staff';
        $password = $this->request->getPost('password');

        if (empty($username) || empty($email) || empty($password)) {
            $this->session->setFlashdata('error', 'Username, email, and password are required.');
            return redirect()->to('/settings');
        }

        $this->db->table('users')->insert([
            'username'   => $username,
            'email'      => $email,
            'role'       => $role,
            'password'   => password_hash($password, PASSWORD_BCRYPT),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->session->setFlashdata('success', "User {$username} created.");
        return redirect()->to('/settings');
    }

    public function deleteUser($id)
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        if ($id == $this->session->get('user_id')) {
            $this->session->setFlashdata('error', 'You cannot delete your own account.');
            return redirect()->to('/settings');
        }

        $this->db->table('users')->where('id', $id)->delete();
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
            'theme'             => 'Maroon Theme',
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
        } catch (\Exception $e) {}

        return $defaults;
    }

    private function upsertSetting(string $key, string $value): void
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
        } catch (\Exception $e) {}
    }
}
?>