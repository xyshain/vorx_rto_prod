<?php

namespace App\Models\SIA\Course;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class CourseAvtDetail extends Model implements AuditableContract
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
        'nominal_hours'                     => ['required', 'numeric'],
        'prg_recog_identifier_id'           => ['required', 'numeric'],
        'prg_lvl_of_educ_identifier_id'     => ['required', 'numeric'],
        'qlf_field_of_educ_identifier_id'   => ['required', 'numeric'],
        'anzsco_identifier_id'              => ['required', 'numeric'],
        'vet_flag_status'                   => ['boolean'],
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'course_avt_details';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nominal_hours','prg_recog_identifier_id','prg_lvl_of_educ_identifier_id','qlf_field_of_educ_identifier_id','anzsco_identifier_id','vet_flag_status'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['course_id'];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    /**
     *
     * Course
     *
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
