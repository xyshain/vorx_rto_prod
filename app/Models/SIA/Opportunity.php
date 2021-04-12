<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Opportunity extends Model implements AuditableContract
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
        'closing_date' => ['required_if:opp_stage_id,5', 'nullable', 'date_format:d/m/Y'],
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    // protected $table = 'opportunities';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['closing_date'];

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function deal()
    {
    	return $this->belongsTo(Deal::class);
    }

    public function opps_staging()
    {
        return $this->belongsTo( OpportunityStage::class, 'opp_stage_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deal_detail()
    {
        return $this->belongsTo(DealDetail::class, 'deal_id', 'deal_id');
    }
}
