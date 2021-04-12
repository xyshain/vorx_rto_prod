<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use App\Models\Course\Course;

class Schedule extends Model implements AuditableContract
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
    protected $table = 'training_schedule';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['code','trainer_id','course_id', 'state_id', 'time_table_id', 'city_id', 'start_date', 'end_date', 'suburb_id', 'status_id', 'venue', 'address','train_org_dlvr_loc_identifier'];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function getCourse()
    {
    	return $this->belongsTo( Course::class, 'course_id' );
    }

    public function getStatus()
    {
    	return $this->belongsTo( TrainingStatus::class, 'status_id' );
    }

    public function getUser()
    {
    	return $this->belongsTo( User::class, 'trainer_id' );
    }

    public function getDeal()
    {
    	return $this->belongsToMany(Deal::class, 'stud_train_sched','schedule_id','deal_id');
    }

    public function checkDeal()
    {
    	return $this->hasMany(Deal::class, 'stud_train_sched','schedule_id','deal_id');
    }

    public function getDealDets()
    {
    	return $this->belongsTo(DealDetail::class, 'deal_id');
    }

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function mode()
    {
        return $this->belongsTo(TrainingMode::class, 'training_mode_id');
    }

    public function attendance()
    {
        return $this->hasOne(Attendance::class, 'schedule_id');
    }

    public function time_table()
    {
        return $this->belongsTo(TimeTable::class);
    }

    public function delivery_location()
    {
        return $this->belongsTo(DeliveryLocation::class, 'train_org_dlvr_loc_identifier', 'dlvr_loc_identifier');
    }

    /*public static function boot()
    {
        parent::boot();

        // delete action
        self::deleted( function( Course $course ) {
            
            // remove related avt item
            $course->course_avt_details->delete();

            // remove related course fee item
            foreach ($course->course_fee->where('course_id', $course->id)->get() as $cf) {
                $cf->delete();
            }

        });

        // restore action
        self::restored( function( Course $course ) {

            // restore related avt item
            $course->course_avt_details->restore();

            // restore related course fee item
            foreach ($course->course_fee->where('course_id', $course->id)->get() as $cf) {
                $cf->restore();
            }
        });
    }*/

}
