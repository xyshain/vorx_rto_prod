<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Phonelog extends Model implements AuditableContract
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

    //
    protected $fillable = [
    						'person_taking',
    						'account',
    						'name_of_person_company',
    						'phone_number',
    						'email',
    						'request',
    						'date',
    						'time_call',
    						'lenght_call',
    						'purpose_call',
    						'followup'
    					];
}
