<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Person extends Model implements AuditableContract
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


    use SoftDeletes;
    protected $dates = ['deleted_at'];

     /**
     *
     * static attribute validation rules
     *
     * @var array
     */
    public static $rules = [
        'firstname'     => ['required', 'string'],
        'middlename'    => ['nullable','string'],
        'lastname'      => ['required', 'string'],
        'date_of_birth' => ['required','date_format:d/m/Y'],
        'gender'        => ['present'],
    ];

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'persons';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['firstname', 'middlename', 'lastname', 'date_of_birth', 'gender', 'visa_inquery', 'visa_status'];

    //
    public function person_type()
    {
        return $this->belongsTo(PersonType::class,'person_type');
    }

    public function student_info()
    {
        return $this->hasOne(StudentInfo::class,'person_id');
    	// return $this->belongsTo(StudentInfo::class,'persons_id');
    }

    public function party()
    {
    	return $this->belongsTo(Party::class);
    }
     public function emergency()
    {
        return $this->hasOne(EmergencyContact::class);
    }
    public function student_completion(){
        return $this->hasMany(StudentCompletion::class,'persons_id');
    }
    public function clientinfo(){
        return $this->hasOne(ClientForm::class);
    }
}
