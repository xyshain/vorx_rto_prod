<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class TaskMeta extends Model implements AuditableContract
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
    protected $fillable = ['task_id','task_meta_key','task_meta_value'];

    use SoftDeletes;
    protected $dates = ['deleted_at'];
    
    public function user()
    {
    	return $this->belongsTo(User::class, 'task_meta_value');
    }
    
}
