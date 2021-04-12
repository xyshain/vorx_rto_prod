<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;

class TaskBoard extends Model
{
	//
	protected $connection = 'SIA';
    protected $table = 'user_taskboards';

    protected $fillable = [
	 	'username',
    	'password',
    	'default_prj'
	];

    public function user(){
		return $this->belongsTo(User::class);
	}
}
