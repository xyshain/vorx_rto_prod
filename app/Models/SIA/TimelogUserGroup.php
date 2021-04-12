<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class TimelogUserGroup extends Model implements AuditableContract
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
	protected $table = 'timelog_user_group';

    /**
    * Should the timestamps be audited?
    *
    * @var bool
    */
    protected $auditTimestamps = true;

}
