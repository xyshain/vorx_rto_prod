<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class CertificateIssuance extends Model implements AuditableContract
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

    protected $fillable = [
        'deal_id',
        'user_id',
        'student_completion_id',
        'generated_cert_num',
        'manual_cert_num',
        'issued_date',
        'released_date',
        'date_reissued',
        'expected_course_completion',
        'enrolment_date'
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'certificate_issuance';

    public function deal()
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function completion()
    {
        return $this->belongsTo(StudentCompletion::class, 'student_completion_id');
    }
    public function sent_cert()
    {

        return $this->hasMany(SentCertificate::class, 'certificate_id');
    }
}
