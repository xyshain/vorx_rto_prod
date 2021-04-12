<?php

namespace App\Models\SIA\Course;

use Kodeine\Metable\Metable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use App\Models\CourseProspectus;
use App\Models\OfferPackageMatrix;
use App\Models\CommissionSettings;

class Course extends Model implements AuditableContract
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
        'name'              => ['required'],
        'code'              => ['required', 'alpha_num'],
        'target_enrolee'    => ['required', 'numeric'],
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'courses';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['name','code','target_enrolee'];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public static function boot()
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
    }

    /**
     *
     * Relationship function for CourseFee Model
     *
     */
    public function course_fee()
    {
        return $this->hasOne( CourseFee::class );
    }

    /**
     *
     * Relationship function for CourseAvtDetail Model
     *
     */
    public function course_avt_details()
    {
        return $this->hasOne( CourseAvtDetail::class );
    }

    /**
     *
     * Reationship function for Course Units
     *
     */
    public function course_units()
    {
        return $this->hasMany( CourseUnit::class );
    }

    public function course_prospectus()
    {
        return $this->hasOne( CourseProspectus::class );
    }

    public function studentcompletion(){
        return $this->hasOne( StudentCompletion::class ,'course_code');
    }

    public function matrix(){
        return $this->hasMany( OfferPackageMatrix::class, 'course_code', 'code', 'offer_package_matrix' );
    }

}
