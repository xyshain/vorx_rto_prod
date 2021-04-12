<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class DealAttachment extends Model implements AuditableContract
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


    /**
     *
     * static attribute validation rules
     *
     * @var array
     */
    public static $rules = [
        'name'      => ['required', 'string'],
        'hash_name' => ['required', 'string', 'unique'],
        'size'      => ['required', 'size'],
        'mime_type' => ['required', 'string'],
        'ext'       => ['required', 'string'],
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'deal_attachments';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['name','hash_name','size','mime_type','ext', '_input'];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function attachment_type()
    {
        return $this->belongsTo(AttachmentType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
