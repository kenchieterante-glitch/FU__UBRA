<?php
namespace App\Models;
use CodeIgniter\Model;

class FacilityChecklistModel extends Model
{
    protected $table         = 'facility_checklists';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'inspector', 'building_area', 'inspection_date', 'inspection_type',
        'overall_condition', 'summary_findings', 'corrective_action_plan',
        'reviewed_by', 'reviewed_date', 'approved_by', 'is_archived',
    ];

    // The form's fixed 4-section, 21-item layout — never changes, so it's
    // seeded onto every new checklist rather than left for staff to build
    // from scratch each time (same idea as AirconChecklistItemModel's
    // seedDefaultTasks()).
    public const SECTIONS = [
        'General & Building Integrity' => [
            '1.1' => 'Exterior/Grounds: Are sidewalks, ramps, and stairs in good condition, free of cracks and trip hazards? Is lighting adequate?',
            '1.2' => 'Interior Floors: Are floors, carpets, and stair tread free from damage and tripping hazards?',
            '1.3' => 'Walls & Ceilings: Are walls and ceilings free of water damage, cracks, or missing tiles?',
            '1.4' => 'Lighting: Is all lighting operational and providing sufficient illumination?',
            '1.5' => 'Exits & Egress: Are all exit signs illuminated and exit pathways clear of obstructions? Do doors open and close properly?',
            '1.6' => 'ADA Accessibility: Are ramps, door hardware, and restrooms compliant with accessibility standards?',
            '1.7' => 'Housekeeping: Is the overall area clean, organized, and free of clutter?',
        ],
        'Fire & Emergency Safety' => [
            '2.1' => 'Fire Extinguishers: Are fire extinguishers in their designated spots, easily accessible, and within their inspection dates?',
            '2.2' => 'Emergency Plans: Are emergency evacuation plans and contact numbers posted and visible?',
            '2.3' => 'First Aid Kits: Is a fully stocked and accessible first aid kit available in the area?',
            '2.4' => 'Fire Doors: Are fire doors kept closed and not propped open?',
        ],
        'Academic & Laboratory Specifics' => [
            '3.1' => 'Chemical Storage: Are chemicals properly labeled and stored in designated cabinets, segregated by hazard class?',
            '3.2' => 'Lab Safety: Are emergency eyewash stations and safety showers present and checked weekly?',
            '3.3' => 'Hazard Communication: Are Safety Data Sheets (SDS) for all chemicals readily accessible?',
            '3.4' => 'Equipment Safety: Are machine guards in place on all equipment, and is damaged equipment tagged "Out of Service"?',
            '3.5' => 'Personal Protective Equipment (PPE): Is required PPE available and in good condition for all personnel?',
            '3.6' => 'Wastes: Are hazardous wastes properly labeled and stored for disposal?',
        ],
        'Electrical & Technology Safety' => [
            '4.1' => 'Electrical Cords: Are extension cords used only for temporary purposes and not as permanent wiring?',
            '4.2' => 'Power Strips: Are power strips UL listed and not "daisy-chained" together?',
            '4.3' => 'Electrical Panels: Is there a clear 3-foot clearance in front of all electrical panels?',
            '4.4' => 'Outlets & Switches: Are all electrical outlets and switches in good condition with no exposed wiring?',
        ],
    ];

    public function getAllWithItems(): array
    {
        $checklists = $this->where('is_archived', 0)->orderBy('id', 'DESC')->findAll();
        $itemModel = new FacilityChecklistItemModel();
        foreach ($checklists as &$c) {
            $c['items'] = $itemModel->where('checklist_id', $c['id'])->orderBy('id', 'ASC')->findAll();
        }
        return $checklists;
    }

    public function createWithItems(array $header): int
    {
        $id = $this->insert($header);
        $itemModel = new FacilityChecklistItemModel();
        foreach (self::SECTIONS as $section => $items) {
            foreach ($items as $code => $label) {
                $itemModel->insert([
                    'checklist_id' => $id,
                    'section'      => $section,
                    'item_code'    => $code,
                    'item_label'   => $label,
                ]);
            }
        }
        return $id;
    }
}
