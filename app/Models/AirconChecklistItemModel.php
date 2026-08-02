<?php
namespace App\Models;
use CodeIgniter\Model;

class AirconChecklistItemModel extends Model
{
    protected $table         = 'aircon_checklist_items';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'aircon_unit_id', 'task_name', 'is_done', 'completed_at',
    ];

    private const DEFAULT_TASKS = [
        'Clean or replace air filter',
        'Check refrigerant level',
        'Inspect drainage line',
        'Test thermostat / airflow',
        'Clean condenser coils',
        'Check electrical connections',
        'Record unit temperature output',
    ];

    public function getForUnit(int $airconUnitId)
    {
        return $this->where('aircon_unit_id', $airconUnitId)->findAll();
    }

    public function seedDefaultTasks(int $airconUnitId): void
    {
        foreach (self::DEFAULT_TASKS as $task) {
            $this->insert(['aircon_unit_id' => $airconUnitId, 'task_name' => $task]);
        }
    }
}
