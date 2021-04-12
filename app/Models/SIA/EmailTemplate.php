<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class EmailTemplate extends Model implements AuditableContract
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
    protected $table = 'email_template';
    protected $fillable = ['email_process_types_id','email_attachment_id','email_content_template_id'];
    public function emailProcess()
    {
    	return $this->belongsToMany(EmailProcessType::class);
    }
    public function emailAttachment()
    {
    	return $this->belongsTo(EmailAttachment::class, 'email_attachment_id', 'id');
    }
    public function emailContent()
    {
    	return $this->belongsTo(EmailContent::class, 'email_content_template_id', 'id');
    }

}
        