<?php

namespace App\Libraries;

/**
 * Which Personnel Management categories (the Drivers / Janitors / … tabs)
 * each role may see and manage. One definition, used by AuthFilter (route
 * access), PersonnelController (add/edit/archive/view checks) and the
 * sidebar, so the three never disagree.
 */
class PersonnelScope
{
    /** Category key => its personnel page. Same keyword grouping as PersonnelController's tabs. */
    public const CATEGORY_ROUTES = [
        'drivers'      => 'personnel/drivers',
        'janitors'     => 'personnel/janitors',
        'carpentries'  => 'personnel/carpentries',
        'maintenance'  => 'personnel/maintenance',
        'construction' => 'personnel/construction-workers',
        'security'     => 'personnel/security',
    ];

    /** Roles limited to some categories. Roles not listed here see every category (or none, per AuthFilter). */
    private const ROLE_CATEGORIES = [
        'janitorial'        => ['janitors'],
        'personnel & tools' => ['maintenance', 'carpentries', 'construction'],
        'security'          => ['drivers', 'security', 'maintenance'],
    ];

    /** Personnel actions a scoped role needs besides its category pages; each one checks the record's category. */
    public const SCOPED_ACTION_PREFIXES = ['personnel/add', 'personnel/edit', 'personnel/delete', 'personnel/view', 'personnel/detail', 'personnel/mis-lookup'];

    /** Category keys the role may use, or null when it isn't limited to specific categories. */
    public static function categoriesFor(string $role): ?array
    {
        return self::ROLE_CATEGORIES[strtolower($role)] ?? null;
    }

    /** The category a position belongs to (first match wins, same order as the Personnel list), or null. */
    public static function categoryOf(?string $position): ?string
    {
        $p = strtolower((string) $position);

        return match (true) {
            str_contains($p, 'driver')                                         => 'drivers',
            str_contains($p, 'janitor') || str_contains($p, 'cleaning')        => 'janitors',
            str_contains($p, 'carpenter')                                      => 'carpentries',
            str_contains($p, 'maintenance') || str_contains($p, 'physical plant') => 'maintenance',
            str_contains($p, 'construction')                                   => 'construction',
            str_contains($p, 'security') || str_contains($p, 'guard')          => 'security',
            default                                                            => null,
        };
    }

    /** Whether the role may see/manage a person with this position. */
    public static function allowsPosition(string $role, ?string $position): bool
    {
        $allowed = self::categoriesFor($role);

        return $allowed === null || in_array(self::categoryOf($position), $allowed, true);
    }

    /** Route prefixes a scoped role needs for its personnel categories; [] for unscoped roles. */
    public static function routePrefixesFor(string $role): array
    {
        $allowed = self::categoriesFor($role);
        if ($allowed === null) {
            return [];
        }

        return array_merge(array_map(static fn($c) => self::CATEGORY_ROUTES[$c], $allowed), self::SCOPED_ACTION_PREFIXES);
    }
}
