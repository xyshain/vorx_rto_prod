<?php

namespace App\Models\Avt;

use Illuminate\Database\Eloquent\Model;
use DB;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Avt60 extends Model implements AuditableContract
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

    protected $table = 'avt_60_subject_file';

    protected $fillable = [
     	'subj_flag',
     	'subj_identifier',
     	'subj_name',
     	'subj_educ_fld_identifier',
     	'vet_flag',
     	'nominal_hours',
        'avt_process_id',
        'isValid',
        'user'
    ];

    public function get_data_avt60($dateFrom,$dateTo)
    {   
        $unit = [];

        // $prospectus = DB::table('course_prospectuses')
        //                 ->join('training_schedule', 'course_prospectuses.course_id', '=', 'training_schedule.course_id')
        //                 ->whereBetween('training_schedule.start_date', [$dateFrom,$dateTo])
        //                 ->where('training_schedule.status_id', 6)
        //                 ->groupBy('course_prospectuses.course_id')
        //                 ->get();

        /*$tPlan = json_decode( json_encode( DB::select(DB::raw("SELECT 
               vrx_training_plan.training_plan
              
               FROM vrx_training_schedule
                INNER JOIN vrx_users ON (vrx_training_schedule.trainer_id = vrx_users.id)
                INNER JOIN vrx_courses ON (vrx_training_schedule.course_id = vrx_courses.id)
                INNER JOIN vrx_course_avt_details ON (vrx_course_avt_details.course_id = vrx_courses.id)
                INNER JOIN vrx_training_status ON (vrx_training_schedule.status_id = vrx_training_status.id) 
                INNER JOIN vrx_stud_train_sched ON (vrx_training_schedule.id = vrx_stud_train_sched.schedule_id) 
                INNER JOIN vrx_deals ON (vrx_deals.id = vrx_stud_train_sched.deal_id) 
                -- INNER JOIN vrx_deals_meta ON (vrx_deals_meta.deal_id = vrx_deals.id) 
                -- INNER JOIN vrx_course_fees ON (vrx_course_fees.course_id = vrx_courses.id) 
                -- INNER JOIN vrx_course_fees_meta ON (vrx_course_fees_meta.key = vrx_deals_meta.value AND vrx_course_fees_meta.course_fee_id = vrx_course_fees.id) 
                -- INNER JOIN vrx_enrolments ON (vrx_enrolments.deal_id = vrx_deals.id) 
                INNER JOIN vrx_training_plan ON (vrx_training_plan.deal_id = vrx_deals.id) 
                -- INNER JOIN vrx_deal_details ON (vrx_deals.id = vrx_deal_details.deal_id) 
                -- INNER JOIN avt_delivery_types ON (vrx_deal_details.training_mode_id = avt_delivery_types.id) 
                -- INNER JOIN vrx_party ON (vrx_party.id = vrx_deals.party_id) 
                -- INNER JOIN vrx_persons ON (vrx_persons.party_id = vrx_party.id) 
            WHERE
              vrx_training_schedule.start_date BETWEEN '2018-01-01' AND '2018-06-01'
              AND vrx_training_schedule.status_id IN (6,2,3)
              -- AND vrx_deals_meta.key = 'course_fee'  
              AND vrx_deals.deleted_at IS NULL 
              AND vrx_training_plan.training_plan IS NOT NULL
            ORDER BY
              vrx_training_schedule.start_date")) ), true );


        if(!$tPlan){
            return [];
        }

        foreach ($tPlan as $key => $value) {
            $jtp = json_decode($value['training_plan'],true);
            // dump($jtp);
            foreach ($jtp['plan'] as $v) {

                // var_dump($v['a_start']);
                // var_dump($v['a_end']);
                
                if(strlen($v['a_start']) != 0 || strlen($v['a_end']) != 0){
                    if(!in_array($v['unitCode'], $unit))
                    array_push($unit, $v['unitCode']);
                }
            
            }
            // dd($r);
        }


        // dd($unit);


        // foreach ($unit as $key => $value) {

        $units = DB::table('course_units')
            ->select('course_units.code as subj_identifier', 'course_units.description as subj_name', 'course_units.subject_educ_fld_identifier_id as subj_educ_fld_identifier', 'course_units.vet_flag', 'course_units.nominal_hours')
            ->whereIn('course_units.code', $unit)
            ->get();

            // dd($units);

        // dd($units[0]->subj_identifier);

            // array_push($avt60, $units);
        // }*/

        // return $units;

        return collect( json_decode( json_encode( DB::select(DB::raw("SELECT
            vrx_course_units.code AS subj_identifier, 
            vrx_course_units.description AS subj_name, 
            vrx_course_units.subject_educ_fld_identifier_id AS subj_educ_fld_identifier, 
            vrx_course_units.vet_flag, 
            vrx_course_units.nominal_hours
            FROM vrx_student_completion
            INNER JOIN vrx_student_completion_details ON vrx_student_completion_details.student_completion_id = vrx_student_completion.id
            INNER JOIN vrx_courses ON vrx_courses.code = vrx_student_completion.course_code
            INNER JOIN vrx_course_units ON vrx_course_units.code = vrx_student_completion_details.course_units_id
            INNER JOIN vrx_persons ON vrx_persons.id = vrx_student_completion.persons_id
            INNER JOIN vrx_party ON vrx_party.id = vrx_persons.party_id
            INNER JOIN vrx_deals ON vrx_deals.party_id = vrx_party.id
            INNER JOIN vrx_enrolments ON (vrx_deals.id = vrx_enrolments.deal_id)
            INNER JOIN vrx_student_info ON (vrx_student_info.person_id = vrx_persons.id)
            WHERE vrx_student_completion_details.completion_status_id IN (3,4,6) AND
              vrx_student_completion.completion_status_id = 3 AND
              vrx_student_completion.deleted_at IS NULL AND
              vrx_student_completion_details.end_date BETWEEN '".$dateFrom."' AND '".$dateTo."' AND
              vrx_student_info.uniq_stud_identifier IS NOT NULL
              AND vrx_courses.code != '1111'
            GROUP BY vrx_course_units.code")) ), true ) );

        // return DB::table('course_units')
        //         ->join('training_schedule', 'course_units.course_id', '=', 'training_schedule.course_id')
        //         ->select('course_units.code as subj_identifier', 'course_units.description as subj_name', 'course_units.subject_educ_fld_identifier_id as subj_educ_fld_identifier', 'course_units.vet_flag', 'course_units.nominal_hours')
        //         ->whereBetween('training_schedule.start_date', [$dateFrom,$dateTo])
        //         ->where('course_units.active', 1)
        //         ->groupBy('course_units.code')
        //         ->orderBy('course_units.course_id')
        //         ->orderBy('course_units.code')
        //         ->get();


     }
    
}
