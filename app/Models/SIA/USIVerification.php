<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class USIVerification extends Model implements AuditableContract
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

    //
    protected $table = 'usiverifications';

    protected $fillable = ['usi','student_id','user_id'];

    public function user(){
    	return $this->belongsTo(User::class);
    }
}
