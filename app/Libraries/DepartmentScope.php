<?php

namespace App\Libraries;

/**
 * Which Information Hub / summary modules each department account may see.
 * null = no limit (Administrator and any other role).
 */
class DepartmentScope
{
    // module label => slug used in the filters and exports
    public const SLUGS = [
        'Tools'      => 'tools',
        'Vehicle'    => 'vehicle',
        'Motor Pool' => 'motor-pool',
        'Safety'     => 'safety',
        'Janitorial' => 'janitorial',
        'Personnel'  => 'personnel',
        'Sports'     => 'sports',
        'Security'   => 'security',
        'Facilities' => 'facilities',
    ];

    public static function modulesForRole(string $role): ?array
    {
        return match (strtolower($role)) {
            'janitorial' => ['Janitorial'],
            'assets'     => ['Vehicle', 'Motor Pool'],
            'sports'     => ['Sports'],
            'security'   => ['Security'],
            'facilities' => ['Facilities', 'Janitorial'],
            default      => null,
        };
    }

    /** Dropdown choices for a "which module?" picker: [value, label] pairs. */
    public static function options(string $role): array
    {
        $scope = self::modulesForRole($role);
        $labels = $scope ?? array_keys(self::SLUGS);
        $opts = array_map(fn($m) => ['value' => self::SLUGS[$m], 'label' => $m], $labels);
        if ($scope === null) {
            array_unshift($opts, ['value' => '', 'label' => 'All Modules']);
        } elseif (count($scope) > 1) {
            array_unshift($opts, ['value' => '', 'label' => 'All my modules']);
        }

        return $opts;
    }
}
