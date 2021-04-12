<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Enrolment extends Model implements AuditableContract
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
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'deal_id',
        'funding_source_national',
        'commencing_program_id',
        'training_contract_identifier',
        'client_identifier_appren',
        'study_reason_id',
        'specific_funding_id',
        'funding_source_state_training_authority',
        'pur_contract_identifier',
        'pur_contract_sched_identifier',
        'assoc_course_identifier',
        'full_time_learning_option',
        'time_table_id',
        'survey_contact_status',
        'time_table'
    ];

    public static $rules = [
        'funding_source_national'                   => ['required_if:deal_type_id,3'],
        'commencing_program_id'                     => ['required_if:deal_type_id,3'],
        'study_reason_id'                           => ['required_if:deal_type_id,3']
    ];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }


}
