<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use App\Models\Course\Course;

class CourseProspectus extends Model implements AuditableContract
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
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'units',
        'location',
        'is_active',
        'course_id'
    ];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
