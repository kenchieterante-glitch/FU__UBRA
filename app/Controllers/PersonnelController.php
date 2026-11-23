<?php

namespace App\Controllers;

use App\Models\PersonnelModel;
use App\Models\DepartmentModel;
use App\Models\JobOrderModel;
use App\Libraries\JobOrderAssignmentService;
use App\Libraries\PersonnelScope;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class PersonnelController extends BaseController
{
    protected $personnelModel;
    protected $departmentModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->personnelModel = new PersonnelModel();
        $this->departmentModel = new DepartmentModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = array_merge([
            'title'       => 'Personnel Management',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->getAllWithDetails(),
            'departments' => $this->departmentModel->findAll(),
        ], $this->getStatCounts());

        return view('personnel/index', $data);
    }

    public function drivers()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = array_merge([
            'title'       => 'Drivers',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->getDrivers(),
            'departments' => $this->departmentModel->findAll(),
        ], $this->getStatCounts());

        return view('personnel/index', $data);
    }

    public function janitors()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = array_merge([
            'title'       => 'Janitors',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->getByCategory('janitors'),
            'departments' => $this->departmentModel->findAll(),
        ], $this->getStatCounts());

        return view('personnel/index', $data);
    }

    public function carpentries()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = array_merge([
            'title'       => 'Carpentries Shop',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->getByCategory('carpentries'),
            'departments' => $this->departmentModel->findAll(),
        ], $this->getStatCounts());

        return view('personnel/index', $data);
    }

    public function security()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = array_merge([
            'title'       => 'Security',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->getByCategory('security'),
            'departments' => $this->departmentModel->findAll(),
        ], $this->getStatCounts());

        return view('personnel/index', $data);
    }

    public function constructionWorkers()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = array_merge([
            'title'       => 'Construction Workers',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->getByCategory('construction'),
            'departments' => $this->departmentModel->findAll(),
        ], $this->getStatCounts());

        return view('personnel/index', $data);
    }

    public function maintenance(?string $trade = null)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Sub-pages under Maintenance (Electrician, Plumber, ...); no trade = everyone in Maintenance.
        $trade = $trade === null ? null : strtolower($trade);
        $trades = PersonnelScope::MAINTENANCE_TRADES;
        if ($trade !== null && !isset($trades[$trade])) {
            return redirect()->to('/personnel/maintenance');
        }

        $rows = $this->personnelModel->getByCategory('maintenance');
        if ($trade !== null) {
            $rows = array_values(array_filter($rows, static fn($p) => PersonnelScope::maintenanceTradeOf($p['position'] ?? null) === $trade));
        }

        $data = array_merge([
            'title'       => 'Maintenance',
            'tradeLabel'  => $trade === null ? '' : ($trades[$trade][0]),
            'pageCss'     => 'personnel.css',
            'personnel'   => $rows,
            'departments' => $this->departmentModel->findAll(),
        ], $this->getStatCounts());

        return view('personnel/index', $data);
    }

    // Mirrors drivers()/janitors()/etc — a filtered tab, but keyed on
    // employment_type instead of a position keyword.
    public function jobOrder()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = array_merge([
            'title'       => 'Job Order Personnel',
            'pageCss'     => 'personnel.css',
            'personnel'   => $this->personnelModel->getJobOrderPersonnel(),
            'departments' => $this->departmentModel->findAll(),
        ], $this->getStatCounts());

        return view('personnel/index', $data);
    }

    // Lets a Job Order be assigned starting from a Personnel row, instead of
    // only reachable from inside a specific Job Order's page — same
    // underlying assignment as JobOrderController::assignPersonnel(), just
    // entered from the other side.
    public function assignJobOrder($id)
    {
        if ($resp = $this->requireJobOrderManager()) return $resp;

        $jobOrderId = (int) $this->request->getPost('job_order_id');
        $result = (new JobOrderAssignmentService())->assign((int) $id, $jobOrderId, $this->request->getPost());

        if (!$result['ok']) {
            return redirect()->back()->with('error', $result['error']);
        }

        $person = $this->personnelModel->find($id);
        $this->logActivity('Personnel Monitoring', "Assigned {$person['full_name']} to Job Order {$result['jobOrder']['job_order_number']}");

        return redirect()->to('/personnel')->with('success', 'Assigned to Job Order successfully.');
    }

    public function view($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $person = $this->personnelModel->getWithDetails((int) $id);
        if (!$person || !PersonnelScope::allowsPosition((string) session()->get('role'), $person['position'] ?? null)) {
            return $this->personnelReturn()->with('error', 'Personnel record not found.');
        }

        $assignmentModel = new \App\Models\PersonnelAssignmentModel();
        $contractModel   = new \App\Models\PersonnelContractModel();
        $documentModel   = new \App\Models\PersonnelDocumentModel();
        $docTypeModel    = new \App\Models\DocumentRequirementTypeModel();

        $requiredDocTypes = $docTypeModel->getRequired();
        $documents        = $documentModel->getForPersonnel((int) $id);
        $completeness     = $documentModel->completenessForPersonnel((int) $id, $requiredDocTypes);

        // The assignment and its contract are always created together
        // (JobOrderAssignmentService) with matching job_order_id + start
        // date — merge them here so the profile shows one "Job Order
        // Assignment" section instead of a separate Assignment card and
        // Contract table repeating the same Job Order/period info.
        $contracts = $contractModel->getForPersonnel((int) $id);
        $findContract = function (?array $assignment) use ($contracts) {
            if (!$assignment) return null;
            foreach ($contracts as $c) {
                if ((int) $c['job_order_id'] === (int) $assignment['job_order_id']
                    && $c['contract_start_date'] === $assignment['assignment_start_date']) {
                    return $c;
                }
            }
            return null;
        };

        $activeAssignment = $assignmentModel->getActiveForPersonnel((int) $id);
        // Past assignments only — the current one already has its own card
        // above, so it isn't repeated here too.
        $assignmentHistory = array_map(function ($h) use ($findContract) {
            $h['contract'] = $findContract($h);
            return $h;
        }, array_values(array_filter(
            $assignmentModel->getHistoryForPersonnel((int) $id),
            fn($h) => $h['status'] !== 'ACTIVE'
        )));

        // Equipment this person is credited with installing — matched by
        // full name against fire_extinguishers.inspector and
        // aircon_units.installed_by, set from the Safety page's "Set
        // Installer" action.
        $installedExtinguishers = [];
        $installedAircon        = [];
        if (!empty($person['full_name'])) {
            $installedExtinguishers = (new \App\Models\FireExtinguisherModel())
                ->where('inspector', $person['full_name'])->orderBy('location', 'ASC')->findAll();
            $installedAircon = (new \App\Models\AirconUnitModel())
                ->where('installed_by', $person['full_name'])->orderBy('location', 'ASC')->findAll();
        }

        return view('personnel/view', [
            'title'             => $person['full_name'],
            'pageCss'           => 'personnel.css',
            'person'            => $person,
            'activeAssignment'  => $activeAssignment,
            'activeContract'    => $findContract($activeAssignment),
            'assignmentHistory' => $assignmentHistory,
            'documents'         => $documents,
            'documentTypes'     => $docTypeModel->getAllOrdered(),
            'completeness'      => $completeness,
            'installedExtinguishers' => $installedExtinguishers,
            'installedAircon'        => $installedAircon,
        ]);
    }

    // AJAX: compact profile data for the row-click detail popup on the
    // Personnel Management table — same underlying data as view(), just
    // the fields that fit a quick popup instead of the full profile page,
    // and fetched on demand instead of loaded for every row up front.
    /**
     * Looks up employees in the FU MIS with UBRA's own API token.
     * /personnel/mis-lookup?employee_id=… returns one employee (404 if the
     * MIS has none); ?search=…&department=…&page=… returns a page of them,
     * and ?all=1 (with optional search/department) returns every match.
     */
    public function misLookup()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $mis = new \App\Libraries\FuMisClient();
        if (!$mis->isConfigured()) {
            return $this->response->setStatusCode(503)->setJSON(['error' => 'FU MIS is not configured on this server.']);
        }

        $employeeId = trim((string) $this->request->getGet('employee_id'));
        if ($employeeId !== '') {
            $res = $mis->findEmployee($employeeId);
            if (!$res['ok']) {
                return $this->response->setStatusCode(502)->setJSON(['error' => $res['error']]);
            }
            if ($res['employee'] === null) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'No FU employee with ID ' . $employeeId . ' was found in the FU MIS.']);
            }

            return $this->response->setJSON(['employee' => $res['employee']]);
        }

        $filters = [
            'search'     => $this->request->getGet('search'),
            'department' => $this->request->getGet('department'),
        ];
        // ?all=1 returns every matching employee across all pages; otherwise one page.
        $res = $this->request->getGet('all')
            ? $mis->allEmployees($filters)
            : $mis->searchEmployees($filters + [
                'limit' => $this->request->getGet('limit') ?: 25,
                'page'  => $this->request->getGet('page') ?: 1,
            ]);
        if (!$res['ok']) {
            return $this->response->setStatusCode(502)->setJSON(['error' => $res['error']]);
        }

        return $this->response->setJSON($res);
    }

    public function detailJson($id)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $person = $this->personnelModel->getWithDetails((int) $id);
        if (!$person || !PersonnelScope::allowsPosition((string) session()->get('role'), $person['position'] ?? null)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        $assignmentModel = new \App\Models\PersonnelAssignmentModel();
        $documentModel   = new \App\Models\PersonnelDocumentModel();
        $docTypeModel    = new \App\Models\DocumentRequirementTypeModel();

        $activeAssignment = $assignmentModel->getActiveForPersonnel((int) $id);
        $history = array_values(array_filter(
            $assignmentModel->getHistoryForPersonnel((int) $id),
            fn($h) => $h['status'] !== 'ACTIVE'
        ));
        $completeness = $documentModel->completenessForPersonnel((int) $id, $docTypeModel->getRequired());

        return $this->response->setJSON([
            'name'           => $person['full_name'],
            'empId'          => $person['emp_id'],
            'email'          => $person['email'] ?: '—',
            'contactNumber'  => $person['contact_number'] ?: '—',
            'department'     => $person['department_name'] ?? 'Unassigned',
            'position'       => $person['position'] ?: '—',
            'employmentType' => ($person['employment_type'] ?? 'Regular') === 'JobOrder' ? 'Job Order' : 'Regular',
            'status'         => $person['status'] ?? 'Active',
            'assignedTask'   => $person['assigned_task'] ?: 'No current assignment',
            'activeAssignment' => $activeAssignment ? [
                'jobOrder'   => $activeAssignment['job_order_number'] . ' — ' . $activeAssignment['job_order_title'],
                'location'   => $activeAssignment['assignment_location'] ?: '—',
                'supervisor' => $activeAssignment['supervisor'] ?: '—',
                'period'     => $activeAssignment['assignment_start_date'] . ' to ' . ($activeAssignment['assignment_end_date'] ?: 'present'),
            ] : null,
            'assignmentHistory' => array_map(fn($h) => [
                'jobOrder' => $h['job_order_number'] . ' — ' . $h['job_order_title'],
                'location' => $h['assignment_location'] ?: '—',
                'period'   => $h['assignment_start_date'] . ' to ' . ($h['assignment_end_date'] ?: 'present'),
                'status'   => $h['status'],
            ], $history),
            'documents' => array_map(fn($d) => [
                'type'   => $d['document_type_name'] ?? 'Document',
                'status' => $d['verification_status'],
                'expiry' => $d['expiration_date'] ?: '—',
            ], $documentModel->getForPersonnel((int) $id)),
            'documentCompleteness' => "{$completeness['verified']} of {$completeness['required']} required documents verified",
        ]);
    }

    /** After add/edit/archive: the full list for unscoped roles, the page they came from for category-scoped ones. */
    private function personnelReturn()
    {
        return PersonnelScope::categoriesFor((string) session()->get('role')) === null
            ? redirect()->to('/personnel')
            : redirect()->back();
    }

    private function getStatCounts(): array
    {
        $byCategory = $this->personnelModel->countByCategory();

        return [
            'total_personnel_count' => $this->personnelModel->where('is_archived', 0)->countAllResults(),
            'drivers_count'         => $byCategory['drivers'],
            'janitors_count'        => $byCategory['janitors'],
            'carpentries_count'     => $byCategory['carpentries'],
            'maintenance_count'     => $byCategory['maintenance'],
            'construction_count'    => $byCategory['construction'],
            'security_count'        => $byCategory['security'],
            'job_order_count'       => $this->personnelModel->where('is_archived', 0)->where('employment_type', 'JobOrder')->countAllResults(),
            'active_count'          => $this->personnelModel->where('is_archived', 0)->where('status', 'Active')->countAllResults(),
            'on_leave_count'        => $this->personnelModel->where('is_archived', 0)->where('status', 'On Leave')->countAllResults(),
            'jobOrders'             => (new JobOrderModel())->getForDropdown(),
        ];
    }

    public function add()
    {
        if ($resp = $this->requirePersonnelManager($this->request->getPost('position'))) return $resp;

        $empId = trim((string) $this->request->getPost('emp_id'));

        if (trim((string) $this->request->getPost('full_name')) === '') {
            return redirect()->back()->withInput()->with('error', 'Full name is required.');
        }
        if ($empId !== '' && $this->personnelModel->isEmpIdTaken($empId)) {
            return redirect()->back()->withInput()->with('error', 'Employee ID already exists.');
        }

        $name = trim((string) $this->request->getPost('full_name'));
        try {
            $this->personnelModel->insert([
                'emp_id'        => $empId,
                'full_name'     => $name,
                'email'         => $this->request->getPost('email'),
                'contact_number' => $this->request->getPost('contact_number'),
                'department_id' => $this->request->getPost('department_id'),
                'position'      => $this->request->getPost('position'),
                'assigned_task' => $this->request->getPost('assigned_task'),
                'status'        => $this->request->getPost('status') ?? 'Active',
            ]);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            return redirect()->back()->withInput()->with('error', 'Could not save personnel because the employee ID is already in use.');
        }
        $this->logActivity('Personnel', "Added personnel {$name} ({$empId})");

        return $this->personnelReturn()->with('success', 'Personnel added successfully.');
    }

    public function edit($id)
    {
        $existing = $this->personnelModel->find($id);
        if ($resp = $this->requirePersonnelManager($existing['position'] ?? null, $this->request->getPost('position'))) return $resp;

        $empId = trim((string) $this->request->getPost('emp_id'));

        if ($empId !== '' && $this->personnelModel->isEmpIdTaken($empId, $id)) {
            return redirect()->back()->withInput()->with('error', 'Employee ID already exists.');
        }

        if (trim((string) $this->request->getPost('full_name')) === '') {
            return redirect()->back()->withInput()->with('error', 'Full name is required.');
        }
        $name = trim((string) $this->request->getPost('full_name'));
        try {
            $this->personnelModel->update($id, [
                'emp_id'        => $empId,
                'full_name'     => $name,
                'email'         => $this->request->getPost('email'),
                'contact_number' => $this->request->getPost('contact_number'),
                'department_id' => $this->request->getPost('department_id'),
                'position'      => $this->request->getPost('position'),
                'assigned_task' => $this->request->getPost('assigned_task'),
                'status'        => $this->request->getPost('status'),
            ]);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            return redirect()->back()->withInput()->with('error', 'Could not save personnel because the employee ID is already in use.');
        }
        $this->logActivity('Personnel', "Updated personnel {$name} (#{$id})");

        return $this->personnelReturn()->with('success', 'Personnel updated successfully.');
    }

    public function delete($id)
    {
        $existing = $this->personnelModel->find($id);
        if ($resp = $this->requirePersonnelManager($existing['position'] ?? null)) return $resp;

        $this->personnelModel->update($id, [
            'is_archived' => 1,
            'archived_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->personnelReturn()->with('success', 'Personnel archived.');
    }

    // POST /personnel/test-sms/{id} - sends one short test text to that person's saved number through the Android SMS gateway.
    public function testSms($id)
    {
        if (!session()->get('isLoggedIn')) return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'message' => 'Please sign in.']);
        if (strtolower((string) session()->get('role')) !== 'administrator') {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Only the administrator can send test texts.']);
        }
        $person = $this->personnelModel->find($id);
        if (!$person) return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'message' => 'Person not found.']);
        $result = (new \App\Libraries\SmsGateway())->deliver((int) $person['id'], $person['full_name'], $person['contact_number'] ?? '', 'FU-UBRA test message for ' . $person['full_name'] . '. The SMS alert is working. You can ignore this.', 'Test');
        $this->logActivity('Personnel', 'Test SMS (' . $result['mode'] . ') for ' . $person['full_name']);
        return $this->response->setJSON($result);
    }

    // POST /personnel/test-telegram/{id} - links any new "/start <employee id>" chats, then sends a test Telegram message.
    public function testTelegram($id)
    {
        if (!session()->get('isLoggedIn')) return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'message' => 'Please sign in.']);
        if (strtolower((string) session()->get('role')) !== 'administrator') {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Only the administrator can send test messages.']);
        }
        $person = $this->personnelModel->find($id);
        if (!$person) return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'message' => 'Person not found.']);

        $bot = new \App\Libraries\TelegramBot();
        if (!$bot->isConfigured()) return $this->response->setJSON(['ok' => false, 'message' => 'Telegram is not set up yet. Add telegram.botToken to the .env file.']);

        // Pick up everyone who has sent "/start <employee id>" to the bot since last time.
        $sync = $bot->collectLinks();
        if ($sync['ok']) {
            foreach ($sync['links'] as $empId => $chatId) {
                $p = $this->personnelModel->where('emp_id', $empId)->first();
                if ($p && ($p['telegram_chat_id'] ?? '') !== $chatId) {
                    $this->personnelModel->update($p['id'], ['telegram_chat_id' => $chatId]);
                    if ((int) $p['id'] === (int) $id) $person['telegram_chat_id'] = $chatId;
                }
            }
        }

        if (trim((string) ($person['telegram_chat_id'] ?? '')) === '') {
            $botName = $bot->username();
            return $this->response->setJSON(['ok' => false, 'message' => $person['full_name'] . ' has not linked Telegram yet. Ask them to open ' . ($botName !== '' ? 't.me/' . $botName : 'the FU-UBRA bot') . ' in Telegram, press Start, and send:  /start ' . $person['emp_id'] . '   Then press this button again.']);
        }

        $result = $bot->send($person['telegram_chat_id'], 'FU-UBRA test message for ' . $person['full_name'] . ". Telegram alerts are working. You can ignore this.");
        $this->logActivity('Personnel', ($result['ok'] ? 'Test Telegram sent to ' : 'Test Telegram failed for ') . $person['full_name']);
        return $this->response->setJSON($result);
    }
}
