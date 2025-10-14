<?php
namespace App\Models;

use CodeIgniter\Model;

class TemplateModel extends Model
{
    protected $table = 'template';
    protected $primaryKey = 'id';
    protected $allowedFields = [];
}
