<?php

namespace App\Models\SIA\Course;

use Kodeine\Metable\Metable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class CourseFee extends Model implements AuditableContract
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

    // soft deletes
    use SoftDeletes;

    /**
     *
     * static attribute validation rules
     *
     * @var array
     */
    public static $rules = [
        'full_course'           => ['nullable', 'required_without_all:non_concessional,concessional', 'sometimes', 'numeric'],
        'concessional'          => ['nullable', 'required_without_all:full_course,non_concessional', 'sometimes', 'numeric'],
        'non_concessional'      => ['nullable', 'required_without_all:concessional,full_course', 'sometimes', 'numeric'],
        // for dynamic course fee fields
        'fee.others.name'       => ['nullable', 'array'],
        'fee.others.value'      => ['nullable', 'array'],
        'fee.others.name.*'     => ['required', 'string'],
        'fee.others.value.*'    => ['required', 'numeric']
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'course_fees';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['course_code', 'location'];

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

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

}
