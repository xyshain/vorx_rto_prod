<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Ultraware\Roles\Models\Permission as PermissionBase;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Permission extends PermissionBase implements AuditableContract
{
    use Auditable;

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
        'name'  => 'required',
        'slug'  => 'bail|required|unique:roles,slug',
        'model' => 'nullable',
        'description'   => 'nullable',
    ];
}
