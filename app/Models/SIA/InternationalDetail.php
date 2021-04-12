<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class InternationalDetail extends Model implements AuditableContract
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

    protected $fillable = [
    	'title',
    	'current_address',
    	'stat_dec_needed',
    	'country',
    	'city',
    	'state_province',
    	'postcode',
    	'mobile',
    	'telephone',
    	'country_of_birth',
    	'country_of_citizenship',
    	'passport_number',
    	'expiry_date',
        'email_personal'
    ];

    public function party()
    {
    	return $this->belongsTo(Party::class);
    }
}
