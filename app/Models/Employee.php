<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    
    protected $primaryKey = 'employee_id';  // Your custom primary key
    public $incrementing = false;         // employee_id is not auto-incrementing
    protected $keyType = 'string';          // employee_id is a string (e.g., "210033")


    protected $fillable = [
        'employee_id', 'first_name', 'middle_name', 'last_name',
        'birthday', 'contact_number', 'address',
        'sss', 'philhealth', 'tin', 'pagibig',
        'status', 'department', 'salary',
    ];

    /**
     * Define the one-to-many relationship with EmployeeSchedule.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function schedules()
    {
        return $this->hasMany(EmployeeSchedule::class, 'employee_id', 'employee_id');
    }

    // Keep other relationships as they are
    public function schedule()
    {
        return $this->hasMany(SchedPost::class, 'employee_id');
    }

    public function leaveCredits()
    {
        return $this->hasMany(LeaveCredit::class, 'employee_id', 'employee_id');
    }

    /**
     * Get shifts for the employee within a given date range.
     *
     * @param string $from 'YYYY-MM-DD'
     * @param string $to 'YYYY-MM-DD'
     * @return array An associative array where keys are dates and values are shift_codes.
     */
     public function getShiftsBetween($from, $to)
    {
        $schedules = $this->schedules()
                          ->whereBetween('date', [$from, $to])
                          ->with('leaveType') // <--- Eager load the leaveType relationship
                          ->get();

        $shiftMap = [];
        foreach ($schedules as $schedule) {
            $displayValue = ''; // Default empty
            if ($schedule->leaveType) {
                // If there's an associated leave type, display its name
                $displayValue = $schedule->leaveType->name;
            } elseif (!is_null($schedule->shift_code)) {
                // Otherwise, if a shift_code exists, display it
                $displayValue = $schedule->shift_code;
            }
            // If both are null, $displayValue remains empty ('')

            $shiftMap[$schedule->date->toDateString()] = $displayValue;
        }

        return $shiftMap;
    }
}   