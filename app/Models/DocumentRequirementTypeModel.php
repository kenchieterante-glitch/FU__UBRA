<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentRequirementTypeModel extends Model
{
    protected $table         = 'document_requirement_types';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['name', 'is_required', 'sort_order'];

    public function getAllOrdered()
    {
        return $this->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC')->findAll();
    }

    public function getRequired()
    {
        return $this->where('is_required', 1)->orderBy('sort_order', 'ASC')->findAll();
    }
}
