<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\Employee;
use App\Models\DTR;
use App\Models\Overtime;
use App\Models\PayrollData; // Use the new PayrollData model
use App\Models\Sss_contributions;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; // For getting the authenticated user's ID

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
        //   dd("Monthly Salary for SSS computation:", $monthlySalary);
        // Find the SSS contribution record that matches the salary range
        $sssContributionRecord = Sss_contributions::where('salary_range_from', '<=', $monthlySalary)
                                                ->where('salary_range_to', '>=', $monthlySalary)
                                                ->first();

    // dd("SSS Contribution Record Found:", $sssContributionRecord);
        if ($sssContributionRecord) {
            $totalEmployeeContribution = $sssContributionRecord->reg_ee_share + $sssContributionRecord->wisp_ee_share;
            return $totalEmployeeContribution;
        }

        // If no matching record is found, return 0 or handle as an error
        return 0.00;
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

            //SSS deduction
              $sssContribution = $this->computeSSSContribution($employee->salary);
            
            // Initialize statutory contributions and tax to 0 for now
            // $sssContribution = 0.00;
            $philhealthContribution = 0.00;
            $pagibigContribution = 0.00;
            $taxWithheld = 0.00;
            $otherDeductions = 0.00; // Placeholder for other custom deductions

            $totalDeductions = $lateDeduction + $undertimeDeduction + 
                               $sssContribution + $philhealthContribution + 
                               $pagibigContribution + $taxWithheld + $otherDeductions;

            // Overtime Pay Calculation (Adjust rates according to your company policy)
            $regularOvertimePay = $totalApprovedOvertimeHours * ($hourlyRate * 1.25); 
            $regHolidayPay = $totalRegHolidayHours * ($hourlyRate * 2.00); 
            $specHolidayPay = $totalSpecHolidayHours * ($hourlyRate * 1.30); 
            $nightDifferentialPay = $totalNightDiffHours * ($hourlyRate * 0.10); 
            // Note: Night diff on regular/special holidays might be included in reg/spec holiday pay,
            // or calculated as a separate premium on top of the holiday rate.
            // For simplicity, summing them up here based on provided DTR fields.
            $nightDifferentialPay += $totalNightDiffRegHours * ($hourlyRate * 2.10); // Example: Night diff on regular holiday
            $nightDifferentialPay += $totalNightDiffSpecHours * ($hourlyRate * 1.40); // Example: Night diff on special holiday

            $overtimePay = $regularOvertimePay + $regHolidayPay + $specHolidayPay + $nightDifferentialPay;

            // Gross Pay
            $grossPay = $basicHoursPay + $overtimePay; // Add other income like allowances here if applicable

            // Net Pay
            $netPay = $grossPay - $totalDeductions;

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
                'sss_contribution' => round($sssContribution, 2), // Placeholder
                'philhealth_contribution' => round($philhealthContribution, 2), // Placeholder
                'pagibig_contribution' => round($pagibigContribution, 2), // Placeholder
                'tax_withheld' => round($taxWithheld, 2), // Placeholder
                'other_deductions' => round($otherDeductions, 2), // Placeholder
                'total_deductions' => round($totalDeductions, 2),
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
            $existingRecords = PayrollData::where('payroll_id', $payroll->id)->count();
            if ($existingRecords > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll for this period has already been saved.'
                ], 409); // 409 Conflict
            }

            foreach ($request->input('payroll_results') as $result) {
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
                    'processed_by' => Auth::id(), // Get the ID of the authenticated user
                ]);
            }

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
}