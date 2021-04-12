<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Email extends Model implements AuditableContract
{
    use Auditable;

    protected $connection = 'SIA';

    /**
     * Auditable events.
     *
     * @var array
     */
    protected $auditableEvents = [
        'deleted',
        'restored',
        'updated',
        'created'
    ];

    /**
    * Should the timestamps be audited?
    *
    * @var bool
    */
    protected $auditTimestamps = true;

    /**
        * The table associated with the model.
        *
        * @var string
        */
       protected $table = 'user_emails';

    /**
     *
     * Fillables
     * 
     * @var array
     */
    protected $fillable = [
    	'email', 'email_password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    	'email_password'
    ];

    /**
     *
     * Guarded
     *
     */
    protected $guarded = [];

    /**
     *
     * Get associated user on each email
     *
     */
    public function user()
    {
    	return $this->belongsTo(User::class);
    }
}
