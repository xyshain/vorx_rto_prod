<?php

namespace App\Models\Avt;

use Illuminate\Database\Eloquent\Model;
use DB;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Avt10 extends Model implements AuditableContract
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

     protected $table = 'avt_10_training_org';

     protected $fillable = ['org_identifier','org_name','org_type_identifier', 'addr_line1', 'addr_line2', 'addr_location', 'postcode', 'state_identifier', 'contact_name', 'tel_number', 'fax_number', 'email_addr', 'avt_process_id', 'isValid', 'user'];

     public function get_data_avt10()
     {
     	return DB::table('organisation_info')->get();
     }


}
