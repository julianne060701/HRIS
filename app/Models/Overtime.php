<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{   
    protected $table = 'overtime';
    
    protected $fillable = [
        'employee_id',
        'ot_date',
        'ot_in',
        'ot_out',
        'total_ot_hours',
        'is_approved',
        'approved_hours',
        'ot_reg_holiday_hours',
        'ot_spec_holiday_hours',
        'ot_reg_ho_rdr',
        'ot_spec_ho_rdr',
        'ot_night_diff_rdr',
    ];
    
}