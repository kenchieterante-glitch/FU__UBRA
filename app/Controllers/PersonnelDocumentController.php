<?php

namespace App\Controllers;

use App\Models\PersonnelDocumentModel;
use App\Models\PersonnelModel;

class PersonnelDocumentController extends BaseController
{
    protected $documentModel;
    protected $personnelModel;
    protected $session;

    public function __construct()
    {
        $this->documentModel  = new PersonnelDocumentModel();
        $this->personnelModel = new PersonnelModel();
        $this->session        = \Config\Services::session();
    }

    public function add()
    {
        if ($resp = $this->requireJobOrderManager()) return $resp;

        $personnelId = (int) $this->request->getPost('personnel_id');
        $personnel = $this->personnelModel->find($personnelId);
        if (!$personnel) {
            return redirect()->back()->with('error', 'Personnel record not found.');
        }

        $issueDate = $this->request->getPost('issue_date');
        $expiryDate = $this->request->getPost('expiration_date');
        if ($issueDate && $expiryDate && $expiryDate < $issueDate) {
            return redirect()->back()->with('error', 'Expiration date cannot be before issue date.');
        }

        $this->documentModel->insert([
            'personnel_id'         => $personnelId,
            'document_type_id'     => (int) $this->request->getPost('document_type_id'),
            'document_number'      => $this->request->getPost('document_number'),
            'issue_date'           => $issueDate ?: null,
            'expiration_date'      => $expiryDate ?: null,
            'verification_status'  => 'PENDING',
            'remarks'              => $this->request->getPost('remarks'),
            'uploaded_at'          => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity('Personnel Monitoring', "Added document for {$personnel['full_name']}");

        return redirect()->to('/personnel/view/' . $personnelId)->with('success', 'Document added successfully.');
    }

    public function verify($id)
    {
        if ($resp = $this->requireJobOrderManager()) return $resp;

        $document = $this->documentModel->find($id);
        if (!$document) {
            return redirect()->back()->with('error', 'Document not found.');
        }

        $this->documentModel->update($id, [
            'verification_status' => 'VERIFIED',
            'verified_at'          => date('Y-m-d H:i:s'),
            'remarks'              => $this->request->getPost('remarks') ?: $document['remarks'],
        ]);

        $this->logActivity('Personnel Monitoring', "Verified document #{$id}");

        return redirect()->to('/personnel/view/' . $document['personnel_id'])->with('success', 'Document verified.');
    }

    public function reject($id)
    {
        if ($resp = $this->requireJobOrderManager()) return $resp;

        $document = $this->documentModel->find($id);
        if (!$document) {
            return redirect()->back()->with('error', 'Document not found.');
        }

        $reason = trim((string) $this->request->getPost('remarks'));
        if ($reason === '') {
            return redirect()->back()->with('error', 'A rejection reason is required.');
        }

        $this->documentModel->update($id, [
            'verification_status' => 'REJECTED',
            'verified_at'          => date('Y-m-d H:i:s'),
            'remarks'              => $reason,
        ]);

        $this->logActivity('Personnel Monitoring', "Rejected document #{$id}: {$reason}");

        return redirect()->to('/personnel/view/' . $document['personnel_id'])->with('success', 'Document rejected.');
    }
}
