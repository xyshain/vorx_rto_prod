<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class CompanySettings extends Model implements AuditableContract
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
    /***
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'organisation_info';

    /**
     *
     * The attribute validation rules
     *
     */

    public static $rules = [
        'org_identifier'        => ['required'],
        'org_name'              => ['required'],
        'org_type_identifier'   => ['required'],
        'addr_line1'            => ['required'],
        'addr_line2'            => ['nullable'],
        'addr_location'         => ['required'],
        'postcode'              => ['required'],
        'state_identifier'      => ['required'],
        'contact_name'          => ['required'],
        'tel_number'            => ['required'],
        'fax_number'            => ['nullable'],
        'email_addr'            => ['required'],
        'site_url'              => ['required' , 'URL'],
        'site_name'             => ['required'],
        'admin_email'           => ['required']
    ];

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'org_identifier',
        'org_name',
        'org_type_identifier',
        'addr_line1',
        'addr_line2',
        'addr_location',
        'postcode',
        'state_identifier',
        'contact_name',
        'tel_number',
        'fax_number',
        'email_addr',
        'site_url',
        'site_name',
        'admin_email',
        'logo_alt',
        'site_img'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public function company_settings()
    {
        return $this->belongsTo(CompanySettings::class);
    }


}
