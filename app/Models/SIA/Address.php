<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Address extends Model implements AuditableContract
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
	public static $rules =[
		'postcode' => ['required_unless:state_identifier,10'],
		'flat_unit' => ['nullable', 'numeric'],
		'street_num' => ['required_with:street_name', 'string'],
		'street_name' => ['required', 'string'],
		'postal_dlvr_box' => ['nullable', 'string'],
		'state_identifier' => ['required', 'numeric'],
		'bldg_property_name' => ['nullable', 'string'],
		'location_suburb_town' => ['required_unless:state_identifier,10'],
		'suburb_locality_town' => ['required_unless:state_identifier,10'],
	];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'address';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
    	'postcode',
    	'flat_unit',
    	'street_num',
    	'street_name',
    	'postal_dlvr_box',
    	'state_identifier',
    	'bldg_property_name',
    	'location_suburb_town',
    	'suburb_locality_town',
	];

    public function party()
    {
    	return $this->belongsTo(Party::class);
    }

    public function stateidentifier(){
        return $this->belongsTo(StateIdentifier::class,'id');
    }
}
