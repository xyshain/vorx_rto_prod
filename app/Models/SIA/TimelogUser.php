<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class TimelogUser extends Model implements AuditableContract
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
	protected $table = 'timelog_users';

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

    public function group()
    {
        return $this->belongsTo(TimelogUserGroup::class, 'group_id');
    }

}
