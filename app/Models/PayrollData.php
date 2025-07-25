<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollData extends Model
{
    use HasFactory;

    protected $table = 'payrolldata'; // Explicitly define the table name

    protected $fillable = [
        'payroll_id', // Added for linking to the Payroll period
        'employee_id',
        'payroll_start_date',
        'payroll_end_date',
        'gross_pay',
        'basic_hours_pay',
        'night_differential_pay',
        'regular_holiday_pay',
        'special_holiday_pay',
        'overtime_pay',
        'late_deduction',
        'undertime_deduction',
        'sss_contribution',
        'philhealth_contribution',
        'pagibig_contribution',
        'tax_withheld',
        'other_deductions',
        'total_deductions',
        'net_pay',
        'processed_by', // Added for tracking who processed the payroll
    ];

    // If 'employee_id' is a foreign key, you might want to define a relationship
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(Payroll::class, 'payroll_id'); // Assuming 'payroll_id' column
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by'); // Assuming 'users' table for processors
    }
}