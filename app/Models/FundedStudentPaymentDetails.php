<?php

namespace App\Models;
use App\Models\Student\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;


class FundedStudentPaymentDetails extends Model implements AuditableContract
{
    
    use SoftDeletes;
    use Auditable;

    protected $table = "funded_student_payment_details";
    protected $dates = ['created_at'];

    protected $fillable = [
        'student_id',
        'student_course_id',
        'offer_letter_course_detail_id',
        'stripe_payments_id',
        'customer_id',
        'transaction_code',
        'note',
        'payment_date',
        'amount',
        'payment_method_id',
        'user_id',
        'pre_deduc_comm',
        'comm_release_status',
        'refunded',
        'sent_receipt'
    ];

    public function attachment(){
        return $this->belongsTo(PaymentAttachment::class,'id', 'funded_student_payment_detail_id');
    }

    public function user(){
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function getTotalPaymentAttribute()
    {
        return $this->sum('amount');
    }
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id','student_id');
    }
    public function funded_student_course()
    {
        return $this->belongsTo(FundedStudentCourse::class, 'student_course_id');
    }
    public function offer_detail()
    {
        return $this->belongsTo(OfferLetterCourseDetail::class, 'offer_letter_course_detail_id');
    }
    public function commission(){
        return $this->hasOne(CommissionDetail::class,'payment_id');
    }

}
