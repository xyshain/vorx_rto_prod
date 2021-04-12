<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use App\Models\Course\Course;

class TimeTable extends Model implements AuditableContract
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

	use Notifiable;
    
    protected $fillable = ['start_time','end_time','start_on','end_on','location','course_id'];

    public static $rules = [
        'start_time'    => ['required'],
        'end_time'     	=> ['required'],
        'start_on'      => ['required', 'date_format:d/m/Y'],
        'end_on'      	=> ['required', 'date_format:d/m/Y'],
        'location'      => ['required'],
        'course_id'     => ['required'],
        'study_mode'     => ['required'],
    ];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

}
