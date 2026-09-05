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
     * Head of Security and Head of Tools Equipment are scoped to their own
     * sub-system (own dashboard, restricted sidebar) — everyone else
     * (Administrator, and any other existing role) keeps full access,
     * unchanged from before these two roles existed.
     */
    protected function userRole(): string
    {
        return (string) service('session')->get('role');
    }

    protected function isSecurityHead(): bool
    {
        return strtolower($this->userRole()) === 'security';
    }

    protected function isToolsHead(): bool
    {
        return strtolower($this->userRole()) === 'tools';
    }

    protected function isFacilitiesSupervisor(): bool
    {
        return strtolower($this->userRole()) === 'facilities';
    }

    /**
     * Gate check-in/check-out at the gate (the Guard page) to the Head of
     * Security role — the only login this system has for guard duty — plus
     * Administrator as the usual superuser override. Previously these
     * actions had no restriction at all: any logged-in role could scan a
     * trip in/out.
     */
    protected function isGuard(): bool
    {
        return $this->isAdmin() || $this->isSecurityHead();
    }

    protected function requireGuard()
    {
        if (!$this->isGuard()) {
            return redirect()->back()->with('error', 'Only the guard can check trip tickets in or out.');
        }

        return null;
    }

    protected function roleLandingUrl(): string
    {
        return match (strtolower($this->userRole())) {
            'security'   => '/security-dashboard',
            'tools'      => '/tools-dashboard',
            'facilities' => '/facilities-dashboard',
            default      => '/dashboard',
        };
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
     * Job Order Personnel Monitoring is managed by Administrator or
     * the Facilities Supervisor (Head of Facilities) — narrower than
     * requireAdmin() because Facilities is the intended day-to-day owner of
     * this module, per the module's design (MIS shouldn't be the one
     * manually monitoring Job Order personnel).
     */
    protected function canManageJobOrderPersonnel(): bool
    {
        return $this->isAdmin() || $this->isFacilitiesSupervisor();
    }

    protected function requireJobOrderManager()
    {
        if (!$this->canManageJobOrderPersonnel()) {
            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        }

        return null;
    }

    /**
     * Writes to the existing activity_logs table/model (previously read-only
     * everywhere in the app — Settings displays it, but nothing wrote to
     * it). Used by the Job Order Personnel Monitoring module for
     * its audit trail rather than introducing a second logging table.
     */
    protected function logActivity(string $module, string $action): void
    {
        try {
            (new \App\Models\ActivityLogModel())->insert([
                'user_id' => service('session')->get('user_id'),
                'module'  => $module,
                'action'  => $action,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'ActivityLog insert failed: ' . $e->getMessage());
        }
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
