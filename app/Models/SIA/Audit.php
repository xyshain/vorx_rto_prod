<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $connection = 'SIA';
    //
    public function user(){
    	return $this->belongsTo(User::class);
    }
}
