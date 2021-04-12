<?php

namespace App\Models\SIA;

use App\Models\SIA\Course\Course;
use App\Models\SIA\Course\CourseFee;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;

class DealDetail extends Model implements AuditableContract
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
		'agent_id'			=> ['nullable', 'numeric'],
		'eligible'			=> ['present', 'numeric'],
		'course_id'			=> ['required', 'numeric'],
		'course_fee_id'		=> ['required', 'numeric'],
		'stud_referrer'		=> ['nullable', 'numeric'],
		'stat_dec_date'		=> ['bail', 'required_with:stat_dec_needed', 'nullable', 'date_format:d/m/Y'],
		'stat_dec_needed'	=> ['boolean'],
		'aiss_check_date'	=> ['nullable', 'date_format:d/m/Y'],
		'training_mode_id'	=> ['required', 'numeric'],
	];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'deal_details';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['eligible', 'aiss_check_date', 'stat_dec_needed', 'stat_dec_date', 'stud_referrer', 'training_mode_id', 'delivery_mode_id', 'predominant_delivery_mode_id'];

    public function deal() {
    	return $this->belongsTo(Deal::class, 'deal_id');
    }

    public function course()
    {
    	return $this->belongsTo(Course::class);
    }

    public function course_fee()
    {
    	return $this->belongsTo(CourseFee::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function training_mode()
    {
        return $this->belongsTo(TrainingMode::class);
    }
}
