<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Collection extends Model implements AuditableContract
{
    //

    use SoftDeletes,Auditable;

    protected $fillable = [
        'student_id',
        'student_course_id',
        'transaction_code',
        'payment_schedule_template_id',
        'note',
        'payment_date',
        'amount',
        'pre_deduc_comm',
        'verified',
        'agent_id',
        'remakrs'
    ];

    public function payment_details(){
        return $this->hasMany(FundedStudentPaymentDetails::class);
    }

    public function payment_sched(){
        return $this->belongsTo(PaymentScheduleTemplate::class);
    }


    public function agent(){
        return $this->belongsTo(AgentDetail::class);
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($collections) {
            $collections->payment_details()->each(function ($collection) {
                $collection->delete();
            });
           
        });
    }
}
