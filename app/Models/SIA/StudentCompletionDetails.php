<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use App\Models\Course\CourseUnit;

class StudentCompletionDetails extends Model implements AuditableContract
{
    //
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

	protected $fillable = ['course_units_id','student_completion_id','completion_status','completion_date','user_id','unit_fee', 'commission_fee', 'redeem','redeem_date', 'start_date','end_date'];   
 	//protected $table = 'student_completion';

    public function student_completion(){
    	return $this->belongsTo(StudentCompletion::class);
    }

    public function units(){
        return $this->belongsTo(CourseUnit::class, 'course_units_id', 'code');
    }

}
