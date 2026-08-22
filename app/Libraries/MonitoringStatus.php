<?php

namespace App\Libraries;

/**
 * Shared expiry-status math for the Job Order Personnel Monitoring
 * module (job orders, contracts, documents). Manually-set states (e.g.
 * TERMINATED, CANCELLED, COMPLETED, RENEWED, DRAFT, PENDING, FOR_RENEWAL,
 * VERIFIED/REJECTED/MISSING for documents) are left as-is; only the
 * time-driven states are recomputed from the stored end/expiration date on
 * every read. No cron exists in this app, so compute-on-read is what keeps
 * these always correct without a background job.
 */
class MonitoringStatus
{
    public static function thresholdDays(): int
    {
        try {
            $row = \Config\Database::connect()
                ->table('system_settings')
                ->where('setting_key', 'job_order_expiry_threshold_days')
                ->get()->getRow();
            $value = (int) ($row->setting_value ?? 30);
            return $value > 0 ? $value : 30;
        } catch (\Exception $e) {
            return 30;
        }
    }

    /**
     * @param string|null $endDate      Y-m-d or null
     * @param string      $manualStatus Currently stored status
     * @param array       $timeDriven   Status values this field auto-recomputes over (the rest pass through untouched)
     */
    public static function derive(?string $endDate, string $manualStatus, array $timeDriven, ?int $thresholdDays = null): string
    {
        if (!in_array($manualStatus, $timeDriven, true)) {
            return $manualStatus;
        }
        if (empty($endDate)) {
            return $manualStatus;
        }

        $thresholdDays ??= self::thresholdDays();
        $daysLeft = self::daysRemaining($endDate);

        if ($daysLeft < 0) {
            return 'EXPIRED';
        }
        if ($daysLeft <= $thresholdDays) {
            return 'EXPIRING_SOON';
        }
        return 'ACTIVE';
    }

    public static function daysRemaining(?string $endDate): int
    {
        if (empty($endDate)) {
            return PHP_INT_MAX;
        }
        $today = new \DateTime(date('Y-m-d'));
        $end   = new \DateTime(substr($endDate, 0, 10));
        return (int) $today->diff($end)->format('%r%a');
    }
}
