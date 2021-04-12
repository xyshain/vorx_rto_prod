<?php
 
namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class EmailAttachment extends Model implements AuditableContract
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
    protected $table = 'email_attachment';
    protected $fillable = ['attachment_name','attachment_size','attachment_link','attachment_ext'];
	/*public function getProcess(){
		return $this->belongsTo(EmailProcess::class);
	}*/
	public function emailtemplate()
	{
		return $this->hasMany(EmailTemplate::class);
	}
}
