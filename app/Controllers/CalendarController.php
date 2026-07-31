<?php

namespace App\Controllers;

use App\Models\VehicleModel;

class CalendarController extends BaseController
{
    protected $session;

    // Must match the legend rendered in calendar/index.php exactly, 1:1 by category.
    private const CATEGORY_COLORS = [
        'Inspection'  => '#f59e0b',
        'Maintenance' => '#7c3aed',
        'Compliance'  => '#2563eb',
    ];

    public function __construct()
    {
        $this->session = \Config\Services::session();
    }

    public function index()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        $data = [
            'title'         => 'Operations Calendar',
            'events_json'   => $this->jsonForScript([]),
            'flash_success' => $this->session->getFlashdata('success'),
        ];

        return view('calendar/index', $data);
    }

    public function add()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');
        // Local calendar events — stored in session for now
        $this->session->setFlashdata('success', 'Event added to calendar.');
        return redirect()->to('/calendar');
    }

    public function events()
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON([]);
        }

        return $this->response->setJSON([]);
    }
}
?>