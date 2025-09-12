<?php

namespace App\Http\Controllers\payroll;
use App\Models\DTR;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class Attendance extends Controller
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

        // Convert to array format for consistency with your blade template
        $payrollData = $payrollData->map(function($payroll) {
            return [
                'id' => $payroll->id,
                'payroll_code' => $payroll->payroll_code,
                'title' => $payroll->title,
                'from_date' => $payroll->from_date,
                'to_date' => $payroll->to_date,
            ];
        })->toArray();

        return view('hr.attendance.importdtr', compact('payrollData'));
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
        // Get filter dates
        $minDate = $request->input('minDate');
        $maxDate = $request->input('maxDate');

        $query = DB::table('attendance')
        ->select('id','employee_id', 'transindate', 'time_in', 'transoutdate', 'time_out', 'total_hours');

        // Apply date filters if provided
        if ($minDate && $maxDate) {
            $query->whereBetween('transindate', [$minDate, $maxDate]);
        }

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    public function importFromAttendance(Request $request)
    {
        $payrollId = $request->input('payroll_id'); // Get payroll ID
        $minDate = $request->input('minDate');
        $maxDate = $request->input('maxDate');

        $query = DB::table('attendance')
            ->select('id', 'employee_id', 'transindate', 'time_in', 'transoutdate', 'time_out');

        if ($minDate && $maxDate) {
            $query->whereBetween('transindate', [$minDate, $maxDate]);
        }

        $attendanceData = $query->get();

        $importedCount = 0;
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
                    'payroll_id'   => $payrollId, // Associate with payroll
                    'transindate'  => $row->transindate,
                    'time_in'      => $row->time_in ?? null,
                    'transoutdate' => $row->transoutdate,
                    'time_out'     => $row->time_out ?? null,
                    'total_hours'  => round($totalHours, 2),
                    'night_diff'   => 0,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $importedCount++;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Successfully imported {$importedCount} attendance records into DTR for the selected payroll period."
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