<?php
namespace App\Models;
use CodeIgniter\Model;

class VehicleInspectionChecklistModel extends Model
{
    protected $table         = 'vehicle_inspection_checklists';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'vehicle_type', 'plate_no', 'odometer_reading', 'mechanic_inspector',
        'next_pm_schedule', 'inspection_date', 'is_archived',
    ];

    // Fixed 4-section, 33-item layout from F-FAC-PMP-VMI-004 — seeded onto
    // every new checklist, same reasoning as FacilityChecklistModel::SECTIONS.
    public const SECTIONS = [
        'Interior Inspection' => [
            'All seats- belts-condition, secure mounting, operation.',
            'Doors condition, hinges, latches, operation of doors windows.',
            'Flooring, headliner, side panels, vent louvers, operation and condition.',
            'Mirror-inside, right & left side mirror, condition and operation.',
            'Lights- interior, hi-lo beam, turn signals, hazard flasher, parking',
            'Lights- clearance, backup, brakes, license, instrument panel.',
            'Warning system, switches gauges, trouble lights, condition & operation',
            'Starter system- key operation',
            'Windshield wipers, w/s washer, w/s wiper speed-condition & operation.',
            'Comfort room- heater, defroster, air conditioning, blower speed.',
            'Fire extinguisher- charged, first aid kit- complete.',
        ],
        'Exterior Inspection' => [
            'Paint, dents, rust decals, bumpers-brackets, condition',
            'Tire - tread wear, wheel lugs, hubcaps, valves cores, condition.',
            'Access doors, fuelport & cap, engine covers & latch operation.',
            'Engine oil & filter- change and replace',
            'Inspect & lubricate- ball joints, steering & driveline, etc.',
        ],
        'Service and Operation Inspection' => [
            'Battery- terminals, water level, battery box & hold down condition',
            'Cooling system, hoses, fan shroud, belts, overflow tank, radiator.',
            'Air cleaner, crankcase, air filter, PVC filter.',
            'Belts, hoses, wiring condition.',
            'Brake operation check, brakes, pedal, parking brake.',
            'Brakes-rotor, pads, caliper, lining, drums.',
            'Hood, transmission fluid level, filter & liner, cooler',
            'Transmission shifts through all ranges, backup lights.',
            'Front wheel bearings, drive shaft, U joints',
            'Shocks, spring, lubricants linkages.',
            'Acceleration, steering, tracking, wheel balance.',
            'Chassis- check for leaks, condition of bushing, rear axle, differential fluid level.',
            'Engine tune-up- plugs, wires, carburetion.',
        ],
        'Accessories' => [
            'Two way radio-operational check.',
            'Spare tire, jack, tire tools.',
            'License plate, vehicle registration, operator manual.',
            'Air conditioning, system check, freon level, drier.',
        ],
    ];

    public function getAllWithItems(): array
    {
        $checklists = $this->where('is_archived', 0)->orderBy('id', 'DESC')->findAll();
        $itemModel = new VehicleInspectionItemModel();
        foreach ($checklists as &$c) {
            $c['items'] = $itemModel->where('checklist_id', $c['id'])->orderBy('id', 'ASC')->findAll();
        }
        return $checklists;
    }

    public function createWithItems(array $header): int
    {
        $id = $this->insert($header);
        $itemModel = new VehicleInspectionItemModel();
        foreach (self::SECTIONS as $section => $items) {
            foreach ($items as $label) {
                $itemModel->insert([
                    'checklist_id' => $id,
                    'section'      => $section,
                    'item_label'   => $label,
                ]);
            }
        }
        return $id;
    }
}
