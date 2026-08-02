<?php

namespace App\Controllers\Api;

use App\Libraries\ApiAuth;
use App\Models\KeyBorrowLogModel;

class GuardController extends BaseApiController
{
    protected KeyBorrowLogModel $keyLogModel;

    public function __construct()
    {
        $this->keyLogModel = new KeyBorrowLogModel();
    }

    private function requireGuardOrFail()
    {
        $role = strtolower((string) (ApiAuth::user()['role'] ?? ''));
        if (!in_array($role, ['guard', 'security', 'administrator'], true)) {
            return $this->fail('This dashboard is for authorized guards only.', 403);
        }
        return null;
    }

    // GET /api/guard/keylogs
    public function keylogs()
    {
        if ($resp = $this->requireGuardOrFail()) return $resp;

        return $this->ok(['key_logs' => $this->keyLogModel->getAllWithTrip()]);
    }

    // POST /api/guard/keyborrow — scan borrow
    public function keyBorrow()
    {
        if ($resp = $this->requireGuardOrFail()) return $resp;

        $borrowerId = trim((string) $this->request->getPost('borrower_id'));
        $fullName   = trim((string) $this->request->getPost('full_name'));
        $keyItem    = trim((string) $this->request->getPost('key_item'));

        if ($borrowerId === '' || $fullName === '' || $keyItem === '') {
            return $this->fail('Borrower ID, name, and key/item are required.', 422);
        }

        $count = $this->keyLogModel->countAllResults(false);

        $id = $this->keyLogModel->insert([
            'log_number'     => sprintf('KL-%03d', $count + 1),
            'borrower_id'    => $borrowerId,
            'trip_ticket_id' => $this->request->getPost('trip_ticket_id') ?: null,
            'full_name'      => $fullName,
            'department'     => $this->request->getPost('department') ?: '',
            'key_item'       => $keyItem,
            'scan_in'        => date('Y-m-d H:i:s'),
            'status'         => 'Active',
            'guard_on_duty'  => ApiAuth::user()['full_name'] ?? null,
        ], true);

        return $this->ok(['id' => $id], 201);
    }

    // POST /api/guard/keyreturn/(:num) — scan return
    public function keyReturn($id = null)
    {
        if ($resp = $this->requireGuardOrFail()) return $resp;

        $log = $this->keyLogModel->find($id);
        if (!$log) {
            return $this->fail('Key log not found.', 404);
        }

        $this->keyLogModel->update($id, [
            'scan_out' => date('Y-m-d H:i:s'),
            'status'   => 'Returned',
        ]);

        return $this->ok();
    }
}
