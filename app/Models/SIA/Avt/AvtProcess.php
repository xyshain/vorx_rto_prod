<?php

namespace App\Models\Avt;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class AvtProcess extends Model implements AuditableContract
{
    use Auditable;

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
    protected $table = 'avt_process';

    protected $fillable = ['process', 'dateFrom', 'dateTo', 'user', 'status_id'];

    public function avt_status()
    {
    	return $this->belongsTo(AvtStatus::class, 'status_id');
    }

    public function users()
    {
    	return $this->belongsTo(User::class, 'user');
    }

}
