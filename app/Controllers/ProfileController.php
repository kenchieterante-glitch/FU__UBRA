<?php

namespace App\Controllers;

class ProfileController extends BaseController
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
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $user_id = $this->session->get('user_id');
        $user    = $this->db->table('users')->where('id', $user_id)->get()->getRowArray();

        $data = [
            'title'         => 'My Profile',
            'user'          => $user,
            'flash_success' => $this->session->getFlashdata('success'),
            'flash_error'   => $this->session->getFlashdata('error'),
        ];

        return view('profile/index', $data);
    }

    public function updateProfile()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $user_id = $this->session->get('user_id');
        $this->db->table('users')->where('id', $user_id)->update([
            'name'    => $this->request->getPost('name'),
            'email'   => $this->request->getPost('email'),
            'emp_id'  => $this->request->getPost('emp_id'),
            'username'=> $this->request->getPost('emp_id'),
        ]);

        $this->session->setFlashdata('success', 'Profile updated successfully.');
        return redirect()->to('/profile');
    }

    public function changePassword()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $user_id = $this->session->get('user_id');
        $user    = $this->db->table('users')->where('id', $user_id)->get()->getRowArray();
        $current = $this->request->getPost('current_password');
        $new     = $this->request->getPost('new_password');

        if (password_verify($current, $user['password'])) {
            $this->db->table('users')->where('id', $user_id)->update([
                'password' => password_hash($new, PASSWORD_BCRYPT),
            ]);
            $this->session->setFlashdata('success', 'Password changed successfully.');
        } else {
            $this->session->setFlashdata('error', 'Current password is incorrect.');
        }

        return redirect()->to('/profile');
    }

    public function updateSettings()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $this->session->setFlashdata('success', 'Preferences saved.');
        return redirect()->to('/profile');
    }
}
