<?php

namespace App\Controllers;

use App\Models\FireExtinguisherModel;
use App\Models\KeyBorrowLogModel;
use App\Models\AirconUnitModel;
use App\Models\AirconChecklistItemModel;
use App\Models\SafetyWorkOrderModel;
use App\Models\NotificationModel;
use App\Models\TravelModel;
use App\Models\PersonnelModel;

class SafetyController extends BaseController
{
    protected $session;
    protected $fireExtinguisherModel;
    protected $keyBorrowLogModel;
    protected $airconUnitModel;
    protected $airconChecklistModel;
    protected $workOrderModel;
    protected $travelModel;
    protected $personnelModel;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->fireExtinguisherModel = new FireExtinguisherModel();
        $this->keyBorrowLogModel = new KeyBorrowLogModel();
        $this->airconUnitModel = new AirconUnitModel();
        $this->airconChecklistModel = new AirconChecklistItemModel();
        $this->workOrderModel = new SafetyWorkOrderModel();
        $this->travelModel = new TravelModel();
        $this->personnelModel = new PersonnelModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $units = $this->fireExtinguisherModel->findAll();
        $today = date('Y-m-d');

        $needsAttention = count(array_filter($units, fn($u) => in_array($u['status'], ['Defective', 'Missing'], true)));
        $dueForRefill   = count(array_filter($units, fn($u) => $u['status'] === 'Refillable'));
        $overdue        = count(array_filter($units, fn($u) => !empty($u['next_due']) && $u['next_due'] < $today));
        $readiness      = count($units) > 0 ? round((count($units) - $overdue) / count($units) * 100) : 100;

        $feRegistry = array_map(fn($u) => [
            'dbId'      => (int) $u['id'],
            'id'        => $u['unit_id'],
            'type'      => $u['type'],
            'loc'       => $u['location'],
            'floor'     => $u['floor'] ?? 'Ground Floor',
            'kg'        => (float) $u['weight_kg'],
            'lastInsp'  => $u['last_inspection'],
            'nextDue'   => $u['next_due'],
            'status'    => $u['status'],
            'year'      => (int) $u['year_acquired'],
            'inspector' => $u['inspector'] ?? '—',
            'assigned'  => $u['assigned_guard'] ?? '—',
        ], $units);

        // Aircon units are registered from the mobile app (scan a building,
        // register its unit) — this just displays what's already there,
        // same read-only relationship the web side has with fire extinguishers.
        $airconUnits = $this->airconUnitModel->findAll();
        $airconNeedsAttention = count(array_filter($airconUnits, fn($u) =>
            $u['condition_status'] !== 'Operational'
            || (!empty($u['next_schedule']) && $u['next_schedule'] < $today)
        ));

        $airconRegistry = array_map(function ($u) {
            $tasks = $this->airconChecklistModel->getForUnit((int) $u['id']);
            return [
                'id'        => (int) $u['id'],
                'unit'      => $u['unit_name'],
                'loc'       => $u['location'],
                'floor'     => $u['floor'] ?? 'Ground Floor',
                'condition' => $u['condition_status'],
                'lastClean' => $u['last_cleaning'],
                'nextDue'   => $u['next_schedule'],
                'tech'      => $u['assigned_tech'] ?? '—',
                'installedBy' => $u['installed_by'] ?? '—',
                'checklist' => array_map(fn($t) => [
                    'id'   => $t['id'],
                    'task' => $t['task_name'],
                    'done' => (bool) $t['is_done'],
                ], $tasks),
            ];
        }, $airconUnits);

        // "Newly installed" already notifies from Api::saveExtinguisher() /
        // Api::saveAirconUnit() when the mobile app registers a unit. These
        // two ongoing states (not one-time events) get checked here instead,
        // every time someone opens this page, and only notify once per unit
        // while the state persists — no duplicate spam on repeat visits.
        $this->notifyStatusAlerts($units, $airconUnits, $today);

        // Open work orders: reported issues not yet completed/verified.
        $openWorkOrders = $this->workOrderModel->where('stage !=', 'Completed/Verified')->orderBy('priority', 'DESC')->findAll();
        $workOrderRegistry = array_map(fn($w) => [
            'dbId'     => (int) $w['id'],
            'id'       => $w['wo_number'],
            'issue'    => $w['issue'],
            'loc'      => $w['location'],
            'priority' => $w['priority'],
            'stage'    => $w['stage'],
            'reported' => $w['reported_by'] ?? '—',
            'assigned' => $w['assigned_to'] ?? '—',
            'logged'   => $w['date_logged'],
        ], $openWorkOrders);

        return view('safety/index', [
            'title' => 'Maintenance',
            'openModule' => 'safety',
            'fe_registry_json' => $this->jsonForScript($feRegistry),
            'coverage_total'      => count($units),
            'coverage_attention'  => $needsAttention,
            'coverage_refill'     => $dueForRefill,
            'inspection_readiness' => $readiness,
            'aircon_registry_json' => $this->jsonForScript($airconRegistry),
            'aircon_total'         => count($airconUnits),
            'aircon_attention'     => $airconNeedsAttention,
            'work_order_registry_json' => $this->jsonForScript($workOrderRegistry),
            'work_order_total'         => count($openWorkOrders),
            'personnel_options_json'   => $this->jsonForScript(
                array_map(fn($p) => $p['full_name'], $this->personnelModel->where('is_archived', 0)->orderBy('full_name', 'ASC')->findAll())
            ),
        ]);
    }

    // Sets who installed a specific fire extinguisher unit — picked from
    // Personnel so it can be cross-referenced on that person's profile,
    // instead of the free-text field this used to be.
    public function setInstaller($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }
        $name = trim((string) $this->request->getPost('installed_by'));
        $this->fireExtinguisherModel->update($id, ['inspector' => $name !== '' ? $name : null]);
        return $this->response->setJSON(['success' => true]);
    }

    // Same, for aircon units.
    public function setAirconInstaller($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }
        $name = trim((string) $this->request->getPost('installed_by'));
        $this->airconUnitModel->update($id, ['installed_by' => $name !== '' ? $name : null]);
        return $this->response->setJSON(['success' => true]);
    }

    // Fire extinguishers due within 30 days (or already Refillable) and
    // aircon units that need cleaning both get a Notification Center entry —
    // one per unit while that state lasts, checked via an unread notification
    // already mentioning that unit's ID, so reloading this page repeatedly
    // doesn't create duplicates.
    private function notifyStatusAlerts(array $feUnits, array $airconUnits, string $today): void
    {
        $notificationModel = new NotificationModel();
        $todayTs = strtotime($today);

        foreach ($feUnits as $u) {
            $daysLeft = !empty($u['next_due']) ? (int) floor((strtotime($u['next_due']) - $todayTs) / 86400) : null;
            $expiresSoon = $u['status'] === 'Refillable' || ($daysLeft !== null && $daysLeft >= 0 && $daysLeft < 30);
            if (!$expiresSoon) continue;

            $alreadyNotified = $notificationModel->where('category', 'Fire Extinguisher Expiring Soon')
                ->where('is_read', 0)
                ->like('description', $u['unit_id'])
                ->countAllResults() > 0;
            if ($alreadyNotified) continue;

            $notificationModel->insert([
                'category'    => 'Fire Extinguisher Expiring Soon',
                'description' => "Fire extinguisher {$u['unit_id']} at {$u['location']} is due for inspection/refill soon (next due {$u['next_due']}).",
                'recipient'   => 'Safety Team',
                'priority'    => 'MODERATE',
                'status'      => 'Pending',
                'is_read'     => 0,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        foreach ($airconUnits as $u) {
            if (($u['condition_status'] ?? '') !== 'Needs Cleaning') continue;

            $alreadyNotified = $notificationModel->where('category', 'Aircon Needs Cleaning')
                ->where('is_read', 0)
                ->like('description', $u['unit_name'])
                ->countAllResults() > 0;
            if ($alreadyNotified) continue;

            $notificationModel->insert([
                'category'    => 'Aircon Needs Cleaning',
                'description' => "Aircon unit {$u['unit_name']} at {$u['location']} needs cleaning.",
                'recipient'   => 'Maintenance Team',
                'priority'    => 'MODERATE',
                'status'      => 'Pending',
                'is_read'     => 0,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function guardDashboard()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $keyLogs = $this->keyBorrowLogModel->getAllWithTrip();
        $activeBorrows = array_values(array_filter($keyLogs, fn($k) => $k['status'] === 'Active'));

        // Derive the activity feed from the real scan_in/scan_out timestamps —
        // not a fabricated log.
        $activity = [];
        foreach ($keyLogs as $k) {
            $activity[] = ['time' => $k['scan_in'], 'action' => "Key {$k['log_number']} issued to {$k['full_name']} ({$k['department']})"];
            if (!empty($k['scan_out'])) {
                $activity[] = ['time' => $k['scan_out'], 'action' => "Key {$k['log_number']} returned by {$k['full_name']}"];
            }
        }
        usort($activity, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));

        // Trip tickets the guard needs to act on: approved trips awaiting
        // dispatch (check-in), or already In Transit and awaiting return
        // (check-out). Checking in now moves a trip from Approved to In
        // Transit, so both statuses have to stay in this list — otherwise
        // a dispatched trip would vanish from the guard's view before it
        // could ever be checked back out.
        $trips = $this->travelModel->getAllWithDetails();
        $guardTrips = array_values(array_filter($trips, fn($t) => in_array($t['status'], ['Approved', 'In Transit'], true)));
        usort($guardTrips, fn($a, $b) => strtotime($a['travel_date'] . ' ' . $a['departure_time']) <=> strtotime($b['travel_date'] . ' ' . $b['departure_time']));

        return view('safety/guard_dashboard', [
            'title' => 'Guard Dashboard',
            'openModule' => 'safety',
            'trips' => $guardTrips,
            'key_logs_json' => $this->jsonForScript(array_map(fn($k) => [
                'id'       => $k['log_number'],
                'name'     => $k['full_name'],
                'dept'     => $k['department'],
                'key'      => $k['key_item'],
                'inTime'   => date('h:i A', strtotime($k['scan_in'])),
                'status'   => $k['status'],
                'tripId'   => $k['trip_id'] ?? null,
            ], $keyLogs)),
            'activity_json' => $this->jsonForScript(array_map(fn($a) => [
                'time'   => date('h:i A', strtotime($a['time'])),
                'action' => $a['action'],
            ], array_slice($activity, 0, 10))),
        ]);
    }

    public function keylogs()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $keyLogs = $this->keyBorrowLogModel->getAllWithTrip();

        return view('safety/keylogs', [
            'title' => 'Keylogs',
            'openModule' => 'safety',
            'key_logs_json' => $this->jsonForScript(array_map(fn($k) => [
                'id'       => $k['log_number'],
                'name'     => $k['full_name'],
                'dept'     => $k['department'],
                'key'      => $k['key_item'],
                'issued'   => date('h:i A', strtotime($k['scan_in'])),
                'returned' => $k['scan_out'] ? date('h:i A', strtotime($k['scan_out'])) : '—',
                'status'   => $k['status'],
                'guard'    => $k['guard_on_duty'] ?? '—',
            ], $keyLogs)),
        ]);
    }

    // AJAX: resolve a scanned/typed Employee ID to a name + department, so
    // the Scan to Borrow modal can auto-fill them (mirrors Api::personnelLookup).
    public function lookupBorrower($empId = null)
    {
        if (!session()->get('isLoggedIn')) return $this->response->setStatusCode(403);

        $person = $this->personnelModel->getByEmpId($empId);
        if (!$person) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'No personnel found for that ID.']);
        }

        return $this->response->setJSON([
            'full_name' => $person['full_name'],
            'department' => $person['department_name'] ?? '',
        ]);
    }

    public function scanBorrow()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $borrowerId = trim((string) $this->request->getPost('borrower_id'));
        $fullName   = trim((string) $this->request->getPost('full_name'));
        $keyItem    = trim((string) $this->request->getPost('key_item'));

        if ($borrowerId === '' || $fullName === '' || $keyItem === '') {
            return redirect()->back()->withInput()->with('error', 'Borrower ID, name, and key/item are all required.');
        }

        $count = $this->keyBorrowLogModel->countAllResults(false);

        $this->keyBorrowLogModel->insert([
            'log_number'    => sprintf('KL-%03d', $count + 1),
            'borrower_id'   => $borrowerId,
            'full_name'     => $fullName,
            'department'    => $this->request->getPost('department') ?: '',
            'key_item'      => $keyItem,
            'scan_in'       => date('Y-m-d H:i:s'),
            'status'        => 'Active',
            'guard_on_duty' => session()->get('full_name'),
        ]);

        return redirect()->back()->with('success', "Key \"{$keyItem}\" scanned out to {$fullName}.");
    }

    // "Scan to Return" — the guard scans/types the Borrower ID or Key Log #
    // (a USB badge scanner just types characters, same as a keyboard), and
    // whichever active borrow matches is closed out.
    public function scanReturn()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $identifier = trim((string) $this->request->getPost('identifier'));
        if ($identifier === '') {
            return redirect()->back()->with('error', 'Scan or enter a Borrower ID or Key Log # to return.');
        }

        $log = $this->keyBorrowLogModel
            ->where('status', 'Active')
            ->groupStart()
                ->where('log_number', $identifier)
                ->orWhere('borrower_id', $identifier)
            ->groupEnd()
            ->orderBy('id', 'DESC')
            ->first();

        if (!$log) {
            return redirect()->back()->with('error', "No active key borrow found for \"{$identifier}\".");
        }

        $this->keyBorrowLogModel->update($log['id'], [
            'scan_out' => date('Y-m-d H:i:s'),
            'status'   => 'Returned',
        ]);

        return redirect()->back()->with('success', "Key \"{$log['key_item']}\" returned by {$log['full_name']}.");
    }
}