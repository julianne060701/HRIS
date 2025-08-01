<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee_deduction extends Model
{
    protected $table = 'employee_deduction';
    
    protected $fillable = [
        'employee_id',
        'payroll_id',
        'monthly_salary',
        'sss_employee_contribution',
        'sss_employer_contribution',
        'pagibig_employee_contribution',
        'pagibig_employer_contribution',
        'philhealth_employee_contribution',
        'philhealth_employer_contribution',
        'withholdingtax', // Matches DB column name
        'total_employee_deduction', // Matches DB column name (singular)
        'total_employer_deduction', // Matches DB column name exactly
    ];

    protected $casts = [
        'monthly_salary' => 'decimal:2',
        'sss_employee_contribution' => 'decimal:2',
        'sss_employer_contribution' => 'decimal:2',
        'pagibig_employee_contribution' => 'decimal:2',
        'pagibig_employer_contribution' => 'decimal:2',
        'philhealth_employee_contribution' => 'decimal:2',
        'philhealth_employer_contribution' => 'decimal:2',
        'withholdingtax' => 'decimal:2',
        'total_employee_deduction' => 'decimal:2',
        'total_employer_deduction' => 'decimal:2', // Fixed: deduction not contribution
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}