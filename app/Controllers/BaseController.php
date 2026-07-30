<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    protected function isAuthenticated(): bool
    {
        return (bool) service('session')->get('isLoggedIn');
    }

    protected function isAdmin(): bool
    {
        return strtolower((string) service('session')->get('role')) === 'administrator';
    }

    /**
     * Call at the top of a destructive/admin-only action. Returns a redirect
     * response if the current session isn't an Administrator, or null to
     * let the caller proceed.
     */
    protected function requireAdmin()
    {
        if (!$this->isAdmin()) {
            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        }

        return null;
    }

    /**
     * json_encode() for data that gets embedded straight into a <script> block
     * (const x = <?= ... ?>;). Plain json_encode() doesn't escape '</script>',
     * quotes, or ampersands, so a DB field containing that literal string could
     * break out of the script tag — this closes that off.
     */
    protected function jsonForScript($data): string
    {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    protected function setNoStoreHeaders(): void
    {
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', '0');
    }

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }
}
