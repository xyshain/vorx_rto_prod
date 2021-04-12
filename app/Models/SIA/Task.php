<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Task extends Model implements AuditableContract
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
    protected $fillable = ['title','description','due_date', 'status_id', 'priority_id'];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function status()
    {
    	return $this->belongsTo( TaskStatus::class, 'status_id' );
    }

    public function priority()
    {
    	return $this->belongsTo( TaskPriority::class, 'priority_id' );
    }

    public function meta()
    {
        return $this->hasMany( TaskMeta::class );
    }
    public function comments()
    {
        return $this->hasMany( TaskComment::class );
    }
    
}
