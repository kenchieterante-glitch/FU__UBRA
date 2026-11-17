<?php

namespace App\Libraries;

/**
 * Which Tools Management categories (the Power Tools / Supplies / … tabs)
 * each role may see and act on. Same idea as PersonnelScope: AuthFilter,
 * ToolsController and the sidebar all read this one definition.
 */
class ToolsScope
{
    /** Tool category (tools.category) => its Tools Management page. */
    public const CATEGORY_ROUTES = [
        'Power Tools'      => 'tools/power-tools',
        'Consumable'       => 'tools/consumable',
        'Sports Equipment' => 'tools/sports-equipment',
    ];

    /** Roles limited to some tool categories. Roles not listed here see all of Tools Management (or none, per AuthFilter). */
    private const ROLE_CATEGORIES = [
        'personnel & tools' => ['Power Tools'],
    ];

    /** Tool actions a scoped role needs besides its category pages; each one checks the tool's category. */
    public const SCOPED_ACTION_PREFIXES = ['tools/refresh', 'tools/add', 'tools/edit', 'tools/borrow', 'tools/returnTool', 'tools/qr-pdf'];

    /** Tool categories the role may use, or null when it isn't limited to specific categories. */
    public static function categoriesFor(string $role): ?array
    {
        return self::ROLE_CATEGORIES[strtolower($role)] ?? null;
    }

    public static function allowsCategory(string $role, ?string $category): bool
    {
        $allowed = self::categoriesFor($role);

        return $allowed === null || in_array((string) $category, $allowed, true);
    }

    /** Route prefixes a scoped role needs for its tool categories; [] for unscoped roles. */
    public static function routePrefixesFor(string $role): array
    {
        $allowed = self::categoriesFor($role);
        if ($allowed === null) {
            return [];
        }

        return array_merge(array_map(static fn($c) => self::CATEGORY_ROUTES[$c], $allowed), self::SCOPED_ACTION_PREFIXES);
    }

    /** The page a scoped role lands on for Tools Management. */
    public static function homeFor(string $role): string
    {
        $allowed = self::categoriesFor($role);

        return $allowed === null ? 'tools' : self::CATEGORY_ROUTES[$allowed[0]];
    }
}
