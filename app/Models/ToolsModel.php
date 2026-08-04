<?php
namespace App\Models;
use CodeIgniter\Model;

class ToolsModel extends Model
{
    protected $table         = 'tools';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'asset_name','asset_code','category','location','custodian',
        'condition_status','availability','current_stock','reorder_threshold',
        'is_archived','archived_at','last_activity_at'
    ];

    public function getAvailableTools()
    {
        return $this->where('availability', 'Available')->where('is_archived', 0)->findAll();
    }

    // Joins the currently-open borrow_records row for each tool (if any) so
    // the table can show who actually has it right now, not just the
    // permanently-assigned custodian. A tool can only have one active
    // borrow at a time, so this picks the latest one if data ever drifts.
    private const BORROWER_JOIN_TABLE = 'borrow_records br';
    private const BORROWER_JOIN_COND = 'br.tool_id = tools.id AND br.status = "Borrowed" '
        . 'AND br.id = (SELECT MAX(id) FROM borrow_records WHERE tool_id = tools.id AND status = "Borrowed")';

    public function getAllWithDetails()
    {
        $builder = $this->select('tools.*, tools.custodian as custodian_name, br.borrower as borrower_name')
                        ->join(self::BORROWER_JOIN_TABLE, self::BORROWER_JOIN_COND, 'left', false)
                        ->where('tools.is_archived', 0)
                        ->orderBy('tools.id', 'DESC');
        return $builder->findAll();
    }

    public function getByCategory($category)
    {
        return $this->select('tools.*, tools.custodian as custodian_name, br.borrower as borrower_name')
                    ->join(self::BORROWER_JOIN_TABLE, self::BORROWER_JOIN_COND, 'left', false)
                    ->where('tools.category', $category)
                    ->where('tools.is_archived', 0)
                    ->orderBy('tools.id', 'DESC')
                    ->findAll();
    }

    public function getCategoryDistribution(): array
    {
        try {
            return $this->db->query("
                SELECT category, COUNT(*) AS count
                FROM tools
                WHERE is_archived = 0
                GROUP BY category
                ORDER BY count DESC
            ")->getResultArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
