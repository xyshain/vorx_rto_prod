<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class TimelogLeave extends Model implements AuditableContract
{
    use Auditable;

    protected $connection = 'SIA';

    /**
	 * The database name used by the model.
	 *
	 * @var string
	 */
	protected $connection = 'DTR';

	 /**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'timelog_leaves';

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
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date_of_filling',
        'type_of_leave',
        'reason_of_leave',
        'number_of_working_days',
        'date_from',
        'date_to',
        'commutation',
        'approval_status',
        'approval_notes',
        'days_with_pay',
        'days_without_pay',
    ];

    public function names()
    {
        return $this->belongsTo(TimelogUser::class, 'user_id');
    }
}
