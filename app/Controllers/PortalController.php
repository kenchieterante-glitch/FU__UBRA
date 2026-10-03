<?php

namespace App\Controllers;

use App\Models\AirconUnitModel;
use App\Models\ConsumableInventoryModel;
use App\Models\FireExtinguisherModel;
use App\Models\JanitorialAssignmentModel;
use App\Models\KeyBorrowLogModel;
use App\Models\ToolsModel;
use App\Models\TravelModel;
use App\Models\VehicleModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class PortalController extends BaseController
{
    private const PORTALS = [
        'facilities' => [
            'box'         => '01',
            'name'        => 'Facilities Administration & General Services',
            'short'       => 'Facilities & General Services',
            'description' => 'Janitorial coverage, consumable supplies, and campus aircon condition.',
            'covers'      => 'Janitorial, Consumables, Aircon',
        ],
        'safety' => [
            'box'         => '02',
            'name'        => 'Safety and Security Department',
            'short'       => 'Safety & Security',
            'description' => 'Fire extinguisher coverage and inspections, key control, and trip gate activity.',
            'covers'      => 'Fire Safety, Keys, Trip Gate',
        ],
        'asset' => [
            'box'         => '03',
            'name'        => 'Asset Acquisition and Monitoring Department',
            'short'       => 'Asset Acquisition & Monitoring',
            'description' => 'Tools and equipment inventory, vehicle fleet status, and consumable stock levels.',
            'covers'      => 'Tools, Vehicles, Stock',
        ],
    ];

    public function index()
    {
        return view('portals/index', [
            'title'   => 'Department Portals',
            'pageCss' => 'portals.css',
            'portals' => self::PORTALS,
        ]);
    }

    public function show(string $key)
    {
        if (!isset(self::PORTALS[$key])) throw PageNotFoundException::forPageNotFound();

        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('login?portal=' . $key));
        }

        if ($key === 'facilities') {
            return redirect()->to(base_url('facilities'));
        }

        $portal = self::PORTALS[$key];

        return view('portals/department', [
            'title'   => $portal['short'],
            'pageCss' => 'portals.css',
            'portal'  => $portal + ['key' => $key],
            'stats'   => $this->statsFor($key),
            'links'   => $this->linksFor($key),
        ]);
    }

    private function statsFor(string $key): array
    {
        $today = date('Y-m-d');

        if ($key === 'facilities') {
            $janitorial = new JanitorialAssignmentModel();
            $consumables = new ConsumableInventoryModel();
            $aircon = new AirconUnitModel();

            return [
                ['label' => 'Active Janitorial Shifts', 'value' => $janitorial->where('status', 'Active')->countAllResults(), 'icon' => 'bi-brush', 'tone' => 'maroon'],
                ['label' => 'Consumables Low or Out', 'value' => $consumables->where('current_stock <= reorder_threshold', null, false)->countAllResults(), 'icon' => 'bi-box-seam-fill', 'tone' => 'amber'],
                ['label' => 'Aircon Units Not Working', 'value' => $aircon->where('condition_status', 'Not Working')->countAllResults(), 'icon' => 'bi-snow2', 'tone' => 'red'],
                ['label' => 'Aircon Units Registered', 'value' => $aircon->countAllResults(), 'icon' => 'bi-thermometer-half', 'tone' => 'blue'],
            ];
        }

        if ($key === 'safety') {
            $fire = new FireExtinguisherModel();
            $keys = new KeyBorrowLogModel();
            $travel = new TravelModel();
            $buildings = $fire->getBuildingCoverage();
            $floors = $fire->getFloorCoverage();

            return [
                ['label' => 'Building Coverage', 'value' => $buildings['covered'] . ' / ' . $buildings['total'], 'icon' => 'bi-building-fill-check', 'tone' => 'maroon'],
                ['label' => 'Floor Coverage', 'value' => $floors['covered'] . ' / ' . $floors['total'], 'icon' => 'bi-layers-fill', 'tone' => 'green'],
                ['label' => 'Overdue Extinguishers', 'value' => $fire->where('next_due <', $today)->countAllResults(), 'icon' => 'bi-exclamation-triangle-fill', 'tone' => 'red'],
                ['label' => 'Keys Currently Out', 'value' => $keys->where('status', 'Active')->countAllResults(), 'icon' => 'bi-key-fill', 'tone' => 'amber'],
                ['label' => 'Trips In Transit', 'value' => $travel->where('status', 'In Transit')->where('is_archived', 0)->countAllResults(), 'icon' => 'bi-truck', 'tone' => 'blue'],
            ];
        }

        $tools = new ToolsModel();
        $vehicles = new VehicleModel();
        $consumables = new ConsumableInventoryModel();

        return [
            ['label' => 'Total Tools & Equipment', 'value' => $tools->where('is_archived', 0)->countAllResults(), 'icon' => 'bi-tools', 'tone' => 'maroon'],
            ['label' => 'Currently Borrowed', 'value' => $tools->where('availability', 'Borrowed')->where('is_archived', 0)->countAllResults(), 'icon' => 'bi-hand-index-thumb-fill', 'tone' => 'amber'],
            ['label' => 'Vehicles in Fleet', 'value' => $vehicles->countAllResults(), 'icon' => 'bi-truck-front-fill', 'tone' => 'blue'],
            ['label' => 'Consumables Low or Out', 'value' => $consumables->where('current_stock <= reorder_threshold', null, false)->countAllResults(), 'icon' => 'bi-box-seam-fill', 'tone' => 'red'],
        ];
    }

    private function linksFor(string $key): array
    {
        return match ($key) {
            'facilities' => [
                ['label' => 'Janitorial Monitoring', 'url' => 'janitorial', 'icon' => 'bi-brush'],
                ['label' => 'Maintenance', 'url' => 'safety', 'icon' => 'bi-wrench-adjustable'],
                ['label' => 'Aircon Inspection Log', 'url' => 'maintenance-forms/aircon-log', 'icon' => 'bi-snow2'],
            ],
            'safety' => [
                ['label' => 'Maintenance', 'url' => 'safety', 'icon' => 'bi-wrench-adjustable'],
                ['label' => 'Guard Dashboard', 'url' => 'safety/guard-dashboard', 'icon' => 'bi-shield-check'],
                ['label' => 'Trip Ticket', 'url' => 'travel', 'icon' => 'bi-ticket-perforated'],
            ],
            default => [
                ['label' => 'Tools Management', 'url' => 'tools', 'icon' => 'bi-boxes'],
                ['label' => 'Vehicle Management', 'url' => 'vehicles', 'icon' => 'bi-truck'],
                ['label' => 'GPS Tracker', 'url' => 'gps', 'icon' => 'bi-geo-alt-fill'],
            ],
        };
    }
}
