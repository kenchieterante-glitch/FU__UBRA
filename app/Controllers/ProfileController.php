<?php

namespace App\Controllers;

use App\Models\UserModel;

class ProfileController extends BaseController
{
    protected $session;
    protected $db;
    protected $userModel;

    public function __construct()
    {
        $this->session   = \Config\Services::session();
        $this->db        = \Config\Database::connect();
        $this->userModel = new UserModel();
    }

    // The users table's real primary key is department_id — it has no 'id' column —
    // so the logged-in user must be looked up by the credential they logged in with.
    private function currentUser(): ?array
    {
        return $this->userModel->getByEmployeeId($this->session->get('emp_id'));
    }

    public function index()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'My Profile',
            'user'  => $this->currentUser(),
        ];

        return view('profile/index', $data);
    }

    public function updateProfile()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $user = $this->currentUser();
        if (!$user) {
            return redirect()->to('/profile')->with('error', 'Could not find your account.');
        }

        $update = [
            'full_name' => $this->request->getPost('name'),
            'email'     => $this->request->getPost('email'),
            'emp_id'    => $this->request->getPost('emp_id'),
        ];

        $photoFile = $this->request->getFile('photo');
        if ($photoFile && $photoFile->isValid() && !$photoFile->hasMoved()) {
            if (!$this->validate([
                'photo' => 'is_image[photo]|max_size[photo,2048]|mime_in[photo,image/jpg,image/jpeg,image/png,image/webp]',
            ])) {
                return redirect()->to('/profile')->withInput()->with('error', 'Profile picture must be a JPG, PNG, or WEBP image under 2MB.');
            }

            $uploadPath = FCPATH . 'uploads';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $newName = $photoFile->getRandomName();
            $photoFile->move($uploadPath, $newName);

            // Remove the previous photo now that the new one is safely on
            // disk, so uploads don't accumulate orphaned files forever.
            if (!empty($user['photo']) && is_file($uploadPath . '/' . $user['photo'])) {
                @unlink($uploadPath . '/' . $user['photo']);
            }

            $update['photo'] = $newName;
            $this->session->set('photo', $newName);
        }

        $this->db->table('users')->where('department_id', $user['department_id'])->update($update);

        $this->session->set('full_name', $this->request->getPost('name'));
        $this->session->setFlashdata('success', 'Profile updated successfully.');
        return redirect()->to('/profile');
    }

    public function changePassword()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $user    = $this->currentUser();
        $current = $this->request->getPost('current_password');
        $new     = $this->request->getPost('new_password');

        if ($user && password_verify($current, $user['password'])) {
            $this->db->table('users')->where('department_id', $user['department_id'])->update([
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
