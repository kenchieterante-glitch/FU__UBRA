<?php

namespace App\Controllers;

use App\Models\FireExtinguisherModel;

/**
 * Public evacuation map for students and visitors (no sign-in). Each building
 * shows its floors and the fire extinguishers registered for it, taken from the
 * same fire_extinguishers table the Safety and Security module maintains.
 */
class EvacuationController extends BaseController
{
    public function index()
    {
        $byBuilding = [];
        foreach ((new FireExtinguisherModel())->select('location, floor')->findAll() as $row) {
            $key = $this->norm($row['location']);
            $byBuilding[$key]['floors'][$row['floor']] = ($byBuilding[$key]['floors'][$row['floor']] ?? 0) + 1;
        }

        $fireInfo = [];
        foreach ($byBuilding as $key => $b) {
            $parts = [];
            foreach ($b['floors'] as $floor => $n) {
                $parts[] = "{$floor}: {$n}";
            }
            $fireInfo[$key] = [
                'count'   => array_sum($b['floors']),
                'byFloor' => implode(', ', $parts),
                'floors'  => $this->floorsFor($key),
            ];
        }
        // Buildings without extinguishers still show their floors.
        foreach (FireExtinguisherModel::BUILDING_FLOORS as $name => $floors) {
            $key = $this->norm($name);
            $fireInfo[$key] ??= ['count' => 0, 'byFloor' => '—', 'floors' => count($floors) . ' (' . implode(', ', $floors) . ')'];
        }

        return view('evacuation/map', ['fireInfo' => $fireInfo]);
    }

    private function norm(string $s): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($s));
    }

    private function floorsFor(string $key): string
    {
        foreach (FireExtinguisherModel::BUILDING_FLOORS as $name => $floors) {
            if ($this->norm($name) === $key) {
                return count($floors) . ' (' . implode(', ', $floors) . ')';
            }
        }

        return count(FireExtinguisherModel::DEFAULT_FLOORS) . ' (' . implode(', ', FireExtinguisherModel::DEFAULT_FLOORS) . ')';
    }
}
