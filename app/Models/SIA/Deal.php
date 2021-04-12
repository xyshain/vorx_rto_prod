<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Deal extends Model implements AuditableContract
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

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'deals';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['source_id', 'deal_types_id', 'status_id', 'creator_id'];

    public function party()
    {
    	return $this->belongsTo(Party::class, 'party_id');
    }
    
    public function deal_detail()
    {
    	return $this->hasOne(DealDetail::class, 'deal_id');
    }

    public function source()
    {
        return $this->belongsTo(DealSource::class);
    }

    public function type()
    {
        return $this->belongsTo(DealType::class, 'deal_type_id');
    }

    public function status()
    {
        return $this->belongsTo(DealStatus::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function opportunity()
    {
        return $this->hasOne(Opportunity::class);
    }

    public function training_plan()
    {
        return $this->hasOne(TrainingPlan::class, 'deal_id');
    }

    public function stud_sched()
    {
        return $this->hasOne(StudTrainSched::class,'deal_id');
    }

    public function checklist()
    {
        return $this->hasOne(Checklist::class,'deal_id');
    }

    public function deal_checklist()
    {
        return $this->hasOne(DealChecklist::class,'deal_id');
    }

    public function certificate_issued(){
        return $this->hasOne(SentCertificate::class,'deal_id');
    }

    public function certificate(){
        return $this->hasOne(CertificateIssuance::class,'deal_id');
    }

    public function enrolment()
    {
        return $this->hasOne(Enrolment::class, 'deal_id');
    }
}
