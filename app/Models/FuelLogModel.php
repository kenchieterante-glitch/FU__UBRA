<?php
namespace App\Models;
use CodeIgniter\Model;

class FuelLogModel extends Model
{
    protected $table         = 'fuel_logs';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'vehicle_id', 'odometer_km', 'liters_filled', 'logged_at', 'logged_by', 'notes', 'created_at',
    ];

    public function getForVehicle(int $vehicleId): array
    {
        return $this->where('vehicle_id', $vehicleId)
                    ->orderBy('odometer_km', 'ASC')
                    ->findAll();
    }

    // Basic average-consumption prediction from a vehicle's own refuel
    // history — no ML, just full-to-full math: each fill's liters divided
    // by the distance driven since the previous fill, averaged across all
    // fills, then projected using this same history's average daily
    // distance. Needs at least 2 logs (one distance interval) to say anything.
    public function getPrediction(int $vehicleId): array
    {
        $logs = $this->getForVehicle($vehicleId);

        // Predicting a rate (L/km) needs the DISTANCE and FUEL USED BETWEEN
        // two fill-ups — one log alone has nothing to measure a rate from,
        // so this is expected, not a bug, until a second entry exists.
        if (count($logs) < 2) {
            return ['hasData' => false, 'logsCount' => count($logs)];
        }

        $rates = [];
        for ($i = 1; $i < count($logs); $i++) {
            $distance = (float) $logs[$i]['odometer_km'] - (float) $logs[$i - 1]['odometer_km'];
            if ($distance <= 0) continue;
            $rates[] = (float) $logs[$i]['liters_filled'] / $distance;
        }

        if ($rates === []) {
            // Every consecutive pair had a flat/decreasing odometer reading
            // (e.g. a typo'd km value) — same "no usable rate yet" case.
            return ['hasData' => false, 'logsCount' => count($logs)];
        }

        $avgLPerKm = array_sum($rates) / count($rates);

        $first = $logs[0];
        $last  = $logs[count($logs) - 1];
        $totalDistance = (float) $last['odometer_km'] - (float) $first['odometer_km'];
        $totalDays = max(1, (strtotime($last['logged_at']) - strtotime($first['logged_at'])) / 86400);

        // Actual trip cadence (from Trip Ticket / Travel records) beats
        // spreading the distance flat across every calendar day — a vehicle
        // idle for weeks then used for a couple of trips would otherwise
        // average out to a misleadingly low daily rate. Where this vehicle
        // has real completed-trip history, "how many km per trip-day" x
        // "how many trip-days it actually tends to have in 30 days" gives a
        // truer picture than "km per calendar day".
        $travelModel      = new TravelModel();
        $tripDaysInWindow = $travelModel->countDistinctTripDaysForVehicle((int) $first['vehicle_id'], $first['logged_at'], $last['logged_at']);

        // 90-day lookback (independent of the fuel-log window, which can be
        // short) for a steadier "how often does this vehicle actually go
        // out" frequency reading.
        $lookbackFrom   = date('Y-m-d', strtotime('-90 days'));
        $lookbackTo     = date('Y-m-d');
        $recentTripDays = $travelModel->countDistinctTripDaysForVehicle((int) $first['vehicle_id'], $lookbackFrom, $lookbackTo);

        if ($tripDaysInWindow > 0 && $recentTripDays > 0) {
            $avgKmPerTripDay      = $totalDistance / $tripDaysInWindow;
            $tripsPerWeek         = $recentTripDays / (90 / 7);
            $projectedTripDays30d = $tripsPerWeek * (30 / 7);
            $avgKmPerDay          = $avgKmPerTripDay * $tripsPerWeek / 7; // "effective" daily rate, for display only
            $predictedLiters30d   = $avgLPerKm * $avgKmPerTripDay * $projectedTripDays30d;
        } else {
            // No completed Trip Ticket history for this vehicle to key off
            // of (e.g. fuel logged for a vehicle used off the books) — fall
            // back to the old flat calendar-day average so it still predicts.
            $avgKmPerDay        = $totalDistance / $totalDays;
            $predictedLiters30d = $avgLPerKm * $avgKmPerDay * 30;
        }

        return [
            'hasData'            => true,
            'avgLPer100km'       => round($avgLPerKm * 100, 2),
            'avgKmPerDay'        => round($avgKmPerDay, 1),
            'predictedLiters30d' => round($predictedLiters30d, 1),
        ];
    }
}
