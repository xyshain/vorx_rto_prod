<?php

namespace App\Models\SIA;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Ultraware\Roles\Traits\HasRoleAndPermission as HasRoleAndPermissionTrait;
use Ultraware\Roles\Contracts\HasRoleAndPermission as HasRoleAndPermissionContract;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Contracts\UserResolver;
use Auth;

class User extends Authenticatable implements HasRoleAndPermissionContract, AuditableContract, UserResolver
{
    use Notifiable;
    use HasRoleAndPermissionTrait;
    use Auditable;

    protected $connection = 'SIA';

    /**
     *
     * static attribute validation rules
     *
     * @var array
     */
    public static $rules = [
        'role'          => ['required'],
        'is_active'     => ['required'],
        'username'      => ['bail', 'required', 'unique:users,username'],
        'password'      => ['bail', 'required', 'min:8'],
        'firstname'     => ['required'],
        'lastname'      => ['required'],
        'department_id' => ['required'],
        'email'         => ['required', 'email', 'unique:user_emails,email'],
        'email_password'=> ['required'],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname', 'lastname', 'username', 'password', 'department_id', 'is_active'
    ];

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
    protected $guarded = [];


    /**
     *
     * resolver
     *
     */
    public static function resolveId()
    {
        return Auth::check() ? Auth::user()->getAuthIdentifier() : null;
    }
    /**
     *
     * Get User email
     *
     */
    public function email()
    {
        return $this->hasOne(Email::class);
    }

    /**
     *
     * Get department
     *
     */
    public function department()
    {
        return $this->belongsTo(Departments::class);
    }

    /**
     *
     * Person Model Relation
     *
     */
    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function taskboard(){
        return $this->hasOne(TaskBoard::class);
    }

    /**
     *
     * User Email Model Relation
     *
     */
    public function user_email(){
        return $this->hasOne(UserEmail::class);
    }
    public function email_setting(){
        return $this->hasOne(EmailSettings::class,'user_id');
    }
}
