<?php

namespace App\Http\Controllers\payroll;
use App\Models\DTR;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Attendance extends Controller
{
    /**
     * Get the active payroll date range
     */
    private function getPayrollDateRange()
    {
        // First, check for an active payroll record
        $activePayroll = Payroll::where('status', 'active')->first();

        if ($activePayroll) {
            return [
                'start_date' => $activePayroll->from_date->toDateString(),
                'end_date' => $activePayroll->to_date->toDateString(),
                'payroll' => $activePayroll
            ];
        }

        // If no 'active' payroll is found, fall back to the latest one
        $latestPayroll = Payroll::orderBy('created_at','desc')->first();
        if ($latestPayroll) {
            return [
                'start_date' => $latestPayroll->from_date->toDateString(),
                'end_date' => $latestPayroll->to_date->toDateString(),
                'payroll' => $latestPayroll
            ];
        }

        // Default to the current month if no payroll data is found
        return [
            'start_date' => Carbon::now()->startOfMonth()->toDateString(),
            'end_date' => Carbon::now()->endOfMonth()->toDateString(),
            'payroll' => null
        ];
    }

   /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $payrollData = $this->getPayrollDateRange();
        
        // Log the dates being used for debugging
        Log::info("Displaying DTR data for the payroll cutoff: From {$payrollData['start_date']} to {$payrollData['end_date']}");

        return view('hr.attendance.importdtr', [
            'startDate' => $payrollData['start_date'],
            'endDate' => $payrollData['end_date'],
            'activePayroll' => $payrollData['payroll']
        ]);
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
        $attendanceData = $request->input('attendance_data');

        if (!empty($attendanceData)) {
            foreach ($attendanceData as $attendance) {
                $validated = Validator::make($attendance, [
                    'employee_id' => 'required|integer', // without exists for testing
                    'transindate'   => 'required|date',
                    'time_in'     => 'nullable|date_format:H:i:s',
                    'transoutdate'   => 'required|date',
                    'time_out'    => 'nullable|date_format:H:i:s',
                ])->validate();
                
                $exists = DTR::where('id', $validated['id'])
                 ->where('transindate', $validated['transindate'])
                 ->exists();

                if (!$exists) {
                    DTR::create([
                        'id'          => $validated['id'],
                        'employee_id' => $validated['employee_id'],
                        'transindate'   => $validated['transindate'],
                        'time_in'     => $validated['time_in'] ?? null,
                        'transoutdate'   => $validated['transoutdate'],
                        'time_out'    => $validated['time_out'] ?? null,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }

            return response()->json(['status' => 'success', 'message' => 'Attendance data stored successfully!']);
        }

        return response()->json(['status' => 'error', 'message' => 'No data provided.']);
    }

    public function storeAttendanceData(Request $request)
    {
        // Get the attendance data from the request
        $attendanceData = $request->input('attendance_data');

        // Check if there's any data
        if (!empty($attendanceData)) {
            foreach ($attendanceData as $attendance) {
                // Validate each attendance record
                $validated = Validator::make($attendance, [
                    'employee_id' => 'required|integer|exists:employees,id',
                    'transdate'   => 'required|date',
                    'time_in'     => 'nullable|date_format:H:i:s',
                    'time_out'    => 'nullable|date_format:H:i:s',
                ])->validate();

                // Optional: Check for duplicates before inserting
                $exists = DTR::where('employee_id', $validated['employee_id'])
                             ->where('transdate', $validated['transdate'])
                             ->exists();

                if (!$exists) {
                    DTR::create($validated);
                }
            }

            return response()->json(['status' => 'success', 'message' => 'Attendance data stored successfully!']);
        }

        return response()->json(['status' => 'error', 'message' => 'No data provided.']);
    }

    /**
     * Display the specified resource.
     */

     public function getAttendanceData(Request $request)
    {
        // Get filter dates from request, or use payroll dates as default
        $minDate = $request->input('minDate');
        $maxDate = $request->input('maxDate');
        
        // If no dates provided, use payroll cutoff dates
        if (!$minDate || !$maxDate) {
            $payrollData = $this->getPayrollDateRange();
            $minDate = $minDate ?: $payrollData['start_date'];
            $maxDate = $maxDate ?: $payrollData['end_date'];
        }

        $query = DB::table('attendance')
            ->select('id','employee_id', 'transindate', 'time_in', 'transoutdate', 'time_out', 'total_hours');

        // Apply date filters
        if ($minDate && $maxDate) {
            $query->whereBetween('transindate', [$minDate, $maxDate]);
        }

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    public function importFromAttendance(Request $request)
    {
        $minDate = $request->input('minDate');
        $maxDate = $request->input('maxDate');
        
        // If no dates provided, use payroll cutoff dates
        if (!$minDate || !$maxDate) {
            $payrollData = $this->getPayrollDateRange();
            $minDate = $minDate ?: $payrollData['start_date'];
            $maxDate = $maxDate ?: $payrollData['end_date'];
        }

        $query = DB::table('attendance')
            ->select('id', 'employee_id', 'transindate', 'time_in', 'transoutdate', 'time_out');

        if ($minDate && $maxDate) {
            $query->whereBetween('transindate', [$minDate, $maxDate]);
        }

        $attendanceData = $query->get();

        foreach ($attendanceData as $row) {
            $exists = DB::table('dtr')
                ->where('id', $row->id)
                ->exists();

            if (!$exists) {
                // Calculate total_hours using Carbon
                $totalHours = 0;
                if ($row->time_in && $row->time_out) {
                    $timeIn  = Carbon::parse($row->transindate . ' ' . $row->time_in);
                    $timeOut = Carbon::parse($row->transoutdate . ' ' . $row->time_out);
                    $totalHours = $timeOut->floatDiffInHours($timeIn);
                }

                DB::table('dtr')->insert([
                    'id'           => $row->id,
                    'employee_id'  => $row->employee_id,
                    'transindate'  => $row->transindate,
                    'time_in'      => $row->time_in ?? null,
                    'transoutdate' => $row->transoutdate,
                    'time_out'     => $row->time_out ?? null,
                    'total_hours'  => round($totalHours, 2),
                    'night_diff' => 0,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance data successfully imported into DTR with calculated total hours.'
        ]);
    }

    /**
     * Get payroll information for frontend
     */
    public function getPayrollInfo(Request $request)
    {
        $payrollData = $this->getPayrollDateRange();
        
        return response()->json([
            'start_date' => $payrollData['start_date'],
            'end_date' => $payrollData['end_date'],
            'payroll_title' => $payrollData['payroll'] ? $payrollData['payroll']->title : 'Current Month',
            'payroll_code' => $payrollData['payroll'] ? $payrollData['payroll']->payroll_code : null,
        ]);
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

    public function getProcessedDTR(Request $request)
    {
        try {
            $query = DB::table('employee_dtr')
                ->join('employees', 'employee_dtr.employee_id', '=', 'employees.id')
                ->select(
                    'employee_dtr.employee_id',
                    DB::raw("CONCAT(employees.first_name, ' ', employees.last_name) AS employee_name"),
                    'employee_dtr.date',
                    'employee_dtr.plotted_time_in',
                    'employee_dtr.plotted_time_out',
                    'employee_dtr.actual_time_in',
                    'employee_dtr.actual_time_out',
                    'employee_dtr.final_time_in',
                    'employee_dtr.final_time_out'
                );

            if ($request->minDate) {
                $query->whereDate('employee_dtr.date', '>=', $request->minDate);
            }

            if ($request->maxDate) {
                $query->whereDate('employee_dtr.date', '<=', $request->maxDate);
            }

            $data = $query->get();

            return response()->json(['data' => $data]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }
}