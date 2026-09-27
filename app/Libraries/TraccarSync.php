<?php

namespace App\Libraries;

use App\Models\GPSModel;
use App\Models\VehicleModel;

/**
 * Pulls live tracker data from Traccar into the tables the rest of the system
 * already reads: gps_logs (latest position + ping history) and
 * vehicles.gps_status (the single source of truth for Online/Offline). That
 * way the GPS Tracker page, Vehicle Management and their popups all show the
 * real tracker without each one having to know about Traccar.
 *
 * Only vehicles with vehicles.gps_device_id set are touched; everything else
 * keeps behaving exactly as before.
 */
class TraccarSync
{
    /**
     * @param int|null $onlyVehicleId sync just this vehicle instead of the whole fleet
     * @return array<int,array> live Traccar data keyed by vehicle id (only vehicles Traccar answered for)
     */
    public function run(?int $onlyVehicleId = null): array
    {
        $vehicleModel = new VehicleModel();
        $query = $vehicleModel->where('is_archived', 0)
                              ->where('gps_device_id IS NOT NULL', null, false)
                              ->where('gps_device_id !=', '');
        if ($onlyVehicleId !== null) {
            $query->where('id', $onlyVehicleId);
        }
        $vehicles = $query->findAll();
        if (!$vehicles) {
            return [];
        }

        $live = (new TraccarClient())->latestByIdentifier();
        if (!$live) {
            return [];
        }

        $gpsModel = new GPSModel();
        $result   = [];

        foreach ($vehicles as $v) {
            $p = $live[$v['gps_device_id']] ?? null;
            if (!$p) {
                continue;
            }

            $status = $p['online'] ? 'Online' : 'Offline';
            if ($status !== $v['gps_status']) {
                $vehicleModel->update($v['id'], ['gps_status' => $status]);
            }

            if ($p['latitude'] !== null && $p['fix_time'] !== null) {
                // Traccar times are UTC; strtotime handles the offset and
                // date() renders it in the app's timezone (Asia/Manila), the
                // same clock the rest of gps_logs.logged_at is written in.
                $fixEpoch = strtotime($p['fix_time']);
                $last     = $gpsModel->where('vehicle_id', $v['id'])->orderBy('id', 'DESC')->first();

                if (!$last || strtotime($last['logged_at']) < $fixEpoch) {
                    $gpsModel->insert([
                        'vehicle_id' => $v['id'],
                        'device_id'  => $v['gps_device_id'],
                        'latitude'   => $p['latitude'],
                        'longitude'  => $p['longitude'],
                        'status'     => $status,
                        'logged_at'  => date('Y-m-d H:i:s', $fixEpoch),
                    ]);
                }
            }

            $result[(int) $v['id']] = $p;
        }

        return $result;
    }
}
