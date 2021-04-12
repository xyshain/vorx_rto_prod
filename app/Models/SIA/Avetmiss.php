<?php

namespace App\Models\SIA;

use DB;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Avetmiss extends Model implements AuditableContract
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
    //

    private $tbl_avt10_to = 'avt_10_training_org';
    private $tbl_orgtype = 'avt_org_type';

    public function training_org_list()
    {
    	return DB::select( DB::raw("SELECT * FROM {$tbl_avt10_to}") );
    }

    public function update_details($tbl, array $data, $id)
    {
    	// set data holder
    	$set = [];

    	// create set data
    	foreach ($data as $key => $value) {
    		if ( ! is_string($key) ) throw new \InvalidArgumentException("Argument key is not a valid string");

    		$set[] = $key . ' = ' . $value;
    		
    	}

    	// set data string
    	$setData = implode(', ', $set);

    	// where argument
    	if ( ! is_array( $id ) ) {
    		$whereData = "id = {$id}";
    	} else {
    		$k = key($id);
    		$whereData = $k . ' = ' . $id[$k];
    	}
    	return DB::statement( DB::raw("UPDATE {$tbl} SET {$setData} WHERE {$whereData}") );
    }

    public function get_data_avt30($datefrom, $dateto)
    {
		return Schedule::with([
				'getCourse.course_avt_details',
				'getCourse'  => function($query) {
					$query->groupBy('name');
				}
			])
			->whereBetween('start_date', [$datefrom, $dateto])
			->orderBy('schedule.id', 'desc')
			->get();
    }

    public function get_data_avt60($datefrom, $dateto)
    {
    	return Schedule::with([
    			'getCourse.course_units' => function($query) {
    				$query->groupBy('code')
    					->orderBy('code', 'asc');
    			}
			])
    		->whereBetween('start_date', [$datefrom, $dateto])
			->get();
    }

    public function get_data_avt80($datefrom, $dateto)
    {
    	return DB::select( DB::raw(
                "SELECT
                
                    *
                
                FROM vrx_training_schedule AS schedule

                JOIN vrx_courses AS courses ON courses.id = schedule.id
                JOIN vrx_training_status AS taining_status ON training_status.id = schedule.status_id
                JOIN vrx_attendances AS attendances ON attendances.schedule_id = schedule.id
                JOIN vrx_stud_train_sched AS pivotStudentSched ON pivotStudentSched.schedule_id = schedule.id
                JOIN vrx_deals AS deals ON deals.id = pivotStudentSched.deal_id
                JOIN vrx_party AS party ON party.id = deals.party_id
                JOIN vrx_persons AS person ON person.party_id = party.id
                JOIN vrx_person_types AS person_types ON 5 = person.person_type -- for customer/students
                JOIN vrx_address AS address ON address.party_id = party.id
                JOIN avt_state_identifier AS state ON state.id = address.state_identifier
                JOIN avt_postcodes as suburbs ON suburbs.id = address.location_suburb_town

                WHERE schedule.start_date BETWEEN '{$datefrom}' AND '{$dateto}'
                ORDER BY schedule.id DESC;
                "
            ) );
    }

    public function get_data_avt85($datefrom, $dateto)
    {
        return DB::select( DB::raw(
                "SELECT
                
                    *

                FROM vrx_training_schedule AS schedule

                JOIN vrx_stud_train_sched AS pivotStudentSched ON pivotStudentSched.schedule_id = schedule.id
                JOIN vrx_deals AS deals ON deals.id = pivotStudentSched.deal_id
                JOIN vrx_party AS party ON party.id = deals.party_id
                JOIN vrx_persons AS person ON person.party_id = party.id
                JOIN vrx_person_types AS person_types ON 5 = person.person_type -- for customer/students
                JOIN vrx_address AS address ON address.party_id = party.id
                JOIN avt_state_identifier AS state ON state.id = address.state_identifier

                WHERE schedule.start_date BETWEEN '{$datefrom}' AND '{$dateto}'
                ORDER BY person.id ASC;
                "
            ) );
    }

    public function get_data_avt90($datefrom, $dateto)
    {
        return DB::select( DB::raw(
                "SELECT
                
                    *
                
                FROM vrx_client_disability AS client_disability

                JOIN avt_disability_types AS avt_disabilities ON avt_disabilities.id = client_disability.disability_type_identifier
                JOIN vrx_training_schedule AS schedule ON schedule.id = client_disability.training_sched_id

                WHERE schedule.start_date BETWEEN '{$datefrom}' AND '{$dateto}'
                ORDER BY schedule.start_date;
                "
            ) );
    }

    public function get_data_avt100()
    {
        return DB::select( DB::raw(
                "SELECT
                
                    *
                
                FROM vrx_client_prior_education AS cpe
                "
            ) );
    }

    public function get_data_avt120($datefrom, $dateto)
    {
        return DB::select( DB::raw(
                "SELECT
                
                    *
                
                FROM vrx_training_schedule AS schedule

                JOIN vrx_stud_train_sched AS pivotStudentSched ON pivotStudentSched.schedule_id = schedule.id
                JOIN vrx_deals AS deals ON deals.id = pivotStudentSched.deal_id
                JOIN vrx_party AS party ON party.id = deals.party_id
                JOIN vrx_persons AS person ON person.party_id = party.id
                JOIN vrx_person_types AS person_types ON 5 = person.person_type -- for customer/students
                JOIN vrx_courses AS courses ON courses.id = schedule.id
                JOIN vrx_course_units AS course_units ON course_units.course_id = course.id
                JOIN vrx_training_modes AS training_modes ON training_modes.id = schedule.training_mode_id
                JOIN avt_postcodes as suburbs ON suburbs.id = schedule.suburb_id

                WHERE schedule.start_date BETWEEN '{$datefrom}' AND '{$dateto}';
                "
            ) );
    }

    public function get_data_avt130()
    {
        return DB::select( DB::raw(
            "SELECT

                *
            
            FROM vrx_client_program_completed as cpc;
            "
            ) );
    }
}
