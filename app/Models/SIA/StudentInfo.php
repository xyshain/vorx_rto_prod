<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class StudentInfo extends Model implements AuditableContract
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
        
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'student_info';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'person_id',
        'at_school_flag',
        'lang_identifier',
        'disability_flag',
        'year_hs_completed',
        'uniq_stud_identifier',
        'prior_educ_achieve',
        'disbility_list',
        'student_id',
        'ind_status_identifier',
        'prof_spk_eng_identifier',
        'lbr_force_status_identifier',
        'highest_school_lvl_comp_identifier'
        ];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function person()
    {
    	return $this->belongsTo(Person::class, 'person_id');
    }
    
}
