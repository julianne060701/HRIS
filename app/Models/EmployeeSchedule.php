<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeSchedule extends Model
{
    use HasFactory;

    protected $table = 'employee_schedules';

    protected $fillable = [
        'employee_id',
        'date',
        'shift_code',
        'leave_type_id',
    ];

    protected $casts = [
        'date' => 'date', // This line tells Laravel to convert the 'date' column to a Carbon instance
    ];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}