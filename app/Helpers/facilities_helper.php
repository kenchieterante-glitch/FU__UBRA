<?php

if (!function_exists('fac_cell')) {
    // One table cell; known status words become colored badges, the rest stay plain text.
    function fac_cell($value): string
    {
        $text = (string) $value;
        $map = [
            'Pending'         => 'tt-badge zb-needs',
            'In Progress'     => 'tt-badge zb-needs',
            'No Tasks'        => 'tt-badge zb-needs',
            'Not checked'     => 'tt-badge zb-needs badge-blink',
            'Needs Cleaning'  => 'tt-badge zb-needs badge-blink',
            'Overdue'         => 'tt-badge zb-overdue badge-blink',
            'Needs Attention' => 'tt-badge zb-overdue badge-blink',
            'Overdue in 7 Days' => 'tt-badge zb-overdue badge-blink',
            'Not Working'     => 'tt-badge zb-overdue',
            'Completed'       => 'tt-badge zb-done',
            'Passed'          => 'tt-badge zb-done',
            'Operational'     => 'tt-badge zb-done',
            'Scheduled'       => 'status-badge status-completed',
            'Routine'         => 'status-badge status-available',
            'Urgent'          => 'status-badge status-pending',
        ];
        $cls = $map[$text] ?? null;
        $safe = esc($text);

        return $cls ? '<td><span class="' . $cls . '">' . $safe . '</span></td>' : '<td>' . $safe . '</td>';
    }
}

if (!function_exists('tool_cat_label')) {
    // The stored category stays "Consumable"; this is only the name people see.
    function tool_cat_label($category): string
    {
        return $category === 'Consumable' ? 'Supplies & Materials' : (string) $category;
    }
}
