<?php

namespace App\Controllers;

use App\Models\ToolsModel;
use App\Models\BorrowModel;
use App\Models\PersonnelModel;
use App\Models\ToolsRefillLogModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class ToolsController extends BaseController
{
    protected $toolsModel;
    protected $borrowModel;
    protected $personnelModel;
    protected $refillLogModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->toolsModel = new ToolsModel();
        $this->borrowModel = new BorrowModel();
        $this->personnelModel = new PersonnelModel();
        $this->refillLogModel = new ToolsRefillLogModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = array_merge([
            'title'   => 'Tools Equipment Management',
            'pageCss' => 'tools.css',
            'tools'   => $this->toolsModel->getAllWithDetails(),
            'personnel' => $this->personnelModel->findAll(),
        ], $this->getStatCounts());

        return view('tools/index', $data);
    }

    public function powerTools()
    {
        return $this->categoryView('Power Tools');
    }

    public function consumable()
    {
        return $this->categoryView('Consumable');
    }

    public function sportsEquipment()
    {
        return $this->categoryView('Sports Equipment');
    }

    // Borrowing ledger — one row per borrow_records transaction (active and
    // returned), not per tool, so it's a history view rather than a live
    // inventory filter like the category tabs above. 'Overdue' is derived
    // on read from expected_return vs today, same reasoning as
    // MonitoringStatus::derive() elsewhere in the app: no cron exists here,
    // so compute-on-read is what keeps it correct without a background job.
    public function borrowing()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $today = date('Y-m-d');
        $records = array_map(function ($r) use ($today) {
            $r['computed_status'] = ($r['status'] === 'Borrowed' && !empty($r['expected_return']) && $r['expected_return'] < $today)
                ? 'Overdue'
                : $r['status'];
            return $r;
        }, $this->borrowModel->getAllWithDetails());

        return view('tools/borrowing', [
            'title'   => 'Borrowing',
            'pageCss' => 'borrowing.css',
            'records' => $records,
        ]);
    }

    private function categoryView(string $category)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = array_merge([
            'title'   => $category,
            'pageCss' => 'tools.css',
            'tools'   => $this->toolsModel->getByCategory($category),
            'personnel' => $this->personnelModel->findAll(),
        ], $this->getStatCounts());

        if ($category === 'Consumable') {
            $data['refill_log_json'] = $this->jsonForScript(array_map(fn($l) => [
                'item' => $l['asset_name'],
                'qty'  => (float) $l['quantity_added'],
                'by'   => $l['performed_by'],
                'at'   => $l['performed_at'],
            ], $this->refillLogModel->getRecent(50)));
        }

        return view('tools/index', $data);
    }

    private function getStatCounts(): array
    {
        return [
            'total_tools'       => $this->toolsModel->where('is_archived', 0)->countAllResults(),
            'available_tools'   => $this->toolsModel->where('availability', 'Available')->where('is_archived', 0)->countAllResults(),
            'borrowed_tools'    => $this->toolsModel->where('availability', 'Borrowed')->where('is_archived', 0)->countAllResults(),
            'maintenance_tools' => $this->toolsModel->where('availability', 'Maintenance')->where('is_archived', 0)->countAllResults(),
            'disposal_tools'    => $this->toolsModel->where('availability', 'Disposal')->where('is_archived', 0)->countAllResults(),
            'consumable_tools'  => $this->toolsModel->where('category', 'Consumable')->where('is_archived', 0)->countAllResults(),
        ];
    }

    public function add()
    {
        $custodianName = $this->request->getPost('custodian_id') ?? $this->request->getPost('custodian');

        $this->toolsModel->insert([
            'asset_name'       => $this->request->getPost('asset_name'),
            'asset_code'       => $this->request->getPost('asset_code'),
            'category'         => $this->request->getPost('category'),
            'location'         => $this->request->getPost('location'),
            'custodian'        => $custodianName,
            'condition_status' => $this->request->getPost('condition_status') ?? 'Excellent',
            'availability'     => 'Available',
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/tools')->with('success', 'Asset added successfully.');
    }

    public function edit($id)
    {
        $custodianName = $this->request->getPost('custodian_id') ?? $this->request->getPost('custodian');

        $this->toolsModel->update($id, [
            'asset_name'       => $this->request->getPost('asset_name'),
            'asset_code'       => $this->request->getPost('asset_code'),
            'category'         => $this->request->getPost('category'),
            'location'         => $this->request->getPost('location'),
            'custodian'        => $custodianName,
            'condition_status' => $this->request->getPost('condition_status'),
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/tools')->with('success', 'Asset updated successfully.');
    }

    public function delete($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $this->toolsModel->update($id, [
            'is_archived' => 1,
            'archived_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/tools')->with('success', 'Asset archived.');
    }

    // Consumable items don't get archived when they run low — they get refilled.
    public function refillStock($id)
    {
        $qty = (float) $this->request->getPost('quantity');
        $item = $this->toolsModel->find($id);
        if (!$item || $qty <= 0) {
            return redirect()->to('/tools/consumable')->with('error', 'Invalid refill quantity.');
        }

        $this->toolsModel->update($id, [
            'current_stock'     => (float) ($item['current_stock'] ?? 0) + $qty,
            'last_activity_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->refillLogModel->insert([
            'tool_id'        => $id,
            'asset_name'     => $item['asset_name'],
            'quantity_added' => $qty,
            'performed_by'   => (string) (session()->get('full_name') ?? session()->get('emp_id') ?? 'Unknown'),
            'performed_at'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/tools/consumable')->with('success', $item['asset_name'] . ' refilled by ' . $qty . '.');
    }

    // --- BORROW ---
    public function borrow($toolId)
    {
        // Server-side double-booking guard — a tool that's already borrowed
        // can't be borrowed again by someone else, regardless of what the UI shows.
        $tool = $this->toolsModel->find($toolId);
        if (!$tool || $tool['availability'] !== 'Available') {
            return redirect()->to('/tools')->with('error', 'That tool is not available to borrow right now.');
        }

        $borrowerName = $this->request->getPost('borrower_id') ?? $this->request->getPost('borrower');

        $this->borrowModel->insert([
            'tool_id'           => $toolId,
            'borrower'         => $borrowerName,
            'department'       => $this->request->getPost('department'),
            'borrowed_date'    => date('Y-m-d'),
            'expected_return'   => $this->request->getPost('expected_return'),
            'condition_on_borrow' => $this->request->getPost('condition_on_borrow') ?? 'Excellent',
            'status'           => 'Borrowed',
            'created_at'       => date('Y-m-d H:i:s'),
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        $this->toolsModel->update($toolId, [
            'availability' => 'Borrowed',
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/tools')->with('success', 'Tool marked as borrowed.');
    }

    // --- RETURN ---
    public function returnTool($toolId)
    {
        $borrowRecord = $this->borrowModel
            ->where('tool_id', $toolId)
            ->where('status', 'Borrowed')
            ->orderBy('id', 'DESC')
            ->first();

        if ($borrowRecord) {
            $this->borrowModel->update($borrowRecord['id'], [
                'actual_return'       => date('Y-m-d'),
                'status'              => 'Returned',
                'condition_on_return' => $this->request->getPost('condition_on_return') ?? 'Excellent',
                'remarks'             => $this->request->getPost('remarks'),
                'last_activity_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        $this->toolsModel->update($toolId, [
            'availability'       => 'Available',
            'condition_status'   => $this->request->getPost('condition_on_return') ?? 'Excellent',
            'last_activity_at'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/tools')->with('success', 'Asset marked as returned.');
    }
}
