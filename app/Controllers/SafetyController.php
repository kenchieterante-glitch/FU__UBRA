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
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        return view('safety/index', [
            'title' => 'Safety Maintenance & Janitorial Monitoring',
            'openModule' => 'safety',
        ]);
    }

    public function guardDashboard()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        return view('safety/guard_dashboard', [
            'title' => 'Guard Dashboard',
            'openModule' => 'safety',
        ]);
    }

    public function keylogs()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        return view('safety/keylogs', [
            'title' => 'Keylogs',
            'openModule' => 'safety',
        ]);
    }
}