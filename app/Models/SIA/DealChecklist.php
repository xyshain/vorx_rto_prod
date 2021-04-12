<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;

class DealChecklist extends Model
{
    protected $connection = 'SIA';
	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'deals_checklist';

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
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'surname',
        'fname',
    	'proof_name_dob',
	    'proof_citizenship',
	    'proof_address',
	    'proof_concession',
	    'blue_card',
	    'enrolment_form',
	    'date_enrol_received',
        'aiss_check_date',
	    'aiss_result',
	    'ct_units',
	    'claiming_remarks',
	    'vettrack_remarks',
	    'remarks'
 	];

 	public function deal()
    {
        return $this->belongsTo(Deal::class);
    }


}
