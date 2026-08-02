<?php

namespace App\Controllers\Api;

use App\Models\BorrowModel;
use App\Models\PersonnelModel;
use App\Models\ToolsModel;

class ToolsController extends BaseApiController
{
    protected ToolsModel $toolsModel;
    protected BorrowModel $borrowModel;
    protected PersonnelModel $personnelModel;

    public function __construct()
    {
        $this->toolsModel     = new ToolsModel();
        $this->borrowModel    = new BorrowModel();
        $this->personnelModel = new PersonnelModel();
    }

    public function index()
    {
        return $this->ok([
            'tools'      => $this->toolsModel->getAllWithDetails(),
            'personnel'  => $this->personnelModel->findAll(),
            'stats'      => [
                'available'   => (clone $this->toolsModel)->where('availability', 'Available')->where('is_archived', 0)->countAllResults(false),
                'borrowed'    => (clone $this->toolsModel)->where('availability', 'Borrowed')->where('is_archived', 0)->countAllResults(false),
                'maintenance' => (clone $this->toolsModel)->where('condition_status', 'Poor')->where('is_archived', 0)->countAllResults(false),
                'disposal'    => (clone $this->toolsModel)->where('availability', 'Disposal')->where('is_archived', 0)->countAllResults(false),
            ],
        ]);
    }

    public function add()
    {
        $id = $this->toolsModel->insert([
            'asset_name'       => $this->request->getPost('asset_name'),
            'asset_code'       => $this->request->getPost('asset_code'),
            'category'         => $this->request->getPost('category'),
            'location'         => $this->request->getPost('location'),
            'custodian'        => $this->request->getPost('custodian_id') ?: $this->request->getPost('custodian'),
            'condition_status' => $this->request->getPost('condition_status') ?: 'Excellent',
            'availability'     => 'Available',
            'last_activity_at' => date('Y-m-d H:i:s'),
        ], true);

        return $this->ok(['id' => $id], 201);
    }

    public function edit($id = null)
    {
        $this->toolsModel->update($id, [
            'asset_name'       => $this->request->getPost('asset_name'),
            'asset_code'       => $this->request->getPost('asset_code'),
            'category'         => $this->request->getPost('category'),
            'location'         => $this->request->getPost('location'),
            'custodian'        => $this->request->getPost('custodian_id') ?: $this->request->getPost('custodian'),
            'condition_status' => $this->request->getPost('condition_status'),
        ]);

        return $this->ok();
    }

    public function delete($id = null)
    {
        if ($resp = $this->requireAdminOrFail()) return $resp;

        $this->toolsModel->update($id, ['is_archived' => 1, 'archived_at' => date('Y-m-d H:i:s')]);
        return $this->ok();
    }

    public function borrow($toolId = null)
    {
        $tool = $this->toolsModel->find($toolId);
        if (!$tool || $tool['availability'] !== 'Available') {
            return $this->fail('This tool is not available to borrow.', 409);
        }

        $this->borrowModel->insert([
            'tool_id'              => $toolId,
            'borrower'             => $this->request->getPost('borrower_id') ?: $this->request->getPost('borrower'),
            'department'           => $this->request->getPost('department'),
            'borrowed_date'        => date('Y-m-d'),
            'expected_return'      => $this->request->getPost('expected_return'),
            'condition_on_borrow'  => $this->request->getPost('condition_on_borrow') ?: 'Excellent',
            'status'               => 'Borrowed',
            'created_at'           => date('Y-m-d H:i:s'),
            'last_activity_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->toolsModel->update($toolId, ['availability' => 'Borrowed', 'last_activity_at' => date('Y-m-d H:i:s')]);

        return $this->ok();
    }

    public function returnTool($toolId = null)
    {
        $conditionOnReturn = $this->request->getPost('condition_on_return') ?: 'Excellent';

        $borrow = $this->borrowModel->where('tool_id', $toolId)->where('status', 'Borrowed')->orderBy('id', 'DESC')->first();
        if ($borrow) {
            $this->borrowModel->update($borrow['id'], [
                'actual_return'       => date('Y-m-d'),
                'status'              => 'Returned',
                'condition_on_return' => $conditionOnReturn,
                'remarks'             => $this->request->getPost('remarks'),
                'last_activity_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        $this->toolsModel->update($toolId, [
            'availability'      => 'Available',
            'condition_status'  => $conditionOnReturn,
            'last_activity_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->ok();
    }
}
