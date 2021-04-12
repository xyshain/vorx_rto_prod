<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReminderSetting extends Model
{
    protected $connection = 'SIA';
    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
    	'email_content_id',
    	'active',
    	'start_date',
    	'send_time',
    	'end_date',
    	'repeat',
    ];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function emailContent()
    {
    	return $this->belongsTo(EmailContent::class, 'email_content_id', 'id');
    }
}
