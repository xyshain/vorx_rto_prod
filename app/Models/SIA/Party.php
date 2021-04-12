<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Party extends Model implements AuditableContract
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


    /**
     *
     * static attribute validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => ['required', 'string']
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'party';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    //
    public function party_type()
    {
    	return $this->belongsTo(PartyType::class);
    }

    public function person()
    {
    	return $this->hasOne(Person::class);
    }

    public function organisation()
    {
    	return $this->hasMany(Organisations::class);
    }

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function address()
    {
        return $this->hasOne(Address::class);
    }

    public function contact()
    {
        return $this->hasOne(ContactInfo::class);
    }

    public function deal()
    {
        return $this->hasOne(Deal::class,'party_id');
    }

    public function int_detail()
    {
        return $this->hasOne(InternationalDetail::class, 'party_id');
    }
}
