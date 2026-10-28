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
        'Nursing'                     => 'College of Nursing',
        'SSS'                         => 'Sofia Soller Sinco Hall',
    ];

    private const ORDER = ['Ground Floor' => 1, '2nd Floor' => 2, '3rd Floor' => 3, '4th Floor' => 4, '5th Floor' => 5, '6th Floor' => 6, '7th Floor' => 7];

    /** "EDUC_ThirdFloorPlan" => "EDUC"; "Nursing Floor-3" => "Nursing". */
    public static function labelFromName(string $name): string
    {
        if (str_contains($name, '_')) return trim(explode('_', $name, 2)[0]);
        return trim(preg_replace('/[\s-]*floor.*$/i', '', $name));
    }

    /** Floor name from a file name: words (second, third, ...) or a floor number ("Nursing Floor-3"). Floor 1 = Ground Floor. */
    public static function floorFromName(string $name): string
    {
        $lower = strtolower($name);
        foreach (['second' => '2nd Floor', 'third' => '3rd Floor', 'fourth' => '4th Floor', 'fifth' => '5th Floor', 'sixth' => '6th Floor', 'seventh' => '7th Floor'] as $w => $f) {
            if (str_contains($lower, $w)) return $f;
        }
        if (preg_match('/floor\D*(\d)/', $lower, $m)) {
            return match ((int) $m[1]) { 2 => '2nd Floor', 3 => '3rd Floor', 4 => '4th Floor', 5 => '5th Floor', 6 => '6th Floor', 7 => '7th Floor', default => 'Ground Floor' };
        }
        return 'Ground Floor';
    }

    /** @return array<string,string[]> campus building => floors (in order) */
    public static function floorsByBuilding(): array
    {
        $found = [];
        foreach (glob(FCPATH . 'Files/Floor Plans/*.{png,jpg,jpeg,PNG,JPG,JPEG}', GLOB_BRACE) ?: [] as $path) {
            $name = pathinfo($path, PATHINFO_FILENAME);
            $label = self::labelFromName($name);
            $campus = self::BUILDINGS[$label] ?? null;
            if (!$campus) continue;
            $floor = self::floorFromName($name);
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
