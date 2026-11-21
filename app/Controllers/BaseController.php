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
     * Janitorial Supervisor — same scoped pattern as Security/Tools/
     * Facilities: own dashboard, restricted sidebar (Janitorial Monitoring
     * map + Calendar only).
     */
    protected function isJanitorialSupervisor(): bool
    {
        return strtolower($this->userRole()) === 'janitorial';
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
        return $this->isAdmin() || $this->isSecurityHead() || strtolower($this->userRole()) === 'guard';
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
            'security'   => '/security-dept',
            'assets'     => '/assets-dept',
            'sports'     => '/sports-dept',
            'tools'      => '/tools-dashboard',
            'facilities' => '/facilities',
            'janitorial' => '/facilities/waste',
            'safety'     => '/safety',
            'guard'      => '/guard-station',
            'personnel & tools' => '/tools/power-tools',
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
     * Like requireAdmin(), but roles limited to some Personnel categories
     * (PersonnelScope) may also add, edit and archive personnel in those
     * categories. Pass the position(s) involved: the existing record's and,
     * for add/edit, the submitted one.
     */
    protected function requirePersonnelManager(?string ...$positions)
    {
        $role = $this->userRole();
        if ($this->isAdmin()) {
            return null;
        }
        if (\App\Libraries\PersonnelScope::categoriesFor($role) === null) {
            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        }
        foreach ($positions as $position) {
            if (!\App\Libraries\PersonnelScope::allowsPosition($role, $position)) {
                return redirect()->back()->withInput()->with('error', 'You can only manage personnel in your own categories.');
            }
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
     * Writes to the existing activity_logs table/model — previously used by
     * only the Job Order Personnel Monitoring module, now called from every
     * module's create/update/delete actions so Information Hub reflects a
     * real system-wide audit trail. The acting user's name is embedded
     * directly in the stored action text ("Name: did something") rather
     * than added as a new column, so every caller and every existing log
     * row stays compatible with the activity_logs table exactly as it is.
     */
    /**
     * Runs a group of database writes as ONE transaction: all of them are saved, or none are.
     * Inside $fn, call lockRow() first for anything two people could change at once, and throw
     * \RuntimeException('message') when the record was taken or changed by someone else.
     * Returns ['ok' => true, 'value' => ...] or ['ok' => false, 'conflict' => bool, 'error' => 'message'].
     */
    protected function atomic(callable $fn): array
    {
        $db = db_connect();
        $db->transStart();
        try {
            $value = $fn($db);
        } catch (\RuntimeException $e) {
            $db->transRollback();
            return ['ok' => false, 'conflict' => true, 'error' => $e->getMessage()];
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Transaction failed: ' . $e->getMessage());
            return ['ok' => false, 'conflict' => false, 'error' => 'Could not save. Nothing was changed; please try again.'];
        }
        $db->transComplete();
        if ($db->transStatus() === false) {
            return ['ok' => false, 'conflict' => false, 'error' => 'Could not save. Nothing was changed; please try again.'];
        }

        return ['ok' => true, 'value' => $value];
    }

    /** Reads one row and locks it until the surrounding transaction ends (SELECT ... FOR UPDATE). Table names are fixed in code, never user input. */
    protected function lockRow(string $table, int $id): ?array
    {
        if (!in_array($table, ['tools', 'facility_keys', 'borrow_records'], true)) {
            throw new \InvalidArgumentException('Table not lockable.');
        }

        return db_connect()->query("SELECT * FROM `{$table}` WHERE id = ? FOR UPDATE", [$id])->getRowArray() ?: null;
    }

    protected function logActivity(string $module, string $action): void
    {
        try {
            $actor = trim((string) (service('session')->get('full_name') ?? ''));
            $entry = $actor !== '' ? "{$actor}: {$action}" : $action;

            (new \App\Models\ActivityLogModel())->insert([
                'user_id' => service('session')->get('user_id'),
                'module'  => $module,
                'action'  => $entry,
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
