<?php

namespace App\Libraries;

/**
 * Keeps Mr. UBRA inside the signed-in account's own department.
 * Administrators (and any role not listed) get the full, system-wide assistant.
 */
class UbraScope
{
    // Words that clearly belong to one department. Used to refuse questions that wander into another department's area.
    private const KEYWORDS = [
        'facilities' => ['aircon', 'air-con', 'air con', 'janitor', 'cleaning', 'repair request', 'building check', 'supplies', 'consumable', 'detergent'],
        'security'   => ['fire extinguisher', 'fire safety', 'smoke detector', 'fire alarm', 'exit sign', 'guard', 'key log', 'keys', 'floor plan', 'inspection'],
        'assets'     => ['vehicle', 'vehicles', 'driver', 'drivers', 'trip ticket', 'motor pool', 'gps', 'fleet', 'mechanical equipment', 'generator'],
        'sports'     => ['sports', 'basketball', 'volleyball', 'badminton', 'racket', 'soccer', 'table tennis'],
        'tools'      => ['tools', 'power tool', 'borrowing ledger'],
        'janitorial' => ['janitor', 'cleaning', 'zone', 'shift'],
    ];

    private const DEPARTMENTS = [
        'facilities' => [
            'name'   => 'Facilities Administration and General Services',
            'scope'  => 'repair requests, aircon units and cleaning schedules, cleaning checks and zones, supplies, and the monthly building checks',
            'quick'  => [
                ['bi-clipboard2-check', 'Open repair requests', 'What repair requests are still open or urgent?'],
                ['bi-snow2', 'Aircon due', 'Which aircon units are overdue or due for cleaning soon?'],
                ['bi-brush', 'Cleaning progress', 'How far along are today\'s cleaning checks?'],
                ['bi-box-seam', 'Supplies', 'Which supplies are out of stock?'],
            ],
        ],
        'security' => [
            'name'   => 'Safety and Security Department',
            'scope'  => 'fire safety equipment (extinguishers, smoke detectors, alarms, exit signs), floor plans, guard monitoring and keys, and safety inspections',
            'quick'  => [
                ['bi-fire', 'Fire safety status', 'Give me a fire safety status — what is expired, overdue or needs attention?'],
                ['bi-key', 'Keys', 'Which keys are borrowed right now, and by whom?'],
                ['bi-clipboard2-check', 'Inspections', 'Which buildings are unsafe or still not inspected this month?'],
                ['bi-file-earmark-text', 'Weekly report', 'Generate a brief weekly safety and security report.'],
            ],
        ],
        'assets' => [
            'name'   => 'Asset Acquisition and Monitoring Department',
            'scope'  => 'vehicles and drivers, vehicle maintenance, mechanical equipment, motor pool work orders, trip tickets, and the GPS tracker',
            'quick'  => [
                ['bi-truck', 'Fleet health', 'Give me a fleet health check — vehicle status and anything needing attention.'],
                ['bi-wrench-adjustable', 'Work orders', 'What motor pool work orders are open or urgent?'],
                ['bi-signpost-2', 'Trips today', 'What trips are scheduled today and which are in transit?'],
                ['bi-file-earmark-text', 'Weekly report', 'Generate a brief weekly vehicle and motor pool report.'],
            ],
        ],
        'sports' => [
            'name'   => 'Sports Equipment Monitoring',
            'scope'  => 'sports equipment records, equipment condition and location, and borrowing and returns',
            'quick'  => [
                ['bi-trophy', 'Equipment status', 'Give me a sports equipment status — what needs attention?'],
                ['bi-box-arrow-up-right', 'Borrowed now', 'What sports equipment is borrowed right now and when is it due back?'],
                ['bi-alarm', 'Overdue returns', 'Is any sports equipment overdue for return?'],
                ['bi-file-earmark-text', 'Weekly report', 'Generate a brief weekly sports equipment report.'],
            ],
        ],
        'janitorial' => [
            'name'   => 'Janitorial Monitoring',
            'scope'  => 'cleaning zones, shift assignments, cleaning tasks, and cleaning supplies',
            'quick'  => [
                ['bi-brush', 'Cleaning progress', 'How many zones are cleaned today?'],
                ['bi-people', 'Shifts', 'Who is on shift today?'],
                ['bi-file-earmark-text', 'Weekly report', 'Generate a brief weekly janitorial report.'],
            ],
        ],
        'tools' => [
            'name'   => 'Tools and Equipment',
            'scope'  => 'tools and equipment records, borrowing, and supplies',
            'quick'  => [
                ['bi-tools', 'Borrowed tools', 'Which tools are borrowed right now?'],
                ['bi-file-earmark-text', 'Weekly report', 'Generate a brief weekly tools report.'],
            ],
        ],
    ];

    /** Department key for a role, or null for the unrestricted (administrator) assistant. */
    public static function departmentFor(string $role): ?string
    {
        return match (strtolower($role)) {
            'facilities' => 'facilities',
            'security'   => 'security',
            'assets'     => 'assets',
            'sports'     => 'sports',
            'janitorial' => 'janitorial',
            'tools'      => 'tools',
            default      => null,
        };
    }

    public static function profile(?string $dept): ?array
    {
        return $dept ? (self::DEPARTMENTS[$dept] ?? null) : null;
    }

    public static function quickActions(string $role): ?array
    {
        $p = self::profile(self::departmentFor($role));

        return $p ? $p['quick'] : null;
    }

    private static function hits(string $message, array $words): bool
    {
        foreach ($words as $w) {
            if (preg_match('/\b' . preg_quote($w, '/') . '(?:s|es)?\b/i', $message)) return true;
        }

        return false;
    }

    /**
     * If the message is clearly about another department (and not about the account's own), returns the refusal text.
     * Returns null when the question may go on to the assistant.
     */
    public static function refusalFor(string $role, string $message): ?string
    {
        $dept = self::departmentFor($role);
        if ($dept === null) return null;
        $profile = self::DEPARTMENTS[$dept];

        $own = self::hits($message, self::KEYWORDS[$dept] ?? []);
        if ($own) return null;
        foreach (self::KEYWORDS as $other => $words) {
            if ($other === $dept) continue;
            // Janitorial and Facilities share cleaning words — never treat them as "other" for each other.
            if (($dept === 'facilities' && $other === 'janitorial') || ($dept === 'janitorial' && $other === 'facilities')) continue;
            if (self::hits($message, $words)) {
                return "That's outside your account's access. This assistant only answers about **{$profile['name']}** — {$profile['scope']}. "
                    . "Please ask the account that handles that area, or ask me something about your own department.";
            }
        }

        return null;
    }
}
