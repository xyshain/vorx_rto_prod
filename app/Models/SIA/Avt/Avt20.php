<?php

namespace App\Models\Avt;

use Illuminate\Database\Eloquent\Model;
use DB;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Avt20 extends Model implements AuditableContract
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

    protected $table = 'avt_20_training_org_dlvr_loc';

	 protected $fillable = [
	 	'org_identifier',
	 	'dlvr_loc_identifier',
	 	'dlvr_loc_name',
	 	'postcode',
	 	'state_identifier',
	 	'addr_location',
	 	'country_identifier',
	 	'avt_process_id',
	 	'isValid',
	 	'user'
	 ];

	 public function get_data_avt20($dateFrom,$dateTo)
     {
     	// return DB::table('training_delivery_loc')->get();

        // return collect( json_decode( json_encode( DB::select(DB::raw("SELECT 
        //        vrx_training_delivery_loc.*
        //        -- (SELECT SUM( vrx_course_units.`nominal_hours`) FROM `vrx_course_units` WHERE `vrx_course_units`.course_id = vrx_courses.id) AS hours_attended
        //        -- *
        //        FROM vrx_training_schedule
        //         INNER JOIN vrx_training_delivery_loc ON (vrx_training_schedule.train_org_dlvr_loc_identifier = vrx_training_delivery_loc.dlvr_loc_identifier)
        //     WHERE
        //       vrx_training_schedule.start_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
        //     GROUP BY vrx_training_delivery_loc.dlvr_loc_identifier")) ), true ) );
        // dump('wew');

        /*return DB::table('student_completion')
                ->join('training_delivery_loc', 'training_delivery_loc.dlvr_loc_identifier', '=', 'student_completion.train_org_dlvr_loc_identifier')
                ->join('student_completion_details', 'student_completion_details.student_completion_id', '=', 'student_completion.id')
                ->join('organisation_info', 'organisation_info.org_identifier', '=', 'training_delivery_loc.org_identifier')
                ->select('training_delivery_loc.*', 'organisation_info.org_identifier AS org_id')
                ->whereBetween('student_completion_details.start_date', [$dateFrom,$dateTo])
                // ->whereIn('training_schedule.status_id', [6,2,3])
                ->groupBy('student_completion.train_org_dlvr_loc_identifier')
                ->get();*/

         return DB::select(DB::raw("select `vrx_training_delivery_loc`.*, `vrx_organisation_info`.`org_identifier` as `org_id` from `vrx_student_completion`
                            inner join `vrx_training_delivery_loc` on `vrx_training_delivery_loc`.`dlvr_loc_identifier` = `vrx_student_completion`.`train_org_dlvr_loc_identifier`
                            inner join `vrx_student_completion_details` on `vrx_student_completion_details`.`student_completion_id` = `vrx_student_completion`.`id`
                            inner join `vrx_organisation_info` on `vrx_organisation_info`.`org_identifier` = `vrx_training_delivery_loc`.`org_identifier`
                            where `vrx_student_completion_details`.`end_date` between '".$dateFrom."' AND '".$dateTo."'
                            group by `vrx_student_completion`.`train_org_dlvr_loc_identifier"));


     }
    
}
