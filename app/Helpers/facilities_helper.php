<?php

if (!function_exists('fac_cell')) {
    // One table cell; known status words become colored badges, the rest stay plain text.
    function fac_cell($value): string
    {
        if (is_array($value) && isset($value['raw'])) return '<td>' . $value['raw'] . '</td>';
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
            'Working'         => 'tt-badge zb-done',
            'Safe'            => 'tt-badge zb-done',
            'Returned'        => 'tt-badge zb-done',
            'OK'              => 'tt-badge zb-done',
            'Needs Repair'    => 'tt-badge zb-overdue badge-blink',
            'Needs Refill'    => 'tt-badge zb-needs',
            'Defective'       => 'tt-badge zb-overdue badge-blink',
            'Missing'         => 'tt-badge zb-overdue badge-blink',
            'Unsafe'          => 'tt-badge zb-overdue badge-blink',
            'Due in 7 Days'   => 'tt-badge zb-needs',
            'Due Soon'        => 'tt-badge zb-needs',
            'Expired'         => 'tt-badge zb-overdue badge-blink',
            'Not inspected'   => 'tt-badge zb-needs',
            'Key Out'         => 'tt-badge zb-needs',
            'Vehicle Out'     => 'tt-badge zb-needs',
            'Awaiting Dispatch' => 'tt-badge zb-needs',
            'Key out over 8 hours' => 'tt-badge zb-overdue badge-blink',
            'Available'       => 'tt-badge zb-done',
            'Excellent'       => 'tt-badge zb-done',
            'Good'            => 'tt-badge zb-done',
            'Fair'            => 'tt-badge zb-needs',
            'Poor'            => 'tt-badge zb-overdue badge-blink',
            'Borrowed'        => 'tt-badge zb-needs',
            'Disposal'        => 'tt-badge zb-overdue',
            'In Use'          => 'tt-badge zb-needs',
            'Reserved'        => 'tt-badge zb-needs',
            'Maintenance'     => 'tt-badge zb-needs',
            'Inactive'        => 'tt-badge zb-overdue',
            'Under Maintenance' => 'tt-badge zb-needs',
            'Out of Service'  => 'tt-badge zb-overdue badge-blink',
            'Service Overdue' => 'tt-badge zb-overdue badge-blink',
            'Service Due Soon' => 'tt-badge zb-needs',
            'Cancelled'       => 'tt-badge zb-overdue',
            'Submitted'       => 'tt-badge zb-needs',
            'Reviewed'        => 'tt-badge zb-needs',
            'Approved'        => 'tt-badge zb-done',
            'In Transit'      => 'tt-badge zb-needs',
            'Rejected'        => 'tt-badge zb-overdue',
            'Active'          => 'tt-badge zb-done',
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
