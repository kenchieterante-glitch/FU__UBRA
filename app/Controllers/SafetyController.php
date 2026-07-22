<?php

namespace App\Controllers;

class SafetyController extends BaseController
{
    protected $session;

    public function __construct()
    {
        $this->session = \Config\Services::session();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/auth/login');

        return view('safety/index', [
            'title' => 'Safety Maintenance & Janitorial Monitoring',
            'openModule' => 'safety',
        ]);
    }
}