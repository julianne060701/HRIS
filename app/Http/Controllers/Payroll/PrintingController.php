<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrintingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get unique payroll codes from payrolldata table
        $payrolls = $this->getPayrollCodes();
        
        return view('HR.payroll.printing', compact('payrolls'));
    }
    
    /**
     * Get employees by payroll code via AJAX
     */
    public function getEmployeesByPayroll(Request $request)
    {
        $payrollCode = $request->input('payroll_code');
        
        // Get employees with payslips for the selected payroll
        $employees = $this->getEmployeesWithPayslips($payrollCode);
        
        return response()->json($employees);
    }
    
    /**
     * Get specific employee payslip data
     */
    public function getEmployeePayslip(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $payrollCode = $request->input('payroll_code');
        
        // Get complete payslip data for the employee
        $payslipData = $this->getEmployeePayslipData($employeeId, $payrollCode);
        
        return response()->json($payslipData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    
    /**
     * Get distinct payroll codes from payrolldata table
     */
    private function getPayrollCodes()
    {
        return DB::table('payrolldata')
            ->distinct()
            ->pluck('payroll_id')
            ->sort()
            ->values()
            ->toArray();
    }
    
    /**
     * Get employees with payslips for a specific payroll code
     */
    private function getEmployeesWithPayslips($payrollCode)
    {
        return DB::table('payrolldata')
            ->join('employees', 'payrolldata.employee_id', '=', 'employees.id')
            ->where('payrolldata.payroll_id', $payrollCode)
            ->select([
                'payrolldata.id',
                'employees.employee_id',
                'employees.first_name',
                'employees.last_name',
                'employees.position',
                'payrolldata.net_pay',
                // Calculate present days from basic_hours_pay if you don't store it directly
                DB::raw('COALESCE(payrolldata.basic_hours_pay / (employees.salary / 22 / 8), 0) as present_days')
            ])
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'employee_id' => $employee->employee_id,
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'position' => $employee->position,
                    'present_days' => round($employee->present_days, 1),
                    'net_pay' => $employee->net_pay
                ];
            })
            ->toArray();
    }
    
    /**
     * Get complete payslip data for a specific employee and payroll
     */
    private function getEmployeePayslipData($employeeId, $payrollCode)
    {
        // Get the payroll record
        $payrollRecord = DB::table('payrolldata')
            ->join('employees', 'payrolldata.employee_id', '=', 'employees.id')
            ->where('payrolldata.id', $employeeId)
            ->where('payrolldata.payroll_id', $payrollCode)
            ->select([
                'employees.employee_id',
                'employees.first_name',
                'employees.last_name',
                'employees.position',
                'employees.salary',
                'payrolldata.*'
            ])
            ->first();

        if (!$payrollRecord) {
            return null;
        }

        // Calculate present days from basic hours pay
        $dailyRate = $payrollRecord->salary / 22;
        $hourlyRate = $dailyRate / 8;
        $presentDays = $payrollRecord->basic_hours_pay > 0 ? 
            round($payrollRecord->basic_hours_pay / $hourlyRate / 8, 1) : 0;

        // Calculate undertime and tardy in minutes (assuming deductions are in monetary value)
        $minuteRate = $hourlyRate / 60;
        $undertimeMinutes = $minuteRate > 0 ? round($payrollRecord->undertime_deduction / $minuteRate) : 0;
        $lateMinutes = $minuteRate > 0 ? round($payrollRecord->late_deduction / $minuteRate) : 0;

        // Prepare earnings array
        $earnings = [];
        if ($payrollRecord->night_differential_pay > 0) {
            $earnings[] = ['name' => 'Night Differential', 'amount' => $payrollRecord->night_differential_pay];
        }
        if ($payrollRecord->regular_holiday_pay > 0) {
            $earnings[] = ['name' => 'Regular Holiday Pay', 'amount' => $payrollRecord->regular_holiday_pay];
        }
        if ($payrollRecord->special_holiday_pay > 0) {
            $earnings[] = ['name' => 'Special Holiday Pay', 'amount' => $payrollRecord->special_holiday_pay];
        }
        if ($payrollRecord->overtime_pay > 0) {
            $earnings[] = ['name' => 'Overtime Pay', 'amount' => $payrollRecord->overtime_pay];
        }

        // Prepare deductions array
        $deductions = [];
        if ($payrollRecord->sss_contribution > 0) {
            $deductions[] = ['name' => 'SSS', 'amount' => $payrollRecord->sss_contribution];
        }
        if ($payrollRecord->philhealth_contribution > 0) {
            $deductions[] = ['name' => 'PhilHealth', 'amount' => $payrollRecord->philhealth_contribution];
        }
        if ($payrollRecord->pagibig_contribution > 0) {
            $deductions[] = ['name' => 'Pag-IBIG', 'amount' => $payrollRecord->pagibig_contribution];
        }
        if ($payrollRecord->loan_deduction > 0) {
            $deductions[] = ['name' => 'Loan Deduction', 'amount' => $payrollRecord->loan_deduction];
        }
        if ($payrollRecord->other_deductions > 0) {
            $deductions[] = ['name' => 'Other Deductions', 'amount' => $payrollRecord->other_deductions];
        }

        return [
            'id' => $payrollRecord->id,
            'employeeId' => $payrollRecord->employee_id,
            'employeeName' => $payrollRecord->first_name . ' ' . $payrollRecord->last_name,
            'position' => $payrollRecord->position,
            'salary' => $payrollRecord->salary,
            'presentDays' => $presentDays,
            'undertime' => $undertimeMinutes,
            'tardy' => $lateMinutes,
            'withholdingTax' => $payrollRecord->tax_withheld,
            'payroll' => $payrollCode,
            'payrollStartDate' => $payrollRecord->payroll_start_date,
            'payrollEndDate' => $payrollRecord->payroll_end_date,
            'grossPay' => $payrollRecord->gross_pay,
            'basicHoursPay' => $payrollRecord->basic_hours_pay,
            'totalDeductions' => $payrollRecord->total_deductions,
            'netPay' => $payrollRecord->net_pay,
            'earnings' => $earnings,
            'deductions' => $deductions
        ];
    }
}