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
        'Power Tools' => 'power-tools',
        'Vehicle'    => 'vehicle',
        'Motor Pool' => 'motor-pool',
        'Safety'     => 'safety',
        'Janitorial' => 'janitorial',
        'Personnel'  => 'personnel',
        'Sports'     => 'sports',
        'Security'   => 'security',
        'Facilities' => 'facilities',
        'Waste Watch' => 'waste-watch',
    ];

    // The three main categories and the types inside each one.
    public const CATEGORIES = [
        'Facilities'          => ['slug' => 'facilities', 'icon' => 'bi-building-gear', 'types' => ['Facilities' => 'Repairs & Aircon', 'Janitorial' => 'Janitorial', 'Waste Watch' => 'Waste Watch', 'Sports' => 'Sports', 'Personnel' => 'Personnel']],
        'Equipment'           => ['slug' => 'equipment', 'icon' => 'bi-wrench-adjustable', 'types' => ['Tools' => 'Tools', 'Power Tools' => 'Power Tools', 'Motor Pool' => 'Motor Pool', 'Vehicle' => 'Vehicle']],
        'Safety and Security' => ['slug' => 'safety-and-security', 'icon' => 'bi-shield-check', 'types' => ['Safety' => 'Fire Safety', 'Security' => 'Security']],
    ];

    /** Main category name for a stored module/type name (unknown names fall under Safety and Security, as before). */
    public static function categoryFor(string $module): string
    {
        foreach (self::CATEGORIES as $cat => $c) {
            if (isset($c['types'][$module]) || $module === $cat) return $cat;
        }

        return 'Safety and Security';
    }

    /** Readable type label for a module name, shown under the category. */
    public static function typeLabel(string $module): string
    {
        foreach (self::CATEGORIES as $c) {
            if (isset($c['types'][$module])) return $c['types'][$module];
        }

        return $module;
    }

    public static function modulesForRole(string $role): ?array
    {
        return match (strtolower($role)) {
            'janitorial' => ['Janitorial'],
            'assets'     => ['Vehicle', 'Motor Pool'],
            'sports'     => ['Sports'],
            'security'   => ['Security', 'Vehicle'],
            'facilities' => ['Facilities', 'Janitorial', 'Waste Watch', 'Sports'],
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
            array_unshift($opts, ['value' => '', 'label' => 'All Categories']);
        } elseif (count($scope) > 1) {
            array_unshift($opts, ['value' => '', 'label' => 'All my categories']);
        }

        return $opts;
    }
}
