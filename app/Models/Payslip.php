<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    protected $fillable = [
        'employee_id', 'payroll_period', 'days_present', 'late_minutes', 'withholding_tax', 'net_pay'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function earnings()
    {
        return $this->hasMany(PayslipEarning::class);
    }

    public function deductions()
    {
        return $this->hasMany(PayslipDeduction::class);
    }
}
