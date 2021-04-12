<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class TrainingDeliveryLoc extends Model implements AuditableContract
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
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'training_delivery_loc';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'org_identifier',
        'dlvr_loc_identifier',
        'dlvr_loc_name',
        'postcode',
        'state_identifier',
        'addr_location',
        'country_identifier'
    ];

    use SoftDeletes;
    protected $dates = ['deleted_at'];


}
