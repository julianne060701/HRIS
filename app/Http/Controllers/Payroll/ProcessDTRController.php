<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use App\Models\DTR; // Assuming this model maps to your processed_dtr or employee_dtr table
use App\Models\Employee; // Used for employee details, good to keep
use App\Models\Schedule; // Assuming you have a Schedule model for shift details
use Carbon\Carbon; // Ensure Carbon is imported

class ProcessDTRController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // This method is for displaying combined raw schedule and attendance data.
        // The calculations for late/undertime happen in the 'store' method,
        // and the 'getProcessedDTR' method would display the results of those calculations.
        $data = DB::table('employee_schedules')
            ->select(
                'employee_schedules.employee_id',
                'employee_schedules.shift_code',
                DB::raw("CONCAT(COALESCE(employees.first_name, ''), ' ', COALESCE(employees.last_name, '')) AS employee_name"),
                'employee_schedules.date',
                'schedule.xptd_time_in AS plotted_time_in', // Expected time in from schedule
                'schedule.xptd_time_out AS plotted_time_out', // Expected time out from schedule
                'attendance.time_in AS actual_time_in',      // Actual time in from attendance
                'attendance.time_out AS actual_time_out'     // Actual time out from attendance
            )
            ->leftJoin('employees', 'employee_schedules.employee_id', '=', 'employees.employee_id')
            ->leftJoin('schedule', 'employee_schedules.shift_code', '=', 'schedule.shift_code')
            ->leftJoin('attendance', function ($join) {
                // Join attendance records for the specific employee and date
                $join->on('employee_schedules.employee_id', '=', 'attendance.employee_id')
                    ->whereRaw('DATE(attendance.transindate) = employee_schedules.date');
            })
            ->orderByDesc('employee_schedules.date') // Order by date descending
            ->get();
        
        return view('HR.attendance.processdtr', ['data' => $data]);
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not implemented for this controller's primary function
    }

    /**
     * Store a newly created resource in storage (or update existing).
     * This method processes raw DTR data to calculate lates and undertime.
     */
    public function store(Request $request)
    {
        // Validate incoming DTR data from the form
        $validated = $request->validate([
            'dtrs.*.employee_id'     => 'required|exists:employees,employee_id',
            'dtrs.*.date'            => 'required|date',
            'dtrs.*.time_in'         => 'nullable|date_format:H:i:s', // Actual time in
            'dtrs.*.time_out'        => 'nullable|date_format:H:i:s', // Actual time out
            'dtrs.*.shift_code'      => 'nullable|string|max:20',
            'dtrs.*.xptd_time_in'    => 'nullable|date_format:H:i:s', // Expected time in from form (or fetched)
            'dtrs.*.xptd_time_out'   => 'nullable|date_format:H:i:s', // Expected time out from form (or fetched)
        ]);

        $dtrs = $request->input('dtrs', []);
        $gracePeriodMinutes = 10; // Define your grace period here, e.g., 10 minutes

        foreach ($dtrs as $dtr) {
            $employeeId = $dtr['employee_id'];
            $date = Carbon::parse($dtr['date'])->toDateString(); // Ensure date is 'YYYY-MM-DD'

            // Clean input: Convert empty strings to null for consistent handling
            $actualTimeInRaw = empty($dtr['time_in']) ? null : $dtr['time_in'];
            $actualTimeOutRaw = empty($dtr['time_out']) ? null : $dtr['time_out'];
            $plottedTimeInRaw = empty($dtr['xptd_time_in']) ? null : $dtr['xptd_time_in'];
            $plottedTimeOutRaw = empty($dtr['xptd_time_out']) ? null : $dtr['xptd_time_out'];
            $shiftCode = $dtr['shift_code'] ?: null;

            // Fallback: If plotted (expected) times are missing from the form, fetch from DB using shift_code
            if ((!$plottedTimeInRaw || !$plottedTimeOutRaw) && $shiftCode) {
                $scheduleData = DB::table('schedule') // Assuming 'schedule' table holds shift details
                    ->where('shift_code', $shiftCode)
                    ->select('xptd_time_in', 'xptd_time_out')
                    ->first();

                if ($scheduleData) {
                    $plottedTimeInRaw = $scheduleData->xptd_time_in;
                    $plottedTimeOutRaw = $scheduleData->xptd_time_out;
                } else {
                    Log::warning("No schedule found for shift_code: {$shiftCode} on {$date} for employee: {$employeeId}. Cannot calculate lates/undertime.");
                    // You might want to skip processing this record or mark it as needing review
                    continue; 
                }
            }

            $lateMinutes = 0;
            $undertimeMinutes = 0;
            $totalWorkMinutes = 0;
            $isLate = false;
            $isUndertime = false;

            // --- Carbon Parsing and Initializing Time Objects ---
            // Create Carbon objects for calculations. Crucially, include the date for correct time comparisons,
            // especially for shifts that span midnight.
            $actualTimeIn = $actualTimeInRaw ? Carbon::parse($date . ' ' . $actualTimeInRaw) : null;
            $actualTimeOut = $actualTimeOutRaw ? Carbon::parse($date . ' ' . $actualTimeOutRaw) : null;
            $plottedTimeIn = $plottedTimeInRaw ? Carbon::parse($date . ' ' . $plottedTimeInRaw) : null;
            $plottedTimeOut = $plottedTimeOutRaw ? Carbon::parse($date . ' ' . $plottedTimeOutRaw) : null;

            // --- Handle Overnight Shifts for Plotted Times ---
            // If plotted time out is earlier than plotted time in, it means the shift spans midnight.
            if ($plottedTimeIn && $plottedTimeOut && $plottedTimeOut->lt($plottedTimeIn)) {
                $plottedTimeOut->addDay();
            }

            // --- Handle Overnight Shifts for Actual Times ---
            // If actual time out is earlier than actual time in, it means the employee worked past midnight.
            if ($actualTimeIn && $actualTimeOut && $actualTimeOut->lt($actualTimeIn)) {
                $actualTimeOut->addDay();
            }
            
            // --- Lateness Calculation ---
            if ($actualTimeIn && $plottedTimeIn) {
                if ($actualTimeIn->greaterThan($plottedTimeIn)) {
                    $lateness = $actualTimeIn->diffInMinutes($plottedTimeIn);
                    if ($lateness > $gracePeriodMinutes) {
                        $isLate = true;
                        // Deduct grace period from total lateness
                        $lateMinutes = $lateness - $gracePeriodMinutes;
                    }
                }
            } else {
                Log::warning("Cannot calculate lateness for employee: {$employeeId} on {$date} due to missing actual or plotted time in.");
            }

            // --- Total Work Minutes Calculation ---
            // Calculate total time between actual clock-in and clock-out.
            // Further logic for deducting unpaid breaks would go here if needed.
            if ($actualTimeIn && $actualTimeOut) {
                $totalWorkMinutes = $actualTimeIn->diffInMinutes($actualTimeOut);
            } else {
                 Log::warning("Cannot calculate total work minutes for employee: {$employeeId} on {$date} due to missing actual time in/out.");
            }


            // --- Undertime Calculation ---
            // Undertime occurs if total actual work minutes are less than total plotted work minutes,
            // or if the employee clocks out significantly before scheduled end.
            if ($actualTimeIn && $actualTimeOut && $plottedTimeIn && $plottedTimeOut) {
                $plottedWorkMinutes = $plottedTimeIn->diffInMinutes($plottedTimeOut);
                
                // Scenario 1: Actual total work hours are less than scheduled total work hours
                if ($totalWorkMinutes < $plottedWorkMinutes) {
                    $undertimeMinutes = $plottedWorkMinutes - $totalWorkMinutes;
                    $isUndertime = true;
                }
                
                // Scenario 2 (Optional, for explicit early clock-out):
                // If actual time out is before plotted time out (and not just accounted for by totalWorkMinutes)
                // You might need to refine this based on your specific undertime rules.
                // For example, if totalWorkMinutes already accounts for early leaving, this might be redundant.
                // if ($actualTimeOut->lt($plottedTimeOut)) {
                //     $undertimeFromEarlyOut = $plottedTimeOut->diffInMinutes($actualTimeOut);
                //     $undertimeMinutes = max($undertimeMinutes, $undertimeFromEarlyOut); // Take the greater if multiple rules
                //     $isUndertime = true;
                // }

            } else {
                Log::warning("Cannot calculate undertime for employee: {$employeeId} on {$date} due to missing actual or plotted times.");
            }

            try {
                DTR::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'date' => $date, 
                    ],
                    [
                        'plotted_time_in'    => $plottedTimeInRaw,    
                        'plotted_time_out'   => $plottedTimeOutRaw,   
                        'actual_time_in'     => $actualTimeInRaw,     
                        'late_minutes'       => $lateMinutes,
                        'undertime_minutes'  => $undertimeMinutes,
                        'total_work_minutes' => $totalWorkMinutes, 
                        'is_late'            => $isLate,
                        'is_undertime'       => $isUndertime,
                        'updated_at'         => now(),
                        // 'transindate' and 'transoutdate' are likely 'date' in your processed DTR table
                        // If 'DTR' model is also used for raw import, you might need separate models/tables.
                        // Assuming 'date' in the processed DTR table covers both `transindate` and `transoutdate` for a single day's record.
                    ]
                );
            } catch (\Exception $e) {
                Log::error("Failed to update/create processed DTR for employee: {$employeeId} on {$date}. Error: " . $e->getMessage());
                // Consider adding to an errors array to return to the user if needed
            }
        }

        // Redirect back with a success message
        return redirect()->back()->with('success', 'DTR records processed and calculations updated successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Not implemented for this controller's primary function
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Not implemented for this controller's primary function
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Not implemented for this controller's primary function
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Not implemented for this controller's primary function
    }

    /**
     * Get processed DTR data (for DataTables).
     * This method fetches data from the table where calculations are stored.
     */
    public function getProcessedDTR(Request $request)
    {
        try {
            
            $query = DB::table('employee_dtr') 
                ->join('employees', 'employee_dtr.employee_id', '=', 'employees.employee_id') // Use employee_id for join
                ->select(
                    'employee_dtr.employee_id',
                    DB::raw("CONCAT(COALESCE(employees.first_name, ''), ' ', COALESCE(employees.last_name, '')) AS employee_name"),
                    'employee_dtr.date',
                    'employee_dtr.plotted_time_in',
                    'employee_dtr.plotted_time_out',
                    'employee_dtr.actual_time_in',
                    'employee_dtr.actual_time_out',
                    'employee_dtr.late_minutes',      
                    'employee_dtr.undertime_minutes',  
                    'employee_dtr.total_work_minutes'  
                    // Add other relevant processed fields
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
            Log::error("Error fetching processed DTR data: " . $e->getMessage() . " on line " . $e->getLine() . " in file " . $e->getFile());
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while fetching processed DTR data. Please check logs.',
            ], 500);
        }
    }
}

