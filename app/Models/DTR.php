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
        'wrkhrs',
        'total_hours',
        'night_diff',
        'night_diff_reg',
        'night_diff_spec',
        'reg_holiday_hours',
        'spec_holiday_hours',
        // 'status',
        // 'leave_id',
        'leave_type_id',
        // 'created_at', 
        'updated_at'
    ];
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');  // Assuming `shift_id` exists in 'dtr' table
    }
// In Attendance.php
    public function employee() 
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function plottedSchedule()
    {
        return $this->hasOne(EmployeeSchedule::class, 'employee_id', 'employee_id')->where('date', $this->transindate);
    }
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}
