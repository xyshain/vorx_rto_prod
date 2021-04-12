<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;

class emailtodb_email extends Model
{
    //
    protected $connection = 'SIA';

    protected $table ='emailtodb_email';

    protected $columns = array('ID','mBox','mail_account','IDEmail','EmailFrom','EmailFromP','EmailTo','DateE','DateDb','DateRead','DateRe','Status','Type','Del','Subject','Message','Message_html','MsgSize','Kind','IDre');

    public function scopeExclude($query,$value = array()){
	    return $query->select( array_diff( $this->columns,(array) $value) );
	}
}
