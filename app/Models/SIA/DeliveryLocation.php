<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class DeliveryLocation extends Model implements AuditableContract
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
     *
     * The attribute validation rules
     *
     */
    public static $rules = [
        'org_identifier'        => ['required'],
        'dlvr_loc_identifier'   => ['required'],
        'dlvr_loc_name'         => ['required'],
        'postcode'              => ['required'],
        'state_identifier'      => ['required'],
        'addr_location'         => ['required'],
        'country_identifier'    => ['required']
    ];

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



    public function delivery_location()
    {
        return $this->belongsTo(DeliveryLocation::class);
    }
}
