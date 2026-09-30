<?php
namespace App\Models;
use CodeIgniter\Model;

class FireExtinguisherModel extends Model
{
    protected $table         = 'fire_extinguishers';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'unit_id', 'type', 'location', 'floor', 'department_id', 'weight_kg', 'last_inspection', 'next_due',
        'status', 'year_acquired', 'inspector', 'assigned_guard', 'notes',
    ];

    public function getBuildingCounts(): array
    {
        return $this->select('location, COUNT(*) AS count')
                    ->groupBy('location')
                    ->findAll();
    }

    // Every real building on the campus map (safety/index.php's SVG
    // `buildings` array) that should have at least one fire extinguisher —
    // excludes map features that aren't actual buildings (gates, the water
    // pump, the flag pole, monuments, etc.), which don't need one. Kept
    // here (not just in the map's own JS) so PHP can report real building
    // coverage as its own number, not just a raw unit count.
    public const BUILDINGS = [
        'University Cafeteria, Bookstore, Sewing',
        'College of Law Building',
        'College of Agriculture and SIE',
        'Museo de Vicente',
        'University Library',
        'Executive House',
        'Guest House',
        'HRM Kitchen',
        'College of Education Building',
        'LG Sinco Computer Center Building',
        'Sofia Soller Sinco Hall',
        'College of Art & Sciences Building',
        'Art & Science Laboratories / Audio Visual Rooms',
        'College of Business Economics and Accountancy',
        'College of Nursing',
        'Administration Building',
        "Registrar's Office",
        'Business and Finance Office',
        'Old College of Industrial Engineering and Technology',
        'Bunk House',
        'Animation Lab / ROTC Office',
    ];

    // Single source of truth for "how many of the campus's real buildings
    // actually have a fire extinguisher installed" — used by the main
    // Dashboard, Security Dashboard, and the mobile app's equivalent
    // summary, so none of them can disagree with each other.
    public function getBuildingCoverage(): array
    {
        $coveredLocations = array_unique(array_column($this->getBuildingCounts(), 'location'));
        $covered = count(array_intersect(self::BUILDINGS, $coveredLocations));
        $total   = count(self::BUILDINGS);

        return ['covered' => $covered, 'total' => $total];
    }

    // Exact same per-building floor list the Safety map's floor tabs
    // already use (safety/index.php's BUILDING_FLOORS/DEFAULT_FLOORS) —
    // mirrored here so PHP can check coverage at the same granularity the
    // map already navigates by. A building not listed falls back to the
    // same 3-floor default the map itself assumes for it.
    public const DEFAULT_FLOORS = ['Ground Floor', '2nd Floor', '3rd Floor'];
    public const BUILDING_FLOORS = [
        "Registrar's Office" => ['Ground Floor'],
        'Business and Finance Office' => ['Ground Floor'],
        'College of Art & Sciences Building' => ['Ground Floor', '2nd Floor', '3rd Floor', '4th Floor'],
        'College of Education Building' => ['Ground Floor', '2nd Floor', '3rd Floor', '4th Floor'],
        'College of Nursing' => ['Ground Floor', '2nd Floor', '3rd Floor', '4th Floor', '5th Floor', '6th Floor', '7th Floor'],
        'LG Sinco Computer Center Building' => ['Ground Floor', '2nd Floor'],
        'College of Agriculture and SIE' => ['Ground Floor', '2nd Floor', '3rd Floor'],
        'College of Law Building' => ['Ground Floor', '1st Floor'],
        'University Library' => ['Ground Floor', '2nd Floor', '3rd Floor'],
        'College of Business Economics and Accountancy' => ['Ground Floor', '2nd Floor', '3rd Floor', '4th Floor'],
    ];

    // Single source of truth for "how many of the campus's building FLOORS
    // (not just buildings as a whole) actually have a fire extinguisher
    // installed on them" — a building can already count as "covered" above
    // with just one unit on its Ground Floor while its upper floors have
    // none, which this catches. Used the same places getBuildingCoverage()
    // is (Dashboard, Security Dashboard, mobile app).
    public function getFloorCoverage(): array
    {
        $rows = $this->select('location, floor')->findAll();
        $coveredPairs = [];
        foreach ($rows as $r) {
            $coveredPairs[$r['location'] . '|' . ($r['floor'] ?: 'Ground Floor')] = true;
        }

        $covered = 0;
        $total   = 0;
        $missing = [];
        foreach (self::BUILDINGS as $building) {
            $floors = self::BUILDING_FLOORS[$building] ?? self::DEFAULT_FLOORS;
            foreach ($floors as $floor) {
                $total++;
                if (isset($coveredPairs[$building . '|' . $floor])) {
                    $covered++;
                } else {
                    $missing[] = ['building' => $building, 'floor' => $floor];
                }
            }
        }

        return ['covered' => $covered, 'total' => $total, 'missing' => $missing];
    }
}
