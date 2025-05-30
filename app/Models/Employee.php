<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    // Define the table name if it's not the plural form of the model name
    protected $table = 'employees';

    // Define the fillable fields
    protected $fillable = [
        'employee_id', 'first_name', 'middle_name', 'last_name',
        'birthday', 'contact_number', 'address',
        'sss', 'philhealth', 'tin', 'pagibig',
        'status', 'department', 'salary',
    ];
    public function schedules()
{
    return $this->hasMany(\App\Models\EmployeeSchedule::class);
}
public function schedule()
{
    return $this->hasMany(\App\Models\SchedPost::class, 'employee_id');
}

    public function getShiftsBetween($from, $to)
{
    $schedules = $this->schedules()
        ->whereBetween('date', [$from, $to])
        ->get();

    $shiftMap = [];

    foreach ($schedules as $schedule) {
        $shiftMap[$schedule->date] = $schedule->shift_code;
    }

    return $shiftMap;
}
}