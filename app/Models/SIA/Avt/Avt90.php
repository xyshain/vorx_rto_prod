<?php

namespace App\Models\Avt;

use Illuminate\Database\Eloquent\Model;
use DB;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Avt90 extends Model implements AuditableContract
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

    protected $table = 'avt_90_client_disablility';

    protected $fillable = [
     	'client_identifier',
     	'disability_type_identifier',
     	'avt_process_id',
        'isValid',
        'user'
    ];

    public function get_data_avt90($dateFrom,$dateTo,$ErrorClient)
     {
        $eclient = '';

        if(!empty($ErrorClient)){
          $eclient = 'AND dis.id NOT IN ('. implode(',', $ErrorClient) .')';
        }

        return collect( json_decode( json_encode( DB::select(DB::raw("SELECT 
        	dis.client_identifier, avtDis.value AS disability_type_identifier 
			FROM vrx_student_completion 
			-- INNER JOIN vrx_stud_train_sched sts ON sts.schedule_id = tSched.id
            INNER JOIN vrx_student_completion_details ON vrx_student_completion_details.student_completion_id = vrx_student_completion.id
			INNER JOIN vrx_persons AS cust ON cust.id = vrx_student_completion.persons_id
            INNER JOIN vrx_party ON vrx_party.id = cust.party_id
            INNER JOIN vrx_deals ON vrx_deals.party_id = vrx_party.id
			INNER JOIN vrx_student_info stud ON stud.person_id = cust.id
			INNER JOIN vrx_student_disability dis ON dis.id = stud.disability_flag
			INNER JOIN avt_disability_types avtDis ON avtDis.value = dis.disability_type_identifier
            WHERE
              vrx_student_completion_details.end_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
              AND vrx_student_completion.deleted_at IS NULL
              -- AND tSched.status_id IN (6,2,3)
              AND stud.uniq_stud_identifier IS NOT NULL
              {$eclient}
            GROUP BY 
              vrx_deals.id
            ORDER BY
              vrx_student_completion_details.end_date")) ), true ) );
     }
    
}
