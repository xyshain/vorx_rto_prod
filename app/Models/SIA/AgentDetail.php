<?php

namespace App\Models\SIA;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class AgentDetail extends Authenticatable implements AuditableContract
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

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'office_address', 'fax_number', 'company_name', 'position', 'website', 'agent_type'
    ];


    use SoftDeletes;
    protected $dates = ['deleted_at'];

    /**
     *
     * Get User email
     *
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function contact_method()
    {
        return $this->hasOne(ContactMethod::class, 'id');
    }
    
}