<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use App\Models\Course\Course;

class StudentCompletion extends Model implements AuditableContract
{
    //
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


    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = ['course_code', 'persons_id','user_id','course_id','completion_status_id','completion_date', 'agent_id', 'train_org_dlvr_loc_identifier'];

    protected $table = 'student_completion';

    public function person(){
    	return $this->hasOne(Person::class, 'id', 'persons_id');
    }

    public function course(){
    	return $this->belongsTo(Course::class, 'course_code', 'code');
    }
    public function user(){
    	return $this->belongsTo(User::class);
    }

    public function details(){
    	return $this->hasMany(StudentCompletionDetails::class,'student_completion_id')->orderBy('completion_status_id', 'DESC')->orderBy('start_date', 'DESC');
    }

    public function cert_issuance(){
        return $this->hasOne(CertificateIssuance::class, 'student_completion_id');
    }

    public function training_delivery_loc(){
        return $this->belongsTo(TrainingDeliveryLoc::class, 'train_org_dlvr_loc_identifier', 'dlvr_loc_identifier');
    }

    // ---------------------- //
    //       MUTATORS        //
    // ---------------------- //
    public function getCompletionStatusAttribute(){
        // return "test";
        $status = collect( \DB::select(\DB::raw('SELECT * FROM avt_completion_status compl WHERE compl.id ='. $this->completion_status_id)) )->pluck('status');
        return $status[0];
    }

    public function getCompletionDateAuAttribute(){
        if($this->completion_date){
            return \Carbon\Carbon::createFromFormat('Y-m-d', $this->completion_date)->format('d/m/Y');
        }else{
            return '';
        }
    }
}
