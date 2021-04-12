<?php

namespace App\Models\Avt;

use Illuminate\Database\Eloquent\Model;
use DB;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Avt100 extends Model implements AuditableContract
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

    protected $table = 'avt_100_client_prior_educ';

     protected $fillable = ['client_identifier', 'prior_educ_achieve_identifier', 'avt_process_id', 'isValid', 'user'];

     public function get_data_avt100($dateFrom,$dateTo,$ErrorClient)
     {
   //      return collect( json_decode( json_encode( DB::select(DB::raw("SELECT 
   //      	cust.id AS client_identifier, 
   //          -- prio.value AS prior_educ_achieve_identifier
   //          stud.prior_educ_achieve AS prior_educ_achieve_identifier
			// FROM vrx_training_schedule tSched
			// JOIN vrx_stud_train_sched sts ON sts.schedule_id = tSched.id
			// JOIN vrx_deals deals ON deals.id = sts.deal_id
			// JOIN vrx_party party ON party.id = deals.party_id
			// JOIN vrx_persons cust ON cust.party_id = party.id
			// JOIN vrx_student_info stud ON stud.person_id = cust.id
			// -- JOIN avt_prior_education_achievements prio ON prio.id = stud.prior_educ_achieve
   //          WHERE
   //            tSched.start_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
   //            AND tSched.status_id = 6
   //          ORDER BY
   //            tSched.start_date")) ), true ) );
        $loop = [];

        $eclient = '';

        if(!empty($ErrorClient)){
          $eclient = 'AND cust.id NOT IN ('. implode(',', $ErrorClient) .')';
        }

        $info =  collect( json_decode( json_encode( DB::select(DB::raw("SELECT 
            cust.id AS client_identifier, 
            -- prio.value AS prior_educ_achieve_identifier
            stud.prior_educ_achieve AS prior_educ_achieve_identifier
            FROM vrx_student_completion
            -- JOIN vrx_stud_train_sched sts ON sts.schedule_id = tSched.id
            INNER JOIN vrx_student_completion_details ON vrx_student_completion_details.student_completion_id = vrx_student_completion.id
            INNER JOIN vrx_persons AS cust ON cust.id = vrx_student_completion.persons_id
            INNER JOIN vrx_party ON vrx_party.id = cust.party_id
            INNER JOIN vrx_deals ON vrx_deals.party_id = vrx_party.id
            JOIN vrx_student_info stud ON stud.person_id = cust.id
            -- JOIN avt_prior_education_achievements prio ON prio.id = stud.prior_educ_achieve
            WHERE
              vrx_student_completion_details.end_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
              AND stud.uniq_stud_identifier IS NOT NULL
              AND vrx_student_completion.deleted_at IS NULL
              -- AND tSched.status_id IN (6,2,3)
              {$eclient}
            GROUP BY
              vrx_deals.id
            ORDER BY
              vrx_student_completion_details.end_date")) ), true ) );
        // dump('wew');
        // dd($info);
        if($info){
            foreach ($info as $key => $value) {
                if($value['prior_educ_achieve_identifier']){
                    $prio = explode(',', $value['prior_educ_achieve_identifier']);

                    foreach ($prio as $v) {
                        if(is_int($v)){
                            $q =    collect( json_decode( json_encode( DB::select(DB::raw("SELECT 
                                    value
                                    FROM avt_prior_education_achievements
                                    -- JOIN avt_prior_education_achievements prio ON prio.id = stud.prior_educ_achieve
                                    WHERE
                                      id = {$v} ")) ), true ) );

                            array_push( $loop, ['client_identifier'=> $value['client_identifier'], 'prior_educ_achieve_identifier' => $q[0]['value']] );      
                        }
                        
                    }

                    // dd($loop);
                }
            }
        }
        return $loop;
     }

}
