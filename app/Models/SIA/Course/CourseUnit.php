<?php

namespace App\Models\SIA\Course;

use Kodeine\Metable\Metable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class CourseUnit extends Model implements AuditableContract
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

    // for meta attributes trait
    // use Metable;

    /**
     *
     * The attribute validation rules
     *
     */
    public static $rules = [
        'code'                              => ['required', 'alpha_num', 'unique:course_units,code'],
        'vet_flag'                          => ['boolean'],
        'unit_type'                         => ['required', 'string'],
        'extra_unit'                        => ['boolean'],
        'description'                       => ['required', 'string'],
        'nominal_hours'                     => ['required', 'numeric'],
        // 'extra_unit_fee'                    => ['bail', 'required_if:extra_unit,1', 'numeric'],
        'assesment_method'                  => ['required', 'filled'],
        'training_method_id'                => ['required', 'numeric'],
        'subject_educ_fld_identifier_id'    => ['required', 'numeric'],
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'course_units';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'vet_flag',
        'unit_type',
        'extra_unit',
        'description',
        'nominal_hours',
        'assesment_method',
        'training_method_id',
        'subject_educ_fld_identifier_id',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['course_id'];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

}
