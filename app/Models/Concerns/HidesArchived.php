<?php

namespace App\Models\Concerns;

/**
 * Records archived from the Reports page are only *marked* in the record_archive table (their own
 * table has no archive column). Models using this trait leave marked records out of normal lists
 * and counts; the Reports page opts back in with withArchived().
 *
 * The model must set  protected string $archiveType  to its record kind (e.g. 'aircon').
 */
trait HidesArchived
{
    protected bool $includeArchived = false;
    private static ?bool $archiveTableReady = null;

    /** Include archived records in the next query (used by the Reports page). */
    public function withArchived(): static
    {
        $this->includeArchived = true;

        return $this;
    }

    private function hideArchivedRows(): void
    {
        if ($this->includeArchived) {
            $this->includeArchived = false;

            return;
        }
        self::$archiveTableReady ??= $this->db->tableExists('record_archive');
        if (!self::$archiveTableReady) {
            return;
        }
        $this->builder()->where(
            "{$this->table}.{$this->primaryKey} NOT IN (SELECT record_id FROM record_archive WHERE record_type = " . $this->db->escape($this->archiveType) . ')',
            null,
            false
        );
    }

    public function findAll(?int $limit = null, int $offset = 0)
    {
        $this->hideArchivedRows();

        return parent::findAll($limit, $offset);
    }

    public function first()
    {
        $this->hideArchivedRows();

        return parent::first();
    }

    public function countAllResults(bool $reset = true, bool $test = false)
    {
        $this->hideArchivedRows();

        return parent::countAllResults($reset, $test);
    }
}
