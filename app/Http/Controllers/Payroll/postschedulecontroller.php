<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SchedPost; // This model seems unused, consider removing if not needed
use App\Models\Schedule;
use App\Models\EmployeeSchedule;
use App\Models\LeaveType;
use App\Models\Employee; // Ensure this is imported

class postschedulecontroller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch payroll data for the dropdown
        $payrollData = DB::table('payrolls')
            ->select('id', 'payroll_code', 'title', 'from_date', 'to_date')
            ->where('status', 'Active') // Only show active payrolls
            ->orderBy('created_at', 'desc')
            ->get();

        // Convert to array format for consistency
        $payrollData = $payrollData->map(function($payroll) {
            return [
                'id' => $payroll->id,
                'payroll_code' => $payroll->payroll_code,
                'title' => $payroll->title,
                'from_date' => $payroll->from_date,
                'to_date' => $payroll->to_date,
            ];
        })->toArray();

        return view('HR.attendance.postsched', compact('payrollData'));
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
    private function getLeaveTypeName(?int $leaveTypeId): ?string
    {
        if (is_null($leaveTypeId)) {
            return null;
        }

        $leaveType = LeaveType::find($leaveTypeId);

        return $leaveType ? $leaveType->name : null;
    }

    public function store(Request $request)
    {
        $schedules = $request->input('schedule');
        $payrollId = $request->input('payroll_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$schedules || !is_array($schedules)) {
            return response()->json(['message' => 'No schedules provided.'], 422);
        }

        if (!$payrollId) {
            return response()->json(['message' => 'Payroll ID is required.'], 422);
        }

        $updatedCount = 0;

        foreach ($schedules as $employeeId => $dates) {
            foreach ($dates as $date => $selectedOption) {
                $actualShiftCode = null;
                $leaveTypeId = null;

                $leaveType = LeaveType::where('name', $selectedOption)->first();

                if ($leaveType) {
                    $leaveTypeId = $leaveType->id;
                    $actualShiftCode = null;
                } elseif ($selectedOption === '') {
                    $actualShiftCode = null;
                    $leaveTypeId = null;
                } else {
                    $actualShiftCode = $selectedOption;
                    $leaveTypeId = null;
                }

                EmployeeSchedule::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'date' => $date
                    ],
                    [
                        'shift_code' => $actualShiftCode,
                        'leave_type_id' => $leaveTypeId,
                        'payroll_id' => $payrollId, // Associate with payroll
                    ]
                );
                $updatedCount++;
            }
        }

        return response()->json([
            'message' => "Schedules posted successfully! Updated {$updatedCount} schedule entries for payroll period {$startDate} to {$endDate}."
        ]);
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

    public function getScheduleData(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $department = $request->query('department');

        $query = Employee::query();

        if ($department) {
            $query->whereRaw('LOWER(department) = ?', [strtolower($department)]);
        }

        $employees = $query->get();

        $result = [];
        foreach ($employees as $employee) {
            $result[] = [
                'employee_id' => $employee->employee_id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                // Directly use the getShiftsBetween method from the Employee model
                'schedules' => $employee->getShiftsBetween($from, $to),
            ];
        }

        return response()->json($result);
    }

    public function getShifts()
    {
        $regularShifts = Schedule::select('shift_code')
            ->distinct()
            ->whereNotNull('shift_code')
            ->pluck('shift_code');

        // Fetch all leave type names
        $leaveTypeNames = LeaveType::pluck('name');

        // Combine them, ensure uniqueness, sort, and re-index the array
        $allAvailableOptions = $regularShifts->merge($leaveTypeNames)->unique()->sort()->values();

        return response()->json($allAvailableOptions);
    }
}