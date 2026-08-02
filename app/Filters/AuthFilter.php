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
        $currentPath = trim($request->getPath(), '/');

        if ($currentPath === '' || $currentPath === 'login') {
            if ($session->get('isLoggedIn')) {
                return redirect()->to('/dashboard');
            }

            return;
        }

        $publicPaths = [
            'logout',
            'auth/login',
            'auth/logout',
            'auth/register',
            'auth/forgot-password',
        ];

        $isPublicPath = false;
        foreach ($publicPaths as $path) {
            if ($currentPath === trim($path, '/')) {
                $isPublicPath = true;
                break;
            }
        }

        if ($isPublicPath || str_starts_with($currentPath, 'auth/') || str_starts_with($currentPath, 'api/')) {
            return;
        }

        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')
                ->with('error', 'Please log in to access this page.');
        }

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
        // Prevent authenticated pages from being cached by the browser.
        // This ensures that when a user clicks the browser's back button
        // (e.g. after logging out), the browser re-requests the page from
        // the server instead of showing a stale cached copy.
        $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', '0');
    }
}
