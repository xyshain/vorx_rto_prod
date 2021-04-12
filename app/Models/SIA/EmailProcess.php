<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class EmailProcess extends Model implements AuditableContract
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
     * resolver
     *
     */
    public static function resolveId()
    {
        return Auth::guard('agent')->check() ? Auth::guard('agent')->id() : null;
    }

    //
    protected $fillable = ['email_process_types_id','persons_id','user_id','offer_letter_num'];
}
