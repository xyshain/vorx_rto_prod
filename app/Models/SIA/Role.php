<?php

namespace App\Models\SIA;

use Ultraware\Roles\Models\Role as RoleBase;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Role extends RoleBase implements AuditableContract
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
		'name'	=> 'required',
		'slug'	=> 'bail|required|unique:roles,slug',
		'description'	=> 'nullable',
	];

    //
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
