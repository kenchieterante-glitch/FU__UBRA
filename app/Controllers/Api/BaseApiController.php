<?php

namespace App\Controllers\Api;

use App\Libraries\ApiAuth;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseApiController extends Controller
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    protected function ok($data = [], int $code = 200)
    {
        return $this->response->setStatusCode($code)->setJSON(array_merge(['success' => true], is_array($data) ? $data : ['data' => $data]));
    }

    protected function fail(string $message, int $code = 400)
    {
        return $this->response->setStatusCode($code)->setJSON(['success' => false, 'message' => $message]);
    }

    protected function requireAdminOrFail()
    {
        if (!ApiAuth::isAdmin()) {
            return $this->fail('You do not have permission to perform this action.', 403);
        }
        return null;
    }

    protected function currentUserId(): ?int
    {
        return ApiAuth::userId();
    }
}
