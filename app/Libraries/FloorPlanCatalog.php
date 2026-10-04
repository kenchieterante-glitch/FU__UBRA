<?php

namespace App\Libraries;

use App\Models\FireExtinguisherModel;

/**
 * Which floors each campus building has, worked out from the files in public/Files/Floor Plans
 * (e.g. "EDUC_ThirdFloorPlan.png" = College of Education Building, 3rd Floor). Only the floor names are used —
 * the pictures themselves are not shown outside Safety and Security.
 */
class FloorPlanCatalog
{
    private const BUILDINGS = [
        'ADMINandARTS'                => 'Administration Building',
        'Agriculture Building'        => 'College of Agriculture and SIE',
        'Arts and Sciences'           => 'College of Art & Sciences Building',
        'Business and Administration' => 'College of Business Economics and Accountancy',
        'EDUC'                        => 'College of Education Building',
        'IT Building'                 => 'LG Sinco Computer Center Building',
        'Kennel Caf'                  => 'University Cafeteria, Bookstore, Sewing',
        'Law Building'                => 'College of Law Building',
        'Main Library'                => 'University Library',
        'Museum'                      => 'Museo de Vicente',
        'SSS'                         => 'Sofia Soller Sinco Hall',
    ];

    private const ORDER = ['Ground Floor' => 1, '2nd Floor' => 2, '3rd Floor' => 3, '4th Floor' => 4];

    /** @return array<string,string[]> campus building => floors (in order) */
    public static function floorsByBuilding(): array
    {
        $found = [];
        foreach (glob(FCPATH . 'Files/Floor Plans/*.{png,jpg,jpeg,PNG,JPG,JPEG}', GLOB_BRACE) ?: [] as $path) {
            $name = pathinfo($path, PATHINFO_FILENAME);
            $label = trim(explode('_', $name, 2)[0]);
            $campus = self::BUILDINGS[$label] ?? null;
            if (!$campus) continue;
            $lower = strtolower($name);
            $floor = str_contains($lower, 'second') ? '2nd Floor' : (str_contains($lower, 'third') ? '3rd Floor' : (str_contains($lower, 'fourth') ? '4th Floor' : 'Ground Floor'));
            $found[$campus][$floor] = true;
        }

        $out = [];
        foreach (FireExtinguisherModel::BUILDINGS as $b) {
            $floors = array_keys($found[$b] ?? []);
            usort($floors, fn($x, $y) => self::ORDER[$x] <=> self::ORDER[$y]);
            // Buildings without a floor plan on file still get the usual floors to choose from.
            $out[$b] = $floors ?: array_keys(self::ORDER);
        }

        return $out;
    }
}
