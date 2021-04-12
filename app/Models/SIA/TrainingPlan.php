<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use App\Models\Course\Course;

class TrainingPlan extends Model implements AuditableContract
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
    protected $table = 'training_plan';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['deal_id','schedule_id','course_id', 'training_plan', 'lln', 'rpl'];

    use SoftDeletes;
    protected $dates = ['deleted_at'];


    public function course()
    {
    	return $this->belongsTo(Course::class, 'course_id');
    }

    public function deal()
    {
    	return $this->belongsTo(Deal::class, 'deal_id');
    }

    public function schedule()
    {
    	return $this->belongsTo(Schedule::class, 'schedule_id');
    }


}
