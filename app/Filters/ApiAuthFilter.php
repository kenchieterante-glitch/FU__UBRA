<?php

namespace App\Filters;

use App\Libraries\ApiAuth;
use App\Models\ApiTokenModel;
use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        ApiAuth::set(null);

        $header = $request->getHeaderLine('Authorization');

        if (!preg_match('/^Bearer\s+(.+)$/i', trim($header), $matches)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['success' => false, 'message' => 'Missing or invalid Authorization header']);
        }

        $plainToken = trim($matches[1]);
        $tokenModel = new ApiTokenModel();
        $tokenRow   = $tokenModel->findByPlainToken($plainToken);

        if (!$tokenRow) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['success' => false, 'message' => 'Invalid or expired token']);
        }

        $userModel = new UserModel();
        $user      = $userModel->find($tokenRow['user_id']);

        if (!$user) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['success' => false, 'message' => 'Token user no longer exists']);
        }

        unset($user['password']);
        ApiAuth::set($user);
        $tokenModel->touch($tokenRow['id']);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}
