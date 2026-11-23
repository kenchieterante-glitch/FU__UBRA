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

    /**
     * Keywords that put a position into a category (case-insensitive, "contains").
     * Order matters: the first category that matches wins, so a position never
     * lands in two tabs. Covers the wording used by the FU MIS (e.g. "Carpentry
     * Staff", "Electrician", "Mason", "Welder") as well as the older names.
     */
    public const CATEGORY_KEYWORDS = [
        'drivers'      => ['driver'],
        'janitors'     => ['janitor', 'cleaning'],
        'carpentries'  => ['carpenter', 'carpentry'],
        'maintenance'  => ['maintenance', 'physical plant', 'plumb', 'electric', 'weld', 'paint', 'mechanic', 'aircon', 'technician', 'repair'],
        'construction' => ['construction', 'mason', 'labor', 'helper'],
        'security'     => ['security', 'guard'],
    ];

    /** Sub-tabs under Maintenance: slug => [label, keywords]. First match wins; anything else is "other". */
    public const MAINTENANCE_TRADES = [
        'electrician' => ['Electrician', ['electric']],
        'plumber'     => ['Plumber',     ['plumb']],
        'welder'      => ['Welder',      ['weld']],
        'painter'     => ['Painter',     ['paint']],
        'mechanic'    => ['Mechanic',    ['mechanic']],
        'technician'  => ['Technician',  ['technician', 'aircon', 'repair']],
    ];

    /** Which Maintenance sub-tab a position belongs to ('other' when it is none of the trades). */
    public static function maintenanceTradeOf(?string $position): string
    {
        $p = strtolower((string) $position);
        foreach (self::MAINTENANCE_TRADES as $slug => [, $keywords]) {
            foreach ($keywords as $kw) {
                if (str_contains($p, $kw)) {
                    return $slug;
                }
            }
        }

        return 'other';
    }

    /** The category a position belongs to (first match wins, same order as the Personnel list), or null. */
    public static function categoryOf(?string $position): ?string
    {
        $p = strtolower((string) $position);
        if ($p === '') {
            return null;
        }
        foreach (self::CATEGORY_KEYWORDS as $category => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($p, $kw)) {
                    return $category;
                }
            }
        }

        return null;
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
