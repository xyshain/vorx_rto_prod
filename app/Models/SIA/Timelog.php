<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Timelog extends Model implements AuditableContract
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
	protected $table = 'timelog';

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
    protected $fillable = ['user_id','log_type_id','timelog', 'notes', 'subtime_diff'];

    // Soft Deletes 
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function names()
    {
        return $this->belongsTo(TimelogUser::class, 'user_id');
    }
}
