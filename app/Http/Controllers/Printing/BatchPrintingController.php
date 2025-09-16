<?php

namespace App\Http\Controllers\Printing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Payroll;
use App\Models\PayrollData;
use App\Models\Employee;

class BatchPrintingController extends Controller
{
    /**
     * Display a listing of the active payroll period with payslips
     */
    public function index(Request $request)
{
    Log::info('Attempting to retrieve payroll for batch printing.');

    // Get all payrolls for dropdown
    $allPayrolls = Payroll::orderBy('id', 'desc')->get();

    // Get selected payroll ID from request
    $selectedPayrollId = $request->input('payroll_id');
    
    // If no payroll selected, get the latest processed payroll
    if (!$selectedPayrollId) {
        $payroll = Payroll::where('status', 'Processed')->orderBy('id', 'desc')->first();
    } else {
        $payroll = Payroll::find($selectedPayrollId);
    }

    if (!$payroll) {
        return view('HR.payslip.batch', [
            'payroll' => null,
            'payslips' => collect(),
            'message' => 'No payroll period found.',
            'departments' => collect(),
            'selectedDepartment' => null,
            'allPayrolls' => $allPayrolls,
            'selectedPayrollId' => $selectedPayrollId,
        ]);
    }

    // Get distinct departments
    $departments = Employee::select('department')->distinct()->get();

    // Load payslips
    $payslipsQuery = PayrollData::with('employee')
        ->where('payroll_id', $payroll->id);

    // Apply filter if department is selected
    $selectedDepartment = $request->input('department');
    if (!empty($selectedDepartment)) {
        $payslipsQuery->whereHas('employee', function ($query) use ($selectedDepartment) {
            $query->where('department', $selectedDepartment);
        });
    }

    $payslips = $payslipsQuery->get();

    return view('HR.payslip.batch', [
        'payroll' => $payroll,
        'payslips' => $payslips,
        'message' => null,
        'departments' => $departments,
        'selectedDepartment' => $selectedDepartment,
        'allPayrolls' => $allPayrolls,
        'selectedPayrollId' => $selectedPayrollId,
    ]);
}

    /** 
     * Show payslips for a specific payroll period
     */
    public function show(string $id)
    {
        Log::info("Attempting to show single payslip details for PayrollData ID: {$id}");

        try {
            // Find the specific payslip data record and eager load its relations
            $payslip = PayrollData::with('employee', 'payroll')->findOrFail($id);
            $payroll = $payslip->payroll; 

            $employee = $payslip->employee;
            $dailyrate = $employee->salary / 22;
            $hourlyrate = $dailyrate / 8;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Redirect back with an error if the record is not found
            return redirect()->route('HR.payslip.batch')->with('error', 'Payslip record not found.');
        }

        // Return the dedicated payslip view with the payslip and payroll data
        return view('HR.payslip.payslip_single', [
            'payslip' => $payslip,
            'payroll' => $payroll,
            'dailyrate' => $dailyrate,
            'hourlyrate' => $hourlyrate,
            'employee' => $employee,
        ]);
    }

    /**
     * Display all payslips for batch printing with two payslips per page layout
     */
    public function batchPrint(Request $request)
    {
        Log::info('Attempting to retrieve payslips for batch printing.');

        // Get selected payroll ID from request
        $selectedPayrollId = $request->input('payroll_id');
        
        // If no payroll selected, get the latest processed payroll
        if (!$selectedPayrollId) {
            $payroll = Payroll::where('status', 'Processed')->orderBy('id', 'desc')->first();
        } else {
            $payroll = Payroll::find($selectedPayrollId);
        }

        if (!$payroll) {
            return view('HR.payslip.batch_print', [
                'payroll' => null,
                'payslips' => collect(),
                'message' => 'No payroll period found.',
            ]);
        }

        // Load payslips
        $payslipsQuery = PayrollData::with('employee')
            ->where('payroll_id', $payroll->id);

        // Apply filter if department is selected
        $selectedDepartment = $request->input('department');
        if (!empty($selectedDepartment)) {
            $payslipsQuery->whereHas('employee', function ($query) use ($selectedDepartment) {
                $query->where('department', $selectedDepartment);
            });
        }

        $payslips = $payslipsQuery->get();

        return view('HR.payslip.batch_print', [
            'payroll' => $payroll,
            'payslips' => $payslips,
            'message' => null,
        ]);
    }

    // Unused methods for now
    public function create() {}
    public function store(Request $request) {}
    public function edit(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}
}