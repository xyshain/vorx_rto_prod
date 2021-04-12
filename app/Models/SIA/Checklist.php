<?php

namespace App\Models\SIA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checklist extends Model
{
    //
    protected $connection = 'SIA';
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'deal_id',
        'concessionaire_card',
        'drivers_license',
        'blue_card',
        'medicare_card',
        'vsn',
        'lln',
        'ptr',
        'student_agreement',
        'student_declaration',
        'attendance_sheet'
    ];
}
