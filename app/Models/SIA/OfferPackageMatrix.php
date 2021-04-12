<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use App\Models\Course\Course;

class OfferPackageMatrix extends Model implements AuditableContract
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

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'offer_package_matrix';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
    	'course_code', 
	    'cricos_code',
	    'wk_duration',
	    'wk_duration_package',
	    'oh_tuition_individual',
	    'oh_tuition_package',
	    'os_tuition_individual',
	    'os_tuition_package',
	    'material_fees',
	    'enrolment_fee',
	    'location'
	];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_code', 'code');
    }

    public function state()
    {
        return $this->belongsTo(StateIdentifier::class, 'location', 'state_key');
    }
}
