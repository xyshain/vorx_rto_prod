<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Document extends Model implements AuditableContract
{
    use Auditable;

    protected $connection = 'SIA';
    
    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['u_id','file_name','version', 'parent_id', 'file_path', 'user_id', 'party_id', 'note', 'is_current', 'ext'];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function party()
    {
    	return $this->belongsTo(Party::class, 'party_id');
    }


}
