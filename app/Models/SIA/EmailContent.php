<?php
 
namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class EmailContent extends Model implements AuditableContract
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
    protected $table = 'email_content_template';
    protected $fillable = ['content_name','content_body'];
	public function emailtemplate()
	{

		return $this->hasMany(EmailTemplate::class, 'email_content_template_id', 'id');
		// return $this->belongsToMany(EmailTemplate::class);
		// return $this->belongsTo(EmailTemplate::class);
	}
}
