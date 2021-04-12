<?php

namespace App\Models\Avt;

use Illuminate\Database\Eloquent\Model;
use DB;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Avt30 extends Model implements AuditableContract
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

    protected $table = 'avt_30_program_file';

    protected $fillable = [
     	'prg_identifier',
     	'prg_name',
     	'nominal_hours',
     	'prg_recog_identifier',
     	'prg_educ_lvl_identifier',
     	'prg_educ_fld_identifier',
     	'anzsco_identifier',
     	'vet_flag',
        'avt_process_id'
    ];

    public function get_data_avt30($dateFrom,$dateTo)
     {

        return collect( json_decode( json_encode( DB::select(DB::raw("SELECT 
            vrx_courses.code AS prg_identifier,

            vrx_courses.name AS prg_name,
            avt.nominal_hours,
            recog.value AS prg_recog_identifier_id,
            lvl.value AS prg_lvl_of_educ_identifier_id,
            qlf.value AS qlf_field_of_educ_identifier_id,
            anzsco.value AS anzsco_identifier_id,
            avt.vet_flag_status
            FROM vrx_student_completion 
            INNER JOIN vrx_student_completion_details ON vrx_student_completion_details.student_completion_id = vrx_student_completion.id
            INNER JOIN vrx_courses ON vrx_courses.code = vrx_student_completion.course_code
            INNER JOIN vrx_course_avt_details AS avt ON (avt.course_id = vrx_courses.id)
            INNER JOIN avt_prg_recog_identifier recog ON (recog.id = avt.prg_recog_identifier_id)
            INNER JOIN avt_prg_lvl_of_educ_identifier lvl ON (lvl.id = avt.prg_lvl_of_educ_identifier_id)
            INNER JOIN avt_qlf_field_of_educ_identifier qlf ON (qlf.id = avt.qlf_field_of_educ_identifier_id)
            INNER JOIN avt_anzsco_identifier anzsco ON (anzsco.id = avt.anzsco_identifier_id)
            INNER JOIN vrx_persons ON vrx_persons.id = vrx_student_completion.persons_id
            INNER JOIN vrx_party ON vrx_party.id = vrx_persons.party_id
            INNER JOIN vrx_deals ON vrx_deals.party_id = vrx_party.id
            INNER JOIN vrx_enrolments ON (vrx_deals.id = vrx_enrolments.deal_id)
            INNER JOIN vrx_student_info ON (vrx_student_info.person_id = vrx_persons.id)
            WHERE vrx_student_completion_details.end_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
            -- AND vrx_training_schedule.status_id IN (6,2,3)
            AND vrx_student_info.uniq_stud_identifier IS NOT NULL
            AND vrx_student_completion.completion_status_id IN (3)
            AND vrx_student_completion.deleted_at IS NULL
            AND vrx_deals.deleted_at IS NULL
            AND vrx_courses.code != '1111'
            GROUP BY vrx_courses.code
            ORDER BY vrx_student_completion.id DESC")) ), true ) );


     }
    
}
