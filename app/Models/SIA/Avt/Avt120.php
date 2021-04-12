<?php

namespace App\Models\Avt;

use Illuminate\Database\Eloquent\Model;
use DB;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Avt120 extends Model implements AuditableContract
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

    protected $table = 'avt_120_enrolment_file';

     protected $fillable = [
     	'train_org_dlvr_loc_identifier',
     	'client_identifier',
     	'subj_identifier',
     	'prg_identifier',
     	'act_start_date',
     	'act_end_date',
     	'dlvr_mode_identifier',
     	'outcome_identifier_ntl',
     	'sched_hours',
     	'funding_src_ntl',
     	'commence_prg_identifier',
     	'training_contract_identifier',
     	'client_identifier_appren',
     	'study_reason_identifier',
     	'vet_in_school_flag',
     	'spec_fund_identifier',
     	'outcome_identifier_training_org',
     	'funding_source_sta',
     	'client_tuition_fee',
     	'fee_exempt_cs_type_identifier',
     	'pur_contract_sched_identifier',
     	'hours_attended',
     	'assoc_course_identifier',
      'full_time_learning_option',
      'school_type_id',
      'training_org_id',
      'delivery_mode_id',
      'predominant_delivery_mode_id',
      'avt_process_id',
      'isValid',
      'user'
     ];

     public function get_data_avt120($dateFrom,$dateTo,$ErrorClient, $getClient = null)
     {

        $eclient = '';

        if(!empty($ErrorClient)){
          $eclient = 'AND vrx_persons.id NOT IN ('. implode(',', $ErrorClient) .')';
        }
        
        if($getClient){
          $gclient = 'AND vrx_persons.id IN ('. implode(',', $getClient) .')';
        }

        // return collect( json_decode( json_encode( DB::select(DB::raw("SELECT 
        //        DATE_FORMAT(`vrx_training_schedule`.start_date,'%d%m%Y') AS act_start_date,
        //        DATE_FORMAT(`vrx_training_schedule`.end_date,'%d%m%Y') AS act_end_date,
        //        vrx_persons.id AS client_identifier,
        //        vrx_courses.code prg_identifier,
        //        avt_delivery_types.value dlvr_mode_identifier,
        //        IF(vrx_course_avt_details.vet_flag_status = 1, 'Y', 'N') AS vet_in_school_flag,
        //        vrx_training_schedule.train_org_dlvr_loc_identifier train_org_dlvr_loc_identifier,
        //        vrx_course_avt_details.nominal_hours sched_hours,
        //        vrx_training_plan.training_plan,
        //        vrx_course_fees_meta.value fee,
        //        IF(vrx_course_fees_meta.key = 'concessional', 'C', IF(vrx_course_fees_meta.key = 'non_concessional', 'N', '')) AS fee_exempt_cs_type_identifier,
        //        vrx_enrolments.*,
        //        (SELECT org_identifier FROM `vrx_organisation_info`) AS training_org_id,
        //        (SELECT org_type_identifier FROM `vrx_organisation_info`) AS school_type_id,
        //        vrx_deal_details.delivery_mode_id,
        //        vrx_deal_details.predominant_delivery_mode_id
        //        -- (SELECT SUM( vrx_course_units.`nominal_hours`) FROM `vrx_course_units` WHERE `vrx_course_units`.course_id = vrx_courses.id) AS hours_attended
        //        -- *
        //        FROM vrx_training_schedule
        //         INNER JOIN vrx_users ON (vrx_training_schedule.trainer_id = vrx_users.id)
        //         INNER JOIN vrx_courses ON (vrx_training_schedule.course_id = vrx_courses.id)
        //         INNER JOIN vrx_course_avt_details ON (vrx_course_avt_details.course_id = vrx_courses.id)
        //         INNER JOIN vrx_training_status ON (vrx_training_schedule.status_id = vrx_training_status.id) 
        //         INNER JOIN vrx_stud_train_sched ON (vrx_training_schedule.id = vrx_stud_train_sched.schedule_id) 
        //         INNER JOIN vrx_deals ON (vrx_deals.id = vrx_stud_train_sched.deal_id) 
        //         INNER JOIN vrx_deals_meta ON (vrx_deals_meta.deal_id = vrx_deals.id) 
        //         INNER JOIN vrx_course_fees ON (vrx_course_fees.course_id = vrx_courses.id) 
        //         INNER JOIN vrx_course_fees_meta ON (vrx_course_fees_meta.key = vrx_deals_meta.value AND vrx_course_fees_meta.course_fee_id = vrx_course_fees.id) 
        //         INNER JOIN vrx_enrolments ON (vrx_enrolments.deal_id = vrx_deals.id) 
        //         INNER JOIN vrx_training_plan ON (vrx_training_plan.deal_id = vrx_deals.id) 
        //         INNER JOIN vrx_deal_details ON (vrx_deals.id = vrx_deal_details.deal_id) 
        //         INNER JOIN avt_delivery_types ON (vrx_deal_details.training_mode_id = avt_delivery_types.id) 
        //         INNER JOIN vrx_party ON (vrx_party.id = vrx_deals.party_id) 
        //         INNER JOIN vrx_persons ON (vrx_persons.party_id = vrx_party.id) 
        //     WHERE
        //       vrx_training_schedule.start_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
        //       AND vrx_training_schedule.status_id IN (6,2,3)
        //       AND vrx_deals_meta.key = 'course_fee'  
        //       AND vrx_deals.deleted_at IS NULL 
        //       AND vrx_training_plan.training_plan IS NOT NULL 
        //       {$eclient}
        //     ORDER BY
        //       vrx_training_schedule.start_date")) ), true ) );

        return collect( json_decode( json_encode( DB::select(DB::raw("SELECT
        DATE_FORMAT(`vrx_student_completion_details`.start_date,'%d%m%Y') AS act_start_date,
        DATE_FORMAT(`vrx_student_completion_details`.end_date,'%d%m%Y') AS act_end_date,
        CONCAT('2019', vrx_student_info.uniq_stud_identifier) AS client_identifier,
        vrx_courses.code prg_identifier,
        vrx_course_units.nominal_hours AS hours,
        vrx_student_completion_details.course_units_id AS subj_identifier,
        avt_completion_status.value AS outcome_identifier_ntl,
        avt_delivery_types.value dlvr_mode_identifier,
        vrx_student_info.uniq_stud_identifier AS usi,
        IF(vrx_course_avt_details.vet_flag_status = 1, 'Y', 'N') AS vet_in_school_flag,
        -- vrx_training_schedule.train_org_dlvr_loc_identifier train_org_dlvr_loc_identifier,
        vrx_student_completion.train_org_dlvr_loc_identifier,
        vrx_course_avt_details.nominal_hours sched_hours,
        -- vrx_training_plan.training_plan,
        vrx_course_fees_meta.value fee,
        IF(vrx_course_fees_meta.key = 'concessional', 'C', IF(vrx_course_fees_meta.key = 'non_concessional', 'N', '')) AS fee_exempt_cs_type_identifier,
        vrx_enrolments.*,
        (SELECT org_identifier FROM `vrx_organisation_info`) AS training_org_id,
        (SELECT org_type_identifier FROM `vrx_organisation_info`) AS school_type_id,
        -- vrx_deal_details.delivery_mode_id,
        IF(avt_completion_status.value = '20', 'YNN', 'NNN') AS delivery_mode_id,
        vrx_deal_details.predominant_delivery_mode_id

        FROM vrx_student_completion

        INNER JOIN vrx_student_completion_details ON vrx_student_completion_details.student_completion_id = vrx_student_completion.id
        INNER JOIN vrx_courses ON vrx_courses.code = vrx_student_completion.course_code

        INNER JOIN vrx_course_units ON vrx_course_units.code = vrx_student_completion_details.course_units_id
        INNER JOIN vrx_course_avt_details ON (vrx_course_avt_details.course_id = vrx_courses.id)
        INNER JOIN vrx_persons ON vrx_persons.id = vrx_student_completion.persons_id
        INNER JOIN vrx_party ON vrx_party.id = vrx_persons.party_id
        INNER JOIN vrx_deals ON vrx_deals.party_id = vrx_party.id
        --
        INNER JOIN vrx_student_info ON (vrx_student_info.person_id = vrx_persons.id)
        INNER JOIN vrx_deals_meta ON (vrx_deals_meta.deal_id = vrx_deals.id) 
        INNER JOIN vrx_course_fees ON (vrx_course_fees.course_id = vrx_courses.id)
        LEFT JOIN vrx_course_fees_meta ON (vrx_course_fees_meta.key = vrx_deals_meta.value AND vrx_course_fees_meta.course_fee_id = vrx_course_fees.id) 
        INNER JOIN vrx_enrolments ON (vrx_enrolments.deal_id = vrx_deals.id) 
        -- INNER JOIN vrx_training_plan ON (vrx_training_plan.deal_id = vrx_deals.id) 
        INNER JOIN vrx_deal_details ON (vrx_deals.id = vrx_deal_details.deal_id) 
        INNER JOIN avt_delivery_types ON (vrx_deal_details.training_mode_id = avt_delivery_types.id)
        INNER JOIN avt_completion_status ON (vrx_student_completion_details.completion_status_id = avt_completion_status.id)
        -- WHERE vrx_student_completion.train_org_dlvr_loc_identifier IS NOT NULL 
        WHERE

        vrx_student_completion_details.start_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
        AND vrx_student_completion_details.end_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
        -- AND vrx_student_completion.train_org_dlvr_loc_identifier IS NOT NULL
        AND vrx_student_completion.completion_status_id = 3
        AND vrx_student_completion.deleted_at IS NULL
        -- AND vrx_student_completion_details.status_id IN (6,2,3)
        AND vrx_deals_meta.key = 'course_fee'  
        AND vrx_deals.deleted_at IS NULL 
        AND vrx_student_info.uniq_stud_identifier IS NOT NULL
        AND vrx_courses.code != '1111'

          -- vrx_student_completion_details.start_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
          -- AND vrx_student_completion_details.end_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
            -- AND vrx_student_completion.train_org_dlvr_loc_identifier IS NOT NULL
            -- AND vrx_student_completion.completion_status_id IN (3)
            -- AND vrx_student_completion_details.status_id IN (6,2,3)
          -- AND vrx_deals_meta.key = 'course_fee'  
          -- AND vrx_deals.deleted_at IS NULL 
          -- AND vrx_student_info.uniq_stud_identifier IS NOT NULL
          -- AND vrx_courses.code != '1111'
          -- AND vrx_training_plan.training_plan IS NOT NULL 
          {$eclient}
        GROUP BY
        client_identifier, usi, subj_identifier
        ORDER BY
          vrx_student_completion_details.start_date DESC")) ), true ) );
     }

     public function get_enrolment($deal_id)
     {
        return collect( json_decode( json_encode( DB::select(DB::raw("SELECT 
               vrx_persons.id AS client_identifier,
               vrx_courses.code prg_identifier,
               trainer.name train_contact_identifier,
               avt_delivery_types.description dlvr_mode_identifier,
               IF(vrx_course_avt_details.vet_flag_status = 1, 'Y', 'N') AS vet_in_school_flag,
               vrx_training_schedule.train_org_dlvr_loc_identifier train_org_dlvr_loc_identifier,
               vrx_course_avt_details.nominal_hours sched_hours,
               (SELECT SUM( vrx_course_units.`nominal_hours`) FROM `vrx_course_units` WHERE `vrx_course_units`.course_id = vrx_courses.id) AS hours_attended

               FROM vrx_training_schedule
               INNER JOIN vrx_users ON (vrx_training_schedule.trainer_id = vrx_users.id)
               INNER JOIN vrx_party trainer ON (trainer.id = vrx_users.party_id)
               INNER JOIN vrx_courses ON (vrx_training_schedule.course_id = vrx_courses.id)
               -- INNER JOIN vrx_course_units ON (vrx_course_units.course_id = vrx_courses.id)
               INNER JOIN vrx_course_avt_details ON (vrx_course_avt_details.course_id = vrx_courses.id)
               -- INNER JOIN vrx_course_fee_types ON (vrx_course_fee_types.course_id = vrx_courses.id)
               INNER JOIN vrx_stud_train_sched ON (vrx_training_schedule.id = vrx_stud_train_sched.schedule_id)
               INNER JOIN vrx_deals ON (vrx_deals.id = vrx_stud_train_sched.deal_id)
               INNER JOIN vrx_deal_details ON (vrx_deals.id = vrx_deal_details.deal_id)
               INNER JOIN vrx_enrolments ON (vrx_enrolments.deal_id = vrx_deals.id)
               INNER JOIN avt_delivery_types ON (vrx_deal_details.training_mode_id = avt_delivery_types.id)
               INNER JOIN vrx_party ON (vrx_party.id = vrx_deals.party_id)
               INNER JOIN vrx_persons ON (vrx_persons.party_id = vrx_party.id)
               -- INNER JOIN vrx_student_info ON (vrx_student_info.person_id = vrx_persons.id)
               -- INNER JOIN vrx_country ON (vrx_address.party_id = vrx_country.id)
            WHERE
              vrx_deals.id = ".$deal_id."
              AND vrx_deals.deleted_at IS NULL
            ORDER BY
              vrx_training_schedule.end_date")) ), true ) );
     }
    
}
