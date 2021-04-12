<?php

namespace App\Models\SIA;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Contracts\UserResolver;


class Agent extends Authenticatable implements AuditableContract, UserResolver
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
    protected $fillable = [
        'username', 'password', 'is_active'
    ];

    /**
     *
     * static attribute validation rules
     *
     * @var array
     */

   

    public static $rules = [
        'is_active'     => ['required'],
        'username'      => ['bail', 'required', 'unique:agents,username'],
        'password'      => ['bail', 'required', 'min:8'],
        'firstname'     => ['required'],
        'lastname'      => ['required'],
        'email'         => ['required', 'email', 'unique:agent_emails,email'],
        'email_password'=> ['required'],
    ];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     *
     * Guarded
     *
     */
    protected $guard = 'agent';

    /**
     *
     * Get User email
     *
     */
    public function email()
    {
        return $this->hasOne(AgentEmail::class);
    }

    public function agent_detail()
    {
        return $this->hasOne(AgentDetail::class);
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

      public function student()
    {
        return $this->hasMany(DealDetail::class);
    }
    
    public static function resolveId()
    {
        // dd(Auth::check());
        return Auth::guard('agent')->check() ? Auth::guard('agent')->id() : null;
    }
}