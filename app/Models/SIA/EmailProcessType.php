<?php
 
namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class EmailProcessType extends Model implements AuditableContract
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
      // protected $table = 'email_process_types';

	/*public function email_process(){
		return $this->hasMany(EmailTemplate::class);
	}

	public function email_content()
	{
		return $this->hasMany( EmailContent::class );
	}*/

	public function emailtemplate(){
		return $this->hasMany(EmailTemplate::class);
	}
	
}
