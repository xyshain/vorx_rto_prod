<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class EmailSettings extends Model implements AuditableContract
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
	 *
	 * static attribute validation rules
	 *
	 * @var array
	 */

	 protected $fillable = [
	 	'user_id',
    	'serve_name',
    	'port',
    	'username',
    	'password',
	];

	public function user(){
		return $this->belongsTo(User::class);
	}
}
