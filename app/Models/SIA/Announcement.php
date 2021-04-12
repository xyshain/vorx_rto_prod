<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Announcement extends Model implements AuditableContract
{
    //

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
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
    	'attachment_code',
    	'title',
    	'category_id',
    	'body',
    	'date',
    	'status',
    	'user_id'
    ];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function announcement_attachments()
    {
    	return $this->hasMany(AnnouncementAttachments::class, 'attachment_code', 'attachment_code');
    }

    public function category()
    {
    	return $this->belongsTo(AnnouncementCategory::class, 'category_id');
    }

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

}
