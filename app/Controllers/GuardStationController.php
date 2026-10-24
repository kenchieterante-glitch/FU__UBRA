<?php

namespace App\Controllers;

// The guard's own desk (desktop): lend / receive keys with a wired barcode scanner or by hand,
// and verify vehicles leaving and returning. It reuses the Safety & Security guard logic, nothing is duplicated.
class GuardStationController extends SecurityDeptController
{
    public function station()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');
        if (!$this->canOperateKeys() && !$this->isAdmin()) return redirect()->to($this->roleLandingUrl());

        return view('guard/station', $this->guardData() + [
            'title'   => 'Guard Station',
            'pageCss' => 'safety.css',
        ]);
    }

    public function scannerTest()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        return view('guard/scanner_test', ['title' => 'Scanner Test', 'pageCss' => 'safety.css']);
    }

    public function deskLookup($empId = '')
    {
        return $this->guardLookup($empId);
    }

    public function deskBorrow()
    {
        return $this->guardBorrow();
    }

    public function deskReturn()
    {
        return $this->guardReturn();
    }

    public function deskBarcodesPdf()
    {
        return $this->barcodesPdf();
    }

    public function deskBarcodes()
    {
        return $this->barcodes();
    }
}
