<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class DocumentRelated extends Model implements AuditableContract
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
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['document_id','related_document_id','is_public', 'note', 'user_id', 'party_id'];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function party()
    {
    	return $this->belongsTo(Party::class, 'party_id');
    }

    public function document()
    {
    	return $this->belongsTo(Document::class);
    }

    public function related_document()
    {
    	return $this->belongsTo(Document::class);
    }
    
}
