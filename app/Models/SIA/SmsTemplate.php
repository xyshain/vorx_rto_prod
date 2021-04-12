<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class SmsTemplate extends Model implements AuditableContract
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

    protected $fillable = ['template_name','template_body','user_id'];

    public static $rules = [
        'template_name'     => ['required', 'string'],
        'template_body'    => ['nullable','string']
    ];

    public function user(){
    	return $this->belongsTo(User::class);
    }
}
