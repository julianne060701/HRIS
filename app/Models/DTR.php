<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DTR extends Model
{
    protected $table = 'dtr';

    // Define the fillable columns
    protected $fillable = [
        'employee_id',
        'transindate',
        'time_in',
        'transoutdate',
        'time_out',
        'shift_code',
        'xptd_time_in',
        'xptd_time_out',
        'is_late',
        'late_minutes',
        'is_undertime',
        'undertime_minutes',
        // 'created_at', 
        'updated_at'
    ];
    public function shift()
{
    return $this->belongsTo(Shift::class, 'shift_id');  // Assuming `shift_id` exists in 'dtr' table
}
 protected $casts = [
        'transindate'     => 'date',
        'transoutdate'    => 'date',
        'time_in'         => 'string', 
        'time_out'        => 'string', 
        'xptd_time_in'    => 'string', 
        'xptd_time_out'   => 'string', 
        'is_late'         => 'boolean',
        'is_undertime'    => 'boolean',
        'late_minutes'    => 'integer',
        'undertime_minutes' => 'integer',
    ];
// In Attendance.php
public function employee() {
    return $this->belongsTo(Employee::class, 'employee_id');
}

public function plottedSchedule() {
    return $this->hasOne(EmployeeSchedule::class, 'employee_id', 'employee_id')->where('date', $this->transindate);
}
}
