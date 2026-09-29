<?php
namespace App\Models;
use CodeIgniter\Model;

class ReturnModel extends Model
{
    protected $table = 'return_records';
    protected $primaryKey = 'id';
    protected $allowedFields = ['borrow_id','tool_id','quantity_returned','returned_by','return_date','condition_status','remarks'];
}
?>