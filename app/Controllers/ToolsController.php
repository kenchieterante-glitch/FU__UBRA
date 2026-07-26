<?php

namespace App\Controllers;

use App\Models\ToolsModel;
use App\Models\BorrowModel;
use App\Models\PersonnelModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class ToolsController extends BaseController
{
    protected $toolsModel;
    protected $borrowModel;
    protected $personnelModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->toolsModel = new ToolsModel();
        $this->borrowModel = new BorrowModel();
        $this->personnelModel = new PersonnelModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $tools = $this->toolsModel->getAllWithDetails();

        $data = [
            'title'           => 'Tools Equipment Management',
            'pageCss'         => 'tools.css',
            'tools'           => $tools,
            'total_tools'     => count($tools),
            'available_tools' => $this->toolsModel->where('availability', 'Available')->where('is_archived', 0)->countAllResults(),
            'borrowed_tools'  => $this->toolsModel->where('availability', 'Borrowed')->where('is_archived', 0)->countAllResults(),
            'maintenance_tools' => $this->toolsModel->where('condition_status', 'Poor')->where('is_archived', 0)->countAllResults(),
            'personnel'       => $this->personnelModel->findAll(),
        ];

        return view('tools/index', $data);
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
        $this->toolsModel->update($id, [
            'is_archived' => 1,
            'archived_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/tools')->with('success', 'Asset archived.');
    }

    // --- BORROW ---
    public function borrow($toolId)
    {
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
