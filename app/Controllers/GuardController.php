
<?php

namespace App\Controllers;

use App\Models\TravelModel;

class GuardController extends BaseController
{
    protected $travelModel;
    protected $session;

    public function __construct()
    {
        $this->travelModel = new TravelModel();
        $this->session = \Config\Services::session();
    }

    public function index()
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');

        // Get today's approved trips, and all active trips
        $trips = $this->travelModel->getAllWithDetails();
        
        $data = [
            'title' => 'Guard Dashboard',
            'trips' => $trips,
        ];

        return view('guard/index', $data);
    }

    public function check($id)
    {
        if (!$this->session->get('isLoggedIn')) return redirect()->to('/login');
        $trip = $this->travelModel->getTripWithDetails($id);
        if (!$trip) {
            return redirect()->to('/guard')->with('error', 'Trip not found.');
        }
        $data = [
            'title' => 'Check Trip Ticket',
            'trip' => $trip,
        ];
        return view('guard/check', $data);
    }
}
