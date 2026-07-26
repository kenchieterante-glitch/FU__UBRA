<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not alter the request or response.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $currentPath = trim(service('uri')->getPath(), '/');

        // Paths that don't require authentication
        $publicPaths = [
            'login',
            'logout',
            'auth/login',
            'auth/logout',
            'auth/register',
            'auth/forgot-password',
        ];

        // Check if the current path is in public paths
        $isPublicPath = false;
        foreach ($publicPaths as $path) {
            if ($currentPath === trim($path, '/')) {
                $isPublicPath = true;
                break;
            }
        }

        // If it's a public path, allow access
        if ($isPublicPath) {
            return;
        }

        // Check if user is logged in
        if (!$session->get('isLoggedIn')) {
            // Not logged in, redirect to login
            return redirect()->to('/login')
                ->with('error', 'Please log in to access this page.');
        }

        // User is logged in, continue
        return;
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * an exception or early exit.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No after filter needed for authentication
    }
}
