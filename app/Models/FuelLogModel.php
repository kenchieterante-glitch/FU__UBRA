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

        if (count($logs) < 2) {
            return ['hasData' => false];
        }

        $rates = [];
        for ($i = 1; $i < count($logs); $i++) {
            $distance = (float) $logs[$i]['odometer_km'] - (float) $logs[$i - 1]['odometer_km'];
            if ($distance <= 0) continue;
            $rates[] = (float) $logs[$i]['liters_filled'] / $distance;
        }

        if ($rates === []) {
            return ['hasData' => false];
        }

        $avgLPerKm = array_sum($rates) / count($rates);

        $first = $logs[0];
        $last  = $logs[count($logs) - 1];
        $totalDistance = (float) $last['odometer_km'] - (float) $first['odometer_km'];
        $totalDays = max(1, (strtotime($last['logged_at']) - strtotime($first['logged_at'])) / 86400);
        $avgKmPerDay = $totalDistance / $totalDays;

        $predictedLiters30d = $avgLPerKm * $avgKmPerDay * 30;

        return [
            'hasData'            => true,
            'avgLPer100km'       => round($avgLPerKm * 100, 2),
            'avgKmPerDay'        => round($avgKmPerDay, 1),
            'predictedLiters30d' => round($predictedLiters30d, 1),
        ];
    }
}
