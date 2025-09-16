<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollData extends Model
{
    use HasFactory;

    protected $table = 'payrolldata';

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'payroll_start_date',
        'payroll_end_date',
        'gross_pay',
        'basic_hours_pay',
        'night_differential_pay',
        'night_differential_pay_reg', // New
        'night_differential_pay_spec', // New
        'regular_holiday_pay',
        'special_holiday_pay',
        'overtime_pay',
        'ot_reg_holiday_pay', // New
        'ot_spec_holiday_pay', // New
        'ot_night_diff_rdr_pay', // New
        'late_deduction',
        'undertime_deduction',
        'rest_day_pay', // New
        'rest_reg_pay', // New
        'rest_spec_pay', // New
        'rest_ot_pay', // New
        'sss_contribution',
        'philhealth_contribution',
        'pagibig_contribution',
        'tax_withheld',
        'loan_deduction', // New
        'other_deductions',
        'total_deductions',
        'net_pay',
        'processed_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'payroll_id'); 
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}