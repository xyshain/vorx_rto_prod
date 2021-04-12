<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class SentCertificate extends Model implements AuditableContract
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
        'certificate_id',
        'student_completion_id',
        'soa_number',
    ];

      use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sent_certificate';

    public function deal(){
        return $this->belongsTo(Deal::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function completion(){
        return $this->belongsTo(CertificateIssuance::class, 'certificate_id');
    }

}
