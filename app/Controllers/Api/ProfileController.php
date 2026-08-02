<?php

namespace App\Controllers\Api;

use App\Libraries\ApiAuth;
use App\Models\UserModel;

class ProfileController extends BaseApiController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        return $this->ok(['user' => ApiAuth::user()]);
    }

    public function updateProfile()
    {
        $deptId = ApiAuth::userId();

        $this->userModel->update($deptId, [
            'full_name' => $this->request->getPost('name'),
            'email'     => $this->request->getPost('email'),
            'emp_id'    => $this->request->getPost('emp_id'),
        ]);

        return $this->ok();
    }

    public function changePassword()
    {
        $current = (string) $this->request->getPost('current_password');
        $new     = (string) $this->request->getPost('new_password');
        $deptId  = ApiAuth::userId();

        $user = $this->userModel->find($deptId);
        if (!$user || !password_verify($current, $user['password'])) {
            return $this->fail('Current password is incorrect.', 422);
        }

        $this->userModel->update($deptId, ['password' => password_hash($new, PASSWORD_BCRYPT)]);

        return $this->ok();
    }
}
