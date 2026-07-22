<?php
namespace App\Models;
use CodeIgniter\Model;

class PredictionModel extends Model
{
    protected $table         = 'predictions';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'module', 'insight_text', 'suggestion_text'
    ];
}
