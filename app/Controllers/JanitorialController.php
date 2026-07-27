<?php

namespace App\Controllers;

class JanitorialController extends BaseController
{
    protected $session;

    public function __construct()
    {
        $this->session = \Config\Services::session();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        return view('janitorial/index', [
            'title'   => 'Janitorial Monitoring',
            'pageCss' => 'safety.css',
        ]);
    }

    public function checklists()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        return view('janitorial/index', [
            'title'   => 'Janitorial Monitoring',
            'pageCss' => 'safety.css',
        ]);
    }
}
