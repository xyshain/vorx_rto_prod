<?php

namespace App\Models\Avt;

use Illuminate\Database\Eloquent\Model;
use DB;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Avt80 extends Model implements AuditableContract
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

    protected $table = 'avt_80_client_file';

    protected $fillable = [
     	'client_identifier',
     	'name_encrypt',
     	'high_school_lvl_comp_identifier',
     	'year_hs_completed',
     	'sex',
     	'date_of_birth',
     	'postcode',
     	'ind_status_identifier',
     	'lang_identifier',
     	'lbr_force_status_identifier',
     	'country_identifier',
     	'disability_flag',
     	'prior_educ_achieve_flag',
     	'at_school_flag',
     	'prof_spk_eng_identifier',
     	'addr_location',
     	'uniq_stud_identifier',
     	'state_identifier',
     	'addr_bldg_prop_name',
     	'addr_flat_unit_dtl',
     	'addr_st_num',
     	'addr_st_name',
     	'stat_area_lvl1_identifier',
      'stat_area_lvl2_identifier',
      'survey_contact_status',
      'learner_unique_identifier',
     	'avt_process_id',
      'isValid',
      'user'
    ];

    public function get_data_avt80($dateFrom,$dateTo)
     {
        return collect( json_decode( json_encode( DB::select(DB::raw("SELECT
              CONCAT('2019', vrx_student_info.uniq_stud_identifier) AS client_identifier,
              CONCAT_WS(' ', IF(cust.lastname = '', ',', CONCAT(UCASE(RTRIM(cust.lastname)), ',')), IF(cust.firstname = '', NULL, UCASE(RTRIM(cust.firstname))), IF(cust.middlename = '', NULL, UCASE(RTRIM(cust.middlename)))) AS name_encrypt,
              cust.gender AS sex,                 
              DATE_FORMAT(cust.date_of_birth, '%d%m%Y') AS date_of_birth,
              vrx_address.postcode,
              vrx_student_info.ind_status_identifier ,
              avt_lang_identifier.value AS lang_identifier,
              vrx_student_info.disability_flag,
              vrx_student_info.prof_spk_eng_identifier,
              vrx_student_info.uniq_stud_identifier,
              avt_postcodes.suburb AS addr_location,
              vrx_student_info.prior_educ_achieve AS prior_educ_achieve_flag,
              vrx_student_info.at_school_flag,
              vrx_student_info.year_hs_completed,
              avt_highest_schl_lvl_completed.value AS high_school_lvl_comp_identifier,
              avt_labour_force_status.value AS lbr_force_status_identifier,
              avt_state_identifier.value AS state_identifier,
              vrx_enrolments.survey_contact_status AS survey_contact_status,
              '1101' AS country_identifier,
              vrx_address.street_num AS addr_st_num,
              vrx_address.street_name AS addr_st_name,
              vrx_address.bldg_property_name AS addr_bldg_prop_name,
              vrx_address.flat_unit AS addr_flat_unit_dtl,
              '' AS stat_area_lvl1_identifier,
              '' AS stat_area_lvl2_identifier
              FROM
              vrx_student_completion
              INNER JOIN vrx_student_completion_details ON vrx_student_completion_details.student_completion_id = vrx_student_completion.id
              INNER JOIN vrx_courses ON vrx_courses.code = vrx_student_completion.course_code
              INNER JOIN vrx_persons AS cust ON cust.id = vrx_student_completion.persons_id
              INNER JOIN vrx_party ON vrx_party.id = cust.party_id
              INNER JOIN vrx_deals ON vrx_deals.party_id = vrx_party.id
              INNER JOIN vrx_student_info ON (vrx_student_info.person_id = cust.id)
              INNER JOIN vrx_address ON (vrx_address.party_id = vrx_party.id)
              INNER JOIN avt_state_identifier ON (vrx_address.state_identifier = avt_state_identifier.id)
              INNER JOIN avt_postcodes ON ( vrx_address.location_suburb_town = avt_postcodes.id )
              INNER JOIN vrx_enrolments ON (vrx_deals.id = vrx_enrolments.deal_id)
              LEFT JOIN vrx_contacts_info contact ON (contact.party_id = vrx_party.id)
              LEFT JOIN avt_highest_schl_lvl_completed ON (avt_highest_schl_lvl_completed.id = vrx_student_info.highest_school_lvl_comp_identifier)          
              LEFT JOIN avt_labour_force_status ON (avt_labour_force_status.id = vrx_student_info.lbr_force_status_identifier)          
              LEFT JOIN avt_lang_identifier ON (avt_lang_identifier.value = vrx_student_info.lang_identifier) 
            WHERE
              vrx_student_completion_details.start_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
              AND vrx_student_completion_details.end_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
              AND vrx_student_info.uniq_stud_identifier IS NOT NULL
              AND vrx_student_completion.deleted_at IS NULL
              AND vrx_courses.code != '1111'
              AND vrx_student_completion.completion_status_id = 3
              AND vrx_deals.deleted_at IS NULL


              
            --   INNER JOIN vrx_student_completion_details ON vrx_student_completion_details.student_completion_id = vrx_student_completion.id
            --   INNER JOIN vrx_courses ON vrx_courses.code = vrx_student_completion.course_code
            --   INNER JOIN vrx_persons AS cust ON cust.id = vrx_student_completion.persons_id
            --   INNER JOIN vrx_party ON vrx_party.id = cust.party_id
            --   INNER JOIN vrx_deals ON vrx_deals.party_id = vrx_party.id
            --   INNER JOIN vrx_student_info ON (vrx_student_info.person_id = cust.id)
            --   INNER JOIN vrx_address ON (vrx_address.party_id = vrx_party.id)
            --   LEFT JOIN vrx_enrolments ON (vrx_deals.id = vrx_enrolments.deal_id)
            --   INNER JOIN avt_state_identifier ON (vrx_address.state_identifier = avt_state_identifier.id)
            --   LEFT JOIN vrx_contacts_info contact ON (contact.party_id = vrx_party.id)
            --   LEFT JOIN avt_highest_schl_lvl_completed ON (avt_highest_schl_lvl_completed.id = vrx_student_info.highest_school_lvl_comp_identifier)          
            --   LEFT JOIN avt_labour_force_status ON (avt_labour_force_status.id = vrx_student_info.lbr_force_status_identifier)          
            --   LEFT JOIN avt_lang_identifier ON (avt_lang_identifier.value = vrx_student_info.lang_identifier)          
            --   INNER JOIN avt_postcodes ON ( vrx_address.location_suburb_town = avt_postcodes.id )
            -- WHERE
            --   vrx_student_completion_details.start_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
            --   AND vrx_student_completion_details.end_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
            --   AND vrx_student_info.uniq_stud_identifier IS NOT NULL
            --   AND vrx_student_completion.completion_status_id = 3
            --   AND vrx_courses.code != '1111'
            --   AND vrx_deals.deleted_at IS NULL
            GROUP BY
              -- vrx_deals.id, vrx_student_info.uniq_stud_identifier
              vrx_student_info.uniq_stud_identifier
            ORDER BY
              vrx_student_completion.id DESC")) ), true ) );
     }
    
}
