<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;

class StudentDoc extends Model
{
    //
    protected $connection = 'SIA';
    protected $fillable =['deal_id', 'file_name', 'size', 'ext' ,'verified'];
    
}
