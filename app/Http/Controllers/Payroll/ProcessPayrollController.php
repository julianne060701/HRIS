<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\Employee;
use App\Models\DTR;
use App\Models\Overtime;
use App\Models\PayrollData;
use App\Models\Sss_contributions;
use App\Models\Employee_deduction;
use App\Models\Loan; // Added for computeLoanDeduction
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Added for logging in computeLoanDeduction

class ProcessPayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payrollPeriods = Payroll::orderBy('to_date', 'desc')
                                ->select('id', 'payroll_code', 'title', 'from_date', 'to_date')
                                ->get();

        // Return the view, passing the payroll periods.
        return view('HR.payroll.process', compact('payrollPeriods'));
    }

    public function fetchPayrollDateRanges()
    {
        try {
            
            $payrollPeriods = Payroll::orderBy('to_date', 'desc')->get();

            $formattedPayrollPeriods = $payrollPeriods->map(function ($payroll) {
                return [
                    'id' => $payroll->id,
                    'payroll_code' => $payroll->payroll_code, // Include code for identification
                    'title' => $payroll->title,
                    'from_date' => $payroll->from_date->format('Y-m-d'), // Format dates as YYYY-MM-DD
                    'to_date' => $payroll->to_date->format('Y-m-d'),
                ];
            });

            // Return a JSON response with the fetched and formatted payroll periods.
            return response()->json([
                'message' => 'Payroll date ranges fetched successfully.',
                'data' => $formattedPayrollPeriods
            ], 200);

        } catch (\Exception $e) {
            // Catch any exceptions (e.g., database connection issues) and return an error response.
            return response()->json([
                'message' => 'Failed to fetch payroll date ranges.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetches and displays a specific payroll period's date range by its ID.
     *
     * @param  string  $id  The ID of the payroll period.
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchSpecificPayrollDateRange(string $id)
    {
        try {
            // Find the payroll period by its ID.
            $payrollPeriod = Payroll::find($id);

            // If the payroll period is not found, return a 404 Not Found response.
            if (!$payrollPeriod) {
                return response()->json([
                    'message' => 'Payroll period not found.'
                ], 404);
            }

            // Transform the single model to only include the necessary date range information.
            $formattedPayrollPeriod = [
                'id' => $payrollPeriod->id,
                'payroll_code' => $payrollPeriod->payroll_code,
                'title' => $payrollPeriod->title,
                'from_date' => $payrollPeriod->from_date->format('Y-m-d'),
                'to_date' => $payrollPeriod->to_date->format('Y-m-d'),
            ];

            // Return a JSON response with the specific payroll period's date range.
            return response()->json([
                'message' => 'Specific payroll date range fetched successfully.',
                'data' => $formattedPayrollPeriod
            ], 200);

        } catch (\Exception $e) {
            // Catch any exceptions and return an error response.
            return response()->json([
                'message' => 'Failed to fetch specific payroll date range.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource and trigger payroll computation.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $id)
    {
        $payrollPeriod = Payroll::find($id);

        if (!$payrollPeriod) {
            return redirect()->route('HR.payroll.process')->with('error', 'Payroll period not found.');
        }

        // Trigger the payroll computation for the selected period
        $payrollResults = $this->computePayroll($payrollPeriod);

        // Pass the payroll period and results to the results view
        return view('HR.payroll.new_result', compact('payrollPeriod', 'payrollResults'));
    }

    protected function computeSSSContribution(float $monthlySalary): float
    {
        // Find the SSS contribution record that matches the salary range
        $sssContributionRecord = Sss_contributions::where('salary_range_from', '<=', $monthlySalary)
                                                    ->where('salary_range_to', '>=', $monthlySalary)
                                                    ->first();

        if ($sssContributionRecord) {
            $totalEmployeeContribution = $sssContributionRecord->reg_ee_share + $sssContributionRecord->wisp_ee_share;
            return $totalEmployeeContribution;
        }

        // If no matching record is found, return 0 or handle as an error
        return 0.00;
    }

    // New method to compute SSS Employer Contribution
    protected function computeSSSEmployerContribution(float $monthlySalary): float
    {
        $sssContributionRecord = Sss_contributions::where('salary_range_from', '<=', $monthlySalary)
                                                    ->where('salary_range_to', '>=', $monthlySalary)
                                                    ->first();

        if ($sssContributionRecord) {
            $totalEmployerContribution = $sssContributionRecord->reg_er_share + $sssContributionRecord->wisp_er_share;
            return $totalEmployerContribution;
        }

        return 0.00;
    }


    protected function ComputePagibigEmployeeShare(float $monthlySalary): float
    {
        $employeeRate = 0;
        $maxsalary = 10000;
        $maxcontrib = 200; 

        if ($monthlySalary <= 1500) {
            $employeeRate = 0.01; 
        } else {
            $employeeRate = 0.02;
        }

        
        $employeeContribution = min($monthlySalary, $maxsalary) * $employeeRate;
        $employeeContribution = min($employeeContribution, $maxcontrib);

        return $employeeContribution;
    }

    protected function ComputePagibigEmployerShare(float $monthlySalary): float
    {
        $employerRate = 0;
        $maxsalaryER = 10000;
        $maxcontER = 200;

        if ($monthlySalary <= 1500) {
            $employerRate = 0.02; // 2%
        } else {
            $employerRate = 0.02; // 2%
        }

        
        $employerContribution = min($monthlySalary, $maxsalaryER) * $employerRate;
        $employerContribution = min($employerContribution, $maxcontER);

        return $employerContribution;
    }

    protected function ComputePhicEe(float $monthlySalary): float
    {
        $employeeRate = 0.025;
        $minsalaryEe = 10000;
        $maxsalaryEe = 100000;

        $psalary = $monthlySalary;

        if ($psalary <= $minsalaryEe) {
            $psalary = $minsalaryEe;
        } elseif ($psalary >=$maxsalaryEe) {
            $psalary = $maxsalaryEe;
        }

        $employeeContribution = $psalary * $employeeRate;

        return $employeeContribution;
    }

    protected function ComputePhicEr(float $monthlySalary): float
    {
        $employeeRate = 0.025;
        $minsalaryEe = 10000;
        $maxsalaryEe = 100000;

        $psalary = $monthlySalary;

        if ($psalary <= $minsalaryEe) {
            $psalary = $minsalaryEe;
        } elseif ($psalary >=$maxsalaryEe) {
            $psalary = $maxsalaryEe;
        }

        $employeeContribution = $psalary * $employeeRate;

        return $employeeContribution;
    }

    protected function computeWithholdingTax(float $monthlyTaxableIncome): float
    {
        // The taxable income here is expected to be the MONTHLY taxable income
        $tax = 0.00;

        if ($monthlyTaxableIncome <= 10146.50) {
            $tax = 0; // 0%
        } elseif ($monthlyTaxableIncome <= 16662) {
            $tax = 0 + (0.15 * ($monthlyTaxableIncome - 10146.50));
        } elseif ($monthlyTaxableIncome <= 33333) {
            $tax = 1875.00 + (0.20 * ($monthlyTaxableIncome - 16662));
        } elseif ($monthlyTaxableIncome <= 83333) {
            $tax = 8541.80 + (0.25 * ($monthlyTaxableIncome - 33333));
        } elseif ($monthlyTaxableIncome <= 333333) {
            $tax = 33541.80 + (0.30 * ($monthlyTaxableIncome - 83333));
        } else {
            $tax = 183541.80 + (0.35 * ($monthlyTaxableIncome - 333333));
        }

        return max(0, round($tax, 2));
    }


    /**
     * Computes and applies loan deductions for a given employee within a payroll period.
     * Updates the loan balance and status in the database.
     *
     * @param string $employeeId The ID of the employee.
     * @param Carbon $payrollFromDate The start date of the payroll period.
     * @param Carbon $payrollToDate The end date of the payroll period.
     * @return float The total loan deduction for the current payroll period.
     */
    protected function computeLoanDeduction(string $employeeId, Carbon $payrollFromDate, Carbon $payrollToDate): float
    {
        $totalLoanDeduction = 0.00;

        // Fetch all active loans for the employee that are due within or before this payroll period
        $activeLoans = Loan::where('employee_id', $employeeId)
                            ->where('status', 'active')
                            ->where('start_date', '<=', $payrollToDate) // Loan started on or before payroll end date
                            ->get();

        Log::info("Employee {$employeeId}: Checking for active loans for period {$payrollFromDate->format('Y-m-d')} to {$payrollToDate->format('Y-m-d')}. Found " . $activeLoans->count() . " active loans.");

        foreach ($activeLoans as $loan) {
            // Determine the amount to deduct for this specific loan in this period
            // It should be the amortization amount, but not more than the remaining balance.
            $deductionAmount = min($loan->amortization_amount, $loan->balance);

            if ($deductionAmount > 0) {
                $totalLoanDeduction += $deductionAmount;

                // Update the loan balance
                $loan->balance -= $deductionAmount;

                // If balance is zero or less, mark the loan as paid
                if ($loan->balance <= 0.00) {
                    $loan->balance = 0.00; // Ensure balance is exactly zero
                    $loan->status = 'paid';
                    Log::info("Employee {$employeeId}: Loan ID {$loan->id} fully paid. Balance: {$loan->balance}");
                }

                // Save the updated loan record
                $loan->save();
                Log::info("Employee {$employeeId}: Loan ID {$loan->id} deducted {$deductionAmount}. New balance: {$loan->balance}. Status: {$loan->status}");
            } else {
                Log::info("Employee {$employeeId}: Loan ID {$loan->id} has zero or negative deduction amount or balance. Skipping.");
            }
        }

        return round($totalLoanDeduction, 2);
    }


    /**
     * Computes payroll for a given payroll period.
     * The keys in the returned array are aligned with PayrollData model's fillable fields.
     *
     * @param  \App\Models\Payroll  $payrollPeriod
     * @return array
     */
    protected function computePayroll(Payroll $payrollPeriod)
    {
        $fromDate = $payrollPeriod->from_date;
        $toDate = $payrollPeriod->to_date;
        
        $isSecondCutoff = ($toDate->day >= 11 && $toDate->day <= 25);

        $employees = Employee::all(); // Fetch all employees

        $payrollResults = [];

        foreach ($employees as $employee) {
            // Assuming 22 working days in a month for daily rate calculation
            // And 8 working hours per day for hourly rate calculation
            $dailyRate = $employee->salary / 22; 
            $hourlyRate = $dailyRate / 8; 

            $totalHoursWorked = 0;
            $totalLateMinutes = 0;
            $totalUndertimeMinutes = 0;
            $totalApprovedOvertimeHours = 0;
            $totalRegHolidayHours = 0;
            $totalSpecHolidayHours = 0;
            $totalNightDiffHours = 0;
            $totalNightDiffRegHours = 0;
            $totalNightDiffSpecHours = 0;


            // Fetch DTR records for the employee within the payroll period
            $dtrRecords = DTR::where('employee_id', $employee->employee_id)
                             ->whereBetween('transindate', [$fromDate, $toDate])
                             ->get();

            foreach ($dtrRecords as $dtr) {
                $totalHoursWorked += $dtr->total_hours ?? 0; 
                $totalLateMinutes += $dtr->late_minutes ?? 0;
                $totalUndertimeMinutes += $dtr->undertime_minutes ?? 0;
                $totalRegHolidayHours += $dtr->reg_holiday_hours ?? 0;
                $totalSpecHolidayHours += $dtr->spec_holiday_hours ?? 0;
                $totalNightDiffHours += $dtr->night_diff ?? 0;
                $totalNightDiffRegHours += $dtr->night_diff_reg ?? 0;
                $totalNightDiffSpecHours += $dtr->night_diff_spec ?? 0;
            }

            // Fetch approved overtime records for the employee within the payroll period
            $overtimeRecords = Overtime::where('employee_id', $employee->employee_id)
                                       ->whereBetween('ot_date', [$fromDate, $toDate])
                                       ->where('is_approved', 1) // Assuming '1' means approved
                                       ->get();

            foreach ($overtimeRecords as $ot) {
                $totalApprovedOvertimeHours += $ot->approved_hours ?? 0;
                $totalRegHolidayHours += $ot->ot_reg_holiday_hours ?? 0; 
                $totalSpecHolidayHours += $ot->ot_spec_holiday_hours ?? 0; 
            }

            // Basic Pay Calculation based on actual hours worked
            $basicHoursPay = ($totalHoursWorked / 8) * $dailyRate; 

            // Deductions for Lates and Undertimes
            $lateDeduction = ($totalLateMinutes / 60) * $hourlyRate;
            $undertimeDeduction = ($totalUndertimeMinutes / 60) * $hourlyRate;

            // SSS, Pag-IBIG, PhilHealth, and Tax computations (Employee & Employer Shares)
            $sssEmployeeContribution = $this->computeSSSContribution($employee->salary);
            $sssEmployerContribution = $this->computeSSSEmployerContribution($employee->salary); // New: Employer SSS
            $pagibigEmployeeContribution = $this->ComputePagibigEmployeeShare($employee->salary);
            $pagibigEmployerContribution = $this->ComputePagibigEmployerShare($employee->salary);
            $philhealthEmployeeContribution = $this->ComputePhicEe($employee->salary);
            $philhealthEmployerContribution = $this->ComputePhicEr($employee->salary);
            $taxWithheld = $this->computeWithholdingTax($employee->salary);
            
            // Compute loan deductions
            $loanDeduction = $this->computeLoanDeduction($employee->employee_id, $fromDate, $toDate);
            
            $otherDeductions = 0.00; // Placeholder for other custom deductions

            $totalEmployeeDeductions = $lateDeduction + $undertimeDeduction + 
                                       $sssEmployeeContribution + $philhealthEmployeeContribution + 
                                       $pagibigEmployeeContribution + $taxWithheld + $loanDeduction + $otherDeductions;

            $totalEmployerContributions = $sssEmployerContribution + $pagibigEmployerContribution + $philhealthEmployerContribution;


            // Overtime Pay Calculation (Adjust rates according to your company policy)
            $regularOvertimePay = $totalApprovedOvertimeHours * ($hourlyRate * 1.25); 
            $regHolidayPay = $totalRegHolidayHours * ($hourlyRate * 2.00); 
            $specHolidayPay = $totalSpecHolidayHours * ($hourlyRate * 1.30); 
            $nightDifferentialPay = $totalNightDiffHours * ($hourlyRate * 0.10); 
            $nightDifferentialPay += $totalNightDiffRegHours * ($hourlyRate * 2.10); // Example: Night diff on regular holiday
            $nightDifferentialPay += $totalNightDiffSpecHours * ($hourlyRate * 1.40); // Example: Night diff on special holiday

            $overtimePay = $regularOvertimePay + $regHolidayPay + $specHolidayPay + $nightDifferentialPay;

            // Gross Pay
            $grossPay = $basicHoursPay + $overtimePay; // Add other income like allowances here if applicable

            // Net Pay
            $netPay = $grossPay - $totalEmployeeDeductions;

            $payrollResults[] = [
                'employee_id' => $employee->employee_id,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name, // For display in blade
                'payroll_start_date' => $fromDate->format('Y-m-d'),
                'payroll_end_date' => $toDate->format('Y-m-d'),
                'gross_pay' => round($grossPay, 2),
                'basic_hours_pay' => round($basicHoursPay, 2),
                'night_differential_pay' => round($nightDifferentialPay, 2),
                'regular_holiday_pay' => round($regHolidayPay, 2),
                'special_holiday_pay' => round($specHolidayPay, 2),
                'overtime_pay' => round($overtimePay, 2),
                'late_deduction' => round($lateDeduction, 2),
                'undertime_deduction' => round($undertimeDeduction, 2),
                'sss_contribution' => round($sssEmployeeContribution, 2), // Employee SSS
                'sss_employer_contribution' => round($sssEmployerContribution, 2), // New: Employer SSS
                'philhealth_contribution' => round($philhealthEmployeeContribution, 2), // Employee PhilHealth
                'philhealth_employer_contribution' => round($philhealthEmployerContribution, 2), // New: Employer PhilHealth
                'pagibig_contribution' => round($pagibigEmployeeContribution, 2), // Employee Pag-IBIG
                'pagibig_employer_contribution' => round($pagibigEmployerContribution, 2), // New: Employer Pag-IBIG
                'tax_withheld' => round($taxWithheld, 2),
                'loan_deduction' => round($loanDeduction, 2), // New: Loan Deduction
                'other_deductions' => round($otherDeductions, 2),
                'total_deductions' => round($totalEmployeeDeductions, 2), // Total Employee Deductions
                'total_employer_contributions' => round($totalEmployerContributions, 2), // New: Total Employer Contributions
                'net_pay' => round($netPay, 2),

                // Include these for detailed view in blade, not directly saved to PayrollData
                'daily_rate' => round($dailyRate, 2),
                'hourly_rate' => round($hourlyRate, 2),
                'total_hours_worked' => round($totalHoursWorked, 2),
                'total_late_minutes' => $totalLateMinutes,
                'total_undertime_minutes' => $totalUndertimeMinutes,
                'total_approved_overtime_hours' => round($totalApprovedOvertimeHours, 2),
                'total_reg_holiday_hours' => round($totalRegHolidayHours, 2),
                'total_spec_holiday_hours' => round($totalSpecHolidayHours, 2),
                'total_night_diff_hours' => round($totalNightDiffHours, 2),
                'total_night_diff_reg_hours' => round($totalNightDiffRegHours, 2),
                'total_night_diff_spec_hours' => round($totalNightDiffSpecHours, 2),
                'regular_overtime_sub_pay' => round($regularOvertimePay, 2), // Sub-component for modal
            ];
        }

        return $payrollResults;
    }

    /**
     * Store the computed payroll results in the database using PayrollData model.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Payroll $payroll
     * @return \Illuminate\Http\JsonResponse
     */
public function savePayroll(Request $request, Payroll $payroll)
{
    $request->validate([
        'payroll_results' => 'required|array',
        'payroll_results.*.employee_id' => 'required|string',
        'payroll_results.*.gross_pay' => 'required|numeric',
        'payroll_results.*.net_pay' => 'required|numeric',
        // Add more validation rules for other fields if necessary
    ]);

    try {
        // Prevent duplicate saving for the same payroll period
        $existingPayrollDataRecords = PayrollData::where('payroll_id', $payroll->id)->count();
        $existingDeductionRecords = Employee_deduction::where('payroll_id', $payroll->id)->count();

        if ($existingPayrollDataRecords > 0 || $existingDeductionRecords > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll for this period has already been saved.'
            ], 409); // 409 Conflict
        }

        foreach ($request->input('payroll_results') as $result) {
            // Save to PayrollData model
            PayrollData::create([
                'payroll_id' => $payroll->id,
                'employee_id' => $result['employee_id'],
                'payroll_start_date' => $result['payroll_start_date'],
                'payroll_end_date' => $result['payroll_end_date'],
                'gross_pay' => $result['gross_pay'],
                'basic_hours_pay' => $result['basic_hours_pay'],
                'night_differential_pay' => $result['night_differential_pay'],
                'regular_holiday_pay' => $result['regular_holiday_pay'],
                'special_holiday_pay' => $result['special_holiday_pay'],
                'overtime_pay' => $result['overtime_pay'],
                'late_deduction' => $result['late_deduction'],
                'undertime_deduction' => $result['undertime_deduction'],
                'sss_contribution' => $result['sss_contribution'], 
                'philhealth_contribution' => $result['philhealth_contribution'], 
                'pagibig_contribution' => $result['pagibig_contribution'], 
                'tax_withheld' => $result['tax_withheld'],
                'other_deductions' => $result['other_deductions'], 
                'total_deductions' => $result['total_deductions'],
                'net_pay' => $result['net_pay'],
                'processed_by' => Auth::id(),
            ]);

            // Save to Employee_deduction model
            Employee_deduction::create([
                'employee_id' => $result['employee_id'],
                'payroll_id' => $payroll->id,
                'monthly_salary' => $result['gross_pay'],
                'sss_employee_contribution' => $result['sss_contribution'],
                'sss_employer_contribution' => $result['sss_employer_contribution'],
                'pagibig_employee_contribution' => $result['pagibig_contribution'],
                'pagibig_employer_contribution' => $result['pagibig_employer_contribution'],
                'philhealth_employee_contribution' => $result['philhealth_contribution'],
                'philhealth_employer_contribution' => $result['philhealth_employer_contribution'],
                'withholdingtax' => $result['tax_withheld'],
                'total_employee_deduction' => $result['total_deductions'],
                'total_employer_deduction' => $result['total_employer_contributions'], 
            ]);
        }

        // Update the payroll's status in the payrolls table
        $payroll->status = 'Processed';
        $payroll->save();

        return response()->json([
            'success' => true,
            'message' => 'Payroll successfully saved for ' . $payroll->title
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to save payroll.',
            'error' => $e->getMessage()
        ], 500);
    }
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
        // This 'store' method is currently unused and can be repurposed
        // or removed if not needed for other functionalities.
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
}