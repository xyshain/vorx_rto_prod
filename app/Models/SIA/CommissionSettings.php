<?php

namespace App\Models\SIA;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Course\Course;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class CommissionSettings extends Model implements AuditableContract
{
    use Notifiable;
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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'agent_id', 'user_id', 'course_id', 'course_fee_type', 'commission_value', 'commission_type_id', 'remarks'];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commission_type()
    {
        return $this->belongsTo(CommissionType::class);
    }

    // public function course_fee()
    // {
    //     return $this->belongsTo(CourseFeeType::class, 'course_fee_type', 'name');
    // }

}
