<?php

namespace App\Models\Avt;

use Illuminate\Database\Eloquent\Model;
use DB;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Avt85 extends Model implements AuditableContract
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

    protected $table = 'avt_85_client_postal_details';

    protected $fillable = [
     	'client_identifier',
     	'client_title',
     	'client_fname',
     	'client_lname',
     	'addr_bldg_prop_name',
     	'addr_flat_unit_dtl',
     	'addr_st_num',
     	'addr_st_name',
     	'addr_post_box',
     	'addr_postal',
     	'postcode',
     	'state_identifier',
     	'tel_num_home',
     	'tel_num_work',
     	'tel_num_mobile',
      'email_ad',
     	'alt_email_ad',
        'avt_process_id',
        'isValid',
        'user'
    ];

    public function get_data_avt85($dateFrom,$dateTo,$ErrorClient)
     {
        $eclient = '';

        if(!empty($ErrorClient)){
          $eclient = 'AND cust.id NOT IN ('. implode(',', $ErrorClient) .')';
        }

        return collect( json_decode( json_encode( DB::select(DB::raw("SELECT
                  CONCAT('2019', vrx_student_info.uniq_stud_identifier) AS client_identifier,
                  '' as client_title,
                  UCASE(cust.firstname) as client_fname,
                  UCASE(cust.lastname) as client_lname,
                  UCASE(vrx_address.street_name) as addr_postal,
                  vrx_address.postcode,
                  contact.mobile_number as tel_num_mobile,
                  contact.phone_home as tel_num_home,
                  contact.phone_work as tel_num_work,
                  UCASE(contact.email_personal) as email_ad,
                  UCASE(contact.email_work) as alt_email_ad,
                  avt_state_identifier.value AS state_identifier,
                  avt_postcodes.suburb AS addr_location,
                  vrx_address.street_num AS addr_st_num,
                  vrx_address.street_name AS addr_st_name,
                  vrx_address.bldg_property_name AS addr_bldg_prop_name,
                  vrx_address.flat_unit AS addr_flat_unit_dtl,
                  vrx_student_info.uniq_stud_identifier AS usi
                FROM
                  vrx_student_completion
                  INNER JOIN vrx_student_completion_details ON vrx_student_completion_details.student_completion_id = vrx_student_completion.id
                  INNER JOIN vrx_courses ON vrx_courses.code = vrx_student_completion.course_code
                  INNER JOIN vrx_persons AS cust ON cust.id = vrx_student_completion.persons_id
                  INNER JOIN vrx_party ON vrx_party.id = cust.party_id
                  INNER JOIN vrx_deals ON vrx_deals.party_id = vrx_party.id
                  INNER JOIN vrx_student_info ON (vrx_student_info.person_id = cust.id)
                  INNER JOIN vrx_address ON (vrx_address.party_id = vrx_party.id)
                  INNER JOIN vrx_enrolments ON (vrx_deals.id = vrx_enrolments.deal_id)
                  INNER JOIN avt_state_identifier ON (vrx_address.state_identifier = avt_state_identifier.id)
                  INNER JOIN avt_postcodes ON ( vrx_address.location_suburb_town = avt_postcodes.id )
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

                  -- INNER JOIN vrx_student_completion_details ON vrx_student_completion_details.student_completion_id = vrx_student_completion.id
                  -- INNER JOIN vrx_courses ON vrx_courses.code = vrx_student_completion.course_code
                  -- INNER JOIN vrx_persons AS cust ON cust.id = vrx_student_completion.persons_id
                  -- INNER JOIN vrx_party ON vrx_party.id = cust.party_id
                  -- INNER JOIN vrx_deals ON vrx_deals.party_id = vrx_party.id
                  -- LEFT JOIN vrx_contacts_info contact ON (contact.party_id = vrx_party.id)
                  -- INNER JOIN vrx_address ON (vrx_address.party_id = vrx_party.id)
                  -- LEFT JOIN vrx_enrolments ON (vrx_deals.id = vrx_enrolments.deal_id)
                  -- INNER JOIN vrx_student_info ON (vrx_student_info.person_id = cust.id)
                  -- INNER JOIN avt_state_identifier ON (vrx_address.state_identifier = avt_state_identifier.id)
                  -- INNER JOIN avt_postcodes ON ( vrx_address.location_suburb_town = avt_postcodes.id )
                  -- LEFT JOIN avt_highest_schl_lvl_completed ON (avt_highest_schl_lvl_completed.id = vrx_student_info.highest_school_lvl_comp_identifier)
                  -- LEFT JOIN avt_labour_force_status ON (avt_labour_force_status.id = vrx_student_info.lbr_force_status_identifier)
                  -- LEFT JOIN avt_lang_identifier ON (avt_lang_identifier.value = vrx_student_info.lang_identifier)
            -- WHERE
            --   vrx_student_completion_details.start_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
            --   AND vrx_student_completion_details.end_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
            --   AND vrx_student_info.uniq_stud_identifier IS NOT NULL
            --   AND vrx_student_completion.completion_status_id = 3
            --   AND vrx_courses.code != '1111'
            --   AND vrx_deals.deleted_at IS NULL
            GROUP BY
              -- vrx_deals.id, usi
              usi
            ORDER BY
              client_identifier ASC")) ), true ) );
     }
    
}
