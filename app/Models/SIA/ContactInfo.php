<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ContactInfo extends Model implements AuditableContract
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
	public static $rules = [
		'phone_work' => ['numeric'],
		'phone_home' => ['numeric'],
		'mobile_number' => ['numeric'],
		'email_personal' => ['required', 'email'],
		'email_work' => ['nullable', 'email'],
	];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contacts_info';

        /**
         * Attributes that should be mass-assignable.
         *
         * @var array
         */
        protected $fillable = [
        	'phone_work',
        	'phone_home',
        	'mobile_number',
        	'email_personal',
        	'email_work',
    	];

    public function party()
    {
    	return $this->belongsTo(Party::class);
    }
}
