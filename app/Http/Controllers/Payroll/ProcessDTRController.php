<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Payroll;
use App\Models\DTR;
use App\Models\Employee;
use App\Models\Holiday;
use Carbon\Carbon;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Log;

class ProcessDTRController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 1. Fetch the latest payroll data to get the current cutoff dates.
        $latestPayroll = Payroll::orderBy('created_at', 'desc')->first();

        // 2. Set the start and end dates. Use the payroll dates if they exist,
        // otherwise, default to the current month.
        if ($latestPayroll) {
            $startDate = $latestPayroll->from_date;
            $endDate = $latestPayroll->to_date;
        } else {
            // Default to the current month if no payroll data is found.
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
        }

        // 3. Log the dates being used for debugging.
        Log::info("Displaying DTR data for the payroll cutoff: From {$startDate} to {$endDate}");

        $data = DB::table('employee_schedules')
            ->select(
                'employee_schedules.employee_id',
                'employee_schedules.shift_code',
                DB::raw("CONCAT(COALESCE(employees.first_name, ''), ' ', COALESCE(employees.last_name, '')) AS employee_name"),
                'employee_schedules.date',
                'schedule.xptd_time_in AS plotted_time_in',
                'schedule.xptd_time_out AS plotted_time_out',
                'attendance.time_in AS actual_time_in',
                'attendance.time_out AS actual_time_out',
                'leave_types.name AS leave_type_name',
                'employee_schedules.leave_type_id'
            )
            ->leftJoin('employees', 'employee_schedules.employee_id', '=', 'employees.employee_id')
            ->leftJoin('schedule', function ($join) {
                $join->on(DB::raw('TRIM(employee_schedules.shift_code)'), '=', DB::raw('TRIM(schedule.shift_code)'));
            })
            ->leftJoin('attendance', function ($join) {
                $join->on('employee_schedules.employee_id', '=', 'attendance.employee_id')
                    ->whereRaw('DATE(attendance.transindate) = employee_schedules.date');
            })
            ->leftJoin('leave_types', 'employee_schedules.leave_type_id', '=', 'leave_types.id')

            // 4. Use the new $startDate and $endDate variables for the date range filter.
            ->whereBetween('employee_schedules.date', [$startDate, $endDate])

            ->orderByDesc('employee_schedules.date')
            ->get();

        return view('HR.attendance.processdtr', ['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Helper function to calculate night differential minutes for a given time range.
     * This function now calculates the total night differential minutes *within that range*,
     * without considering holidays. The holiday classification will happen in the store method.
     * @param Carbon|null $startTime Carbon instance of the start time (with date context)
     * @param Carbon|null $endTime Carbon instance of the end time (with date context)
     * @return int Night differential minutes (always non-negative)
     */
    private function calculateNightDifferentialMinutes(?Carbon $startTime, ?Carbon $endTime): int
    {
        if (!$startTime || !$endTime) {
            return 0;
        }

        $start = $startTime->copy();
        $end = $endTime->copy();

        // Ensure end is after start for accurate duration, especially for shifts crossing midnight
        if ($end->lt($start)) {
            $end->addDay();
        }

        $totalNightDiffMinutes = 0;

        
        $ndWindowStartHour = 22; // 10 PM
        $ndWindowEndHour = 6;    // 6 AM

        // Iterate minute by minute within the effective period
        $current = $start->copy();
        while ($current->lt($end)) {
            $currentHour = $current->hour;

            // Check if the current minute falls within the 10 PM to 6 AM window
            if ($currentHour >= $ndWindowStartHour || $currentHour < $ndWindowEndHour) {
                $totalNightDiffMinutes++;
            }
            $current->addMinute();
        }

        return abs($totalNightDiffMinutes);
    }

    /**
     * Helper function to get the "total_hours" value for leave types.
     * This uses a hardcoded mapping.
     *
     * @param string $leaveTypeName The name of the leave type.
     * @return float The hours to credit/deduct for the leave, or 0.0 if not found.
     */
    private function getLeaveDeductionHours(string $leaveTypeName): float
    {
        $leaveHoursMapping = [
            'Absent Leave' => 0.0,
            'Vacation Leave Without Pay' => 0.0,
            'Sick Leave Without Pay' => 0.0,
            'Maternity Leave' => 0.0,
            'Suspension' => 0.0,

            'Vacation Leave' => 8.0,
            'Sick Leave' => 8.0,
            'Paternity Leave' => 8.0,
            'Bereavement Leave' => 8.0,
            'Birthday Leave' => 8.0,
            'Emergency Leave' => 8.0,
            'Solo Parent Leave' => 8.0,

            'Vacation Leave AM' => 4.0,
            'Vacation Leave PM' => 4.0,
            'Sick Leave AM' => 4.0,
            'Sick Leave PM' => 4.0,
            'Emergency Leave AM' => 4.0,
            'Emergency Leave PM' => 4.0,
        ];

        if (!array_key_exists($leaveTypeName, $leaveHoursMapping)) {
            Log::warning("Undefined leave type '{$leaveTypeName}' encountered. Defaulting to 0 hours for DTR.");
        }
        return $leaveHoursMapping[$leaveTypeName] ?? 0.0;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dtrs.*.employee_id' => 'required|exists:employees,employee_id',
            'dtrs.*.date' => 'required|date',
            'dtrs.*.time_in' => 'nullable|date_format:H:i:s',
            'dtrs.*.time_out' => 'nullable|date_format:H:i:s',
            'dtrs.*.shift_code' => 'nullable|string|max:20',
            'dtrs.*.xptd_time_in' => 'nullable|date_format:H:i:s', // These are from employee_schedules table
            'dtrs.*.xptd_time_out' => 'nullable|date_format:H:i:s',// These are from employee_schedules table
            'dtrs.*.leave_type_id' => 'nullable|integer|exists:leave_types,id',
        ]);

        $dtrs = $request->input('dtrs', []);

        // Define the grace period in minutes
        $lateGracePeriodMinutes = 1;

        foreach ($dtrs as $dtr) {
            $employeeId = $dtr['employee_id'];
            $baseDate = Carbon::parse($dtr['date']);

            $shiftCodeFromForm = $dtr['shift_code'] ?: null; // Original shift_code from employee_schedules
            $leaveTypeId = $dtr['leave_type_id'] ?? null;

            $actualTimeIn = $dtr['time_in'] ?: null;
            $actualTimeOut = $dtr['time_out'] ?: null;

            $expectedIn = $dtr['xptd_time_in'] ?: null;
            $expectedOut = $dtr['xptd_time_out'] ?: null;

            $expectedWorkHours = 0;
            $expectedBreakIn = null;
            $expectedBreakOut = null;

            // --- Always fetch schedule data if a shift code is available ---
            if ($shiftCodeFromForm) {
                Log::info("DEBUG: Attempting to fetch schedule for shift_code: " . trim($shiftCodeFromForm));
                $scheduleData = DB::table('schedule')
                    ->whereRaw('TRIM(shift_code) = ?', [trim($shiftCodeFromForm)])
                    ->select('xptd_time_in', 'xptd_time_out', 'wrkhrs', 'xptd_brk_in', 'xptd_brk_out') // Make sure to select break times too
                    ->first();

                if ($scheduleData) {
                    // Only override expectedIn/Out if they were not already passed from the form (i.e., they were null)
                    $expectedIn = $expectedIn ?: $scheduleData->xptd_time_in;
                    $expectedOut = $expectedOut ?: $scheduleData->xptd_time_out;

                    $expectedWorkHours = $scheduleData->wrkhrs;
                    $expectedBreakIn = $scheduleData->xptd_brk_in;
                    $expectedBreakOut = $scheduleData->xptd_brk_out;

                    Log::info("DEBUG: Schedule data found. xptd_time_in: {$expectedIn}, xptd_time_out: {$expectedOut}, wrkhrs: {$expectedWorkHours}, break_in: {$expectedBreakIn}, break_out: {$expectedBreakOut}");
                } else {
                    Log::warning("DEBUG: Schedule data NOT found for shift_code: " . trim($shiftCodeFromForm) . ". Defaulting wrkhrs to 0 and proceeding without specific schedule times.");
                }
            } else {
                Log::info("DEBUG: No shift code provided. Proceeding without specific schedule times or wrkhrs.");
            }

            // --- Initialize Carbon objects for Actual Times ---
            $actualTimeInCarbon = $actualTimeIn ? Carbon::parse($baseDate->toDateString() . ' ' . $actualTimeIn) : null;
            $actualTimeOutCarbon = $actualTimeOut ? Carbon::parse($baseDate->toDateString() . ' ' . $actualTimeOut) : null;

            if ($actualTimeInCarbon && $actualTimeOutCarbon && $actualTimeOutCarbon->lt($actualTimeInCarbon)) {
                $actualTimeOutCarbon->addDay();
            }

            // --- Initialize Carbon objects for Expected Times (using potentially updated values from schedule) ---
            $expectedTimeInCarbon = $expectedIn ? Carbon::parse($baseDate->toDateString() . ' ' . $expectedIn) : null;
            $expectedTimeOutCarbon = $expectedOut ? Carbon::parse($baseDate->toDateString() . ' ' . $expectedOut) : null;

            if ($expectedTimeInCarbon && $expectedTimeOutCarbon && $expectedTimeOutCarbon->lt($expectedTimeInCarbon)) {
                $expectedTimeOutCarbon->addDay();
            }
            // --- End Initialization ---

            $isLate = false;
            $lateMinutes = 0;
            $isUndertime = false;
            $undertimeMinutes = 0;
            $totalHours = 0;
            $totalMinutesWorked = 0;

            $nightDiffRegMinutes = 0;
            $nightDiffSpecMinutes = 0;
            $nightDiffNormalMinutes = 0; // Renamed to clarify it's non-holiday ND

            $accumulatedRegHolidayHours = 0.0;
            $accumulatedSpecHolidayHours = 0.0;

            // --- Determine the shift_code to store in the DTR table for THIS DTR entry ---
            $shiftCodeToStore = $shiftCodeFromForm;

            Log::info("Processing DTR for Employee ID: {$employeeId} on Date: {$baseDate->toDateString()}");
            Log::info("Shift Code (from form): {$shiftCodeFromForm}, Expected In: {$expectedIn}, Expected Out: {$expectedOut}, Leave Type ID: {$leaveTypeId}");


            if ($leaveTypeId) {
                $leaveType = LeaveType::find($leaveTypeId);
                if ($leaveType) {
                    $shiftCodeToStore = $leaveType->name;
                    $totalHours = $this->getLeaveDeductionHours($leaveType->name);
                }

                // Zero out other time-related calculations for leave days
                $lateMinutes = 0;
                $undertimeMinutes = 0;
                $nightDiffNormalMinutes = 0;
                $nightDiffRegMinutes = 0;
                $nightDiffSpecMinutes = 0;
                $accumulatedRegHolidayHours = 0.0;
                $accumulatedSpecHolidayHours = 0.0;
                // Keep actual time in/out values to show they were present for a partial day
                // $actualTimeIn = null; 
                // $actualTimeOut = null; 
                // Removed the lines that set time_in/out to null. This is the key change.


                Log::info("Employee is on leave (ID: {$leaveTypeId}, Name: " . ($shiftCodeToStore ?? 'N/A') . "). Setting total_hours to leave type's deduction hours: {$totalHours}.");

            }
            // The previous 'else' block is now removed, allowing the time-based calculations to run regardless of a leave type.

            $hourFraction = 1 / 60.0;

            // Calculate Lateness with Grace Period
            if (trim(strtoupper($shiftCodeFromForm)) === 'RDR') {
                $isLate = false;
                $lateMinutes = 0;
                $isUndertime = false;
                $undertimeMinutes = 0;
                Log::info("Exempting Employee ID {$employeeId} from late/undertime due to RDR shift code.");
            } else {
                if ($actualTimeInCarbon && $expectedTimeInCarbon) {
                    $expectedTimeInWithGrace = $expectedTimeInCarbon->copy()->addMinutes($lateGracePeriodMinutes);

                    if ($actualTimeInCarbon->gt($expectedTimeInWithGrace)) {
                        $isLate = true;
                        // Late minutes are calculated from actual time in against expected time in
                        $lateMinutes = $actualTimeInCarbon->diffInMinutes($expectedTimeInCarbon);
                    } else {
                        $isLate = false;
                        $lateMinutes = 0;
                    }
                    $lateMinutes = abs($lateMinutes); // Ensure non-negative
                }

                // Calculate Undertime
                if ($actualTimeOutCarbon && $expectedTimeOutCarbon) {
                    try {
                        if ($actualTimeOutCarbon->lt($expectedTimeOutCarbon)) {
                            $isUndertime = true;
                            // Undertime minutes are calculated from expected time out against actual time out
                            $undertimeMinutes = $expectedTimeOutCarbon->diffInMinutes($actualTimeOutCarbon);
                        } else {
                            $isUndertime = false;
                            $undertimeMinutes = 0;
                        }
                        $undertimeMinutes = abs($undertimeMinutes); // Ensure non-negative
                    } catch (\Exception $e) {
                        report($e);
                        $undertimeMinutes = 0;
                        $isUndertime = false;
                        Log::error("Error during undertime calculation: " . $e->getMessage());
                    }
                }
            }

            // --- Calculate total_hours based on effective time (actual bounded by expected) and deducting breaks ---
            $effectiveTimeIn = null;
            $effectiveTimeOut = null;
            $breakMinutes = 0;

            // Determine the effective time in: Later of actual or expected
            if ($actualTimeInCarbon && $expectedTimeInCarbon) {
                $effectiveTimeIn = $actualTimeInCarbon->max($expectedTimeInCarbon);
            } else {
                $effectiveTimeIn = $actualTimeInCarbon ?? $expectedTimeInCarbon;
            }

            // Determine the effective time out: Earlier of actual or expected
            if ($actualTimeOutCarbon && $expectedTimeOutCarbon) {
                $effectiveTimeOut = $actualTimeOutCarbon->min($expectedTimeOutCarbon);
            } else {
                $effectiveTimeOut = $actualTimeOutCarbon ?? $expectedTimeOutCarbon;
            }


            if ($effectiveTimeIn && $effectiveTimeOut && $effectiveTimeOut->gt($effectiveTimeIn)) {
                $totalMinutesWorked = $effectiveTimeOut->diffInMinutes($effectiveTimeIn);
                $totalMinutesWorked = abs($totalMinutesWorked); // Ensure positive
            } else {
                $totalMinutesWorked = 0;
            }

            Log::info("DEBUG: totalMinutesWorked (initial gross, after effective times): {$totalMinutesWorked}");

            // --- Deduct break minutes if applicable and within the effective range ---
            if ($expectedBreakIn && $expectedBreakOut) {
                $breakInCarbon = Carbon::parse($baseDate->toDateString() . ' ' . $expectedBreakIn);
                $breakOutCarbon = Carbon::parse($baseDate->toDateString() . ' ' . $expectedBreakOut);

                // Adjust breakOutCarbon if break spans midnight (e.g., 23:00 to 00:00 next day)
                if ($breakOutCarbon->lt($breakInCarbon)) {
                    $breakOutCarbon->addDay();
                }

                // Calculate overlap of the expected break with the actual effective worked duration
                $overlapStart = $effectiveTimeIn->max($breakInCarbon);
                $overlapEnd = $effectiveTimeOut->min($breakOutCarbon);

                if ($overlapStart->lt($overlapEnd)) {
                    $breakMinutes = abs($overlapEnd->diffInMinutes($overlapStart));
                }
                Log::info("DEBUG: Calculated breakMinutes to deduct: {$breakMinutes}");
            }

            $totalMinutesWorked = max(0, $totalMinutesWorked - $breakMinutes); // Deduct break, ensure non-negative

            Log::info("DEBUG: totalMinutesWorked (after break deduction and max(0,...)): {$totalMinutesWorked}");

            // Add the worked hours to the total. If it's a full leave day, worked hours will be 0.
            $totalHours = ($leaveTypeId ? $totalHours : 0) + round($totalMinutesWorked / 60, 2);

            Log::info("Calculated totalHours (after final processing and made positive): {$totalHours}");


            $totalNightDiffEffectiveMinutes = $this->calculateNightDifferentialMinutes($effectiveTimeIn, $effectiveTimeOut);
            Log::info("DEBUG: totalNightDiffEffectiveMinutes (from helper): {$totalNightDiffEffectiveMinutes}");


            $accumulatedRegHolidayHours = 0.0;
            $accumulatedSpecHolidayHours = 0.0;
            $nightDiffRegMinutes = 0;
            $nightDiffSpecMinutes = 0;
            $nightDiffNormalMinutes = 0;

            // Recalculate holiday hours and holiday night differential minutes based on effective time in/out
            if ($effectiveTimeIn && $effectiveTimeOut) { // Use effective times for holiday distribution of ND
                $currentMinute = $effectiveTimeIn->copy();
                $ndWindowStartHour = 22; // 10 PM
                $ndWindowEndHour = 6;    // 6 AM

                while ($currentMinute->lt($effectiveTimeOut)) {
                    $minuteDate = $currentMinute->toDateString();
                    $holidayForMinute = Holiday::where('date', $minuteDate)->first();
                    $isMinuteOnRegularHoliday = ($holidayForMinute && $holidayForMinute->type === 'REGULAR HOLIDAY');
                    $isMinuteOnSpecialHoliday = ($holidayForMinute && $holidayForMinute->type === 'SPECIAL NON-WORKING HOLIDAY');

                    $currentHour = $currentMinute->hour;
                    $isWithinNDWindow = ($currentHour >= $ndWindowStartHour || $currentHour < $ndWindowEndHour);

                    if ($isMinuteOnRegularHoliday) {
                        $accumulatedRegHolidayHours += $hourFraction;
                        if ($isWithinNDWindow) {
                            $nightDiffRegMinutes++;
                        }
                    } elseif ($isMinuteOnSpecialHoliday) {
                        $accumulatedSpecHolidayHours += $hourFraction;
                        if ($isWithinNDWindow) {
                            $nightDiffSpecMinutes++;
                        }
                    } else {
                        // If not a holiday, and within ND window, it's normal night differential
                        if ($isWithinNDWindow) {
                            $nightDiffNormalMinutes++;
                        }
                    }
                    $currentMinute->addMinute();
                }
            }

            // --- Ensure all magnitude-based values are non-negative before saving ---
            $lateMinutes = abs($lateMinutes);
            $undertimeMinutes = abs($undertimeMinutes);
            // Night diffs are already non-negative from calculation logic
            $accumulatedRegHolidayHours = abs($accumulatedRegHolidayHours);
            $accumulatedSpecHolidayHours = abs($accumulatedSpecHolidayHours);


            // Logging final values for debugging
            Log::info("Final values for updateOrCreate for {$employeeId} on {$baseDate->toDateString()}:");
            Log::info(" Total Hours: " . $totalHours);
            Log::info(" Reg Holiday Hours: " . round($accumulatedRegHolidayHours, 2));
            Log::info(" Spec Holiday Hours: " . round($accumulatedSpecHolidayHours, 2));
            Log::info(" Night Diff (Normal, minutes): " . $nightDiffNormalMinutes); // Use the new variable
            Log::info(" Night Diff Reg (minutes): " . $nightDiffRegMinutes);
            Log::info(" Night Diff Spec (minutes): " . $nightDiffSpecMinutes);
            Log::info(" Leave Type ID (to save): " . ($leaveTypeId ?? 'NULL'));
            Log::info(" Shift Code to Store: " . ($shiftCodeToStore ?? 'NULL')); // Now this should be correct
            Log::info("--- Processing DTR for Employee ID: {$employeeId} on Date: {$baseDate->toDateString()} ---");
            
            Log::info("Actual Time In: " . ($actualTimeInCarbon ? $actualTimeInCarbon->format('Y-m-d H:i:s') : 'N/A'));
            Log::info("Actual Time Out: " . ($actualTimeOutCarbon ? $actualTimeOutCarbon->format('Y-m-d H:i:s') : 'N/A'));
            Log::info("Expected Time In: " . ($expectedTimeInCarbon ? $expectedTimeInCarbon->format('Y-m-d H:i:s') : 'N/A'));
            Log::info("Expected Time Out: " . ($expectedTimeOutCarbon ? $expectedTimeOutCarbon->format('Y-m-d H:i:s') : 'N/A'));
            Log::info("Expected Break In: " . ($expectedBreakIn ?? 'N/A'));
            Log::info("Expected Break Out: " . ($expectedBreakOut ?? 'N/A'));
            Log::info("Expected Work Hours (wrkhrs): " . $expectedWorkHours);

            Log::info("Effective Time In: " . ($effectiveTimeIn ? $effectiveTimeIn->format('Y-m-d H:i:s') : 'N/A'));
            Log::info("Effective Time Out: " . ($effectiveTimeOut ? $effectiveTimeOut->format('Y-m-d H:i:s') : 'N/A'));
            Log::info("Initial Gross totalMinutesWorked: {$totalMinutesWorked}");
            Log::info("Calculated Break Minutes: {$breakMinutes}");
            Log::info("totalMinutesWorked after break deduction: " . max(0, $totalMinutesWorked - $breakMinutes));
            Log::info("FINAL total_hours to be saved: {$totalHours}");

            DTR::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'transindate' => $baseDate->toDateString(),
                ],
                [
                    'time_in' => $actualTimeIn, // Will be null if on leave
                    'time_out' => $actualTimeOut, // Will be null if on leave
                    'transoutdate' => $actualTimeOutCarbon ? $actualTimeOutCarbon->toDateString() : null,
                    'shift_code' => $shiftCodeToStore, // This is now correctly set per DTR entry
                    'xptd_time_in' => $expectedIn,
                    'xptd_time_out' => $expectedOut,
                    'is_late' => $isLate,
                    'late_minutes' => $lateMinutes,
                    'is_undertime' => $isUndertime,
                    'undertime_minutes' => $undertimeMinutes,
                    'total_hours' => $totalHours, // This will now reflect the new logic
                    'overtime_minutes' => 0, // Overtime needs separate calculation
                    'night_diff' => round($nightDiffNormalMinutes / 60, 2),
                    'night_diff_reg' => round($nightDiffRegMinutes / 60, 2),
                    'night_diff_spec' => round($nightDiffSpecMinutes / 60, 2),
                    'reg_holiday_hours' => round($accumulatedRegHolidayHours, 2),
                    'spec_holiday_hours' => round($accumulatedSpecHolidayHours, 2),
                    'reg_holiday_ot_minutes' => 0, // Needs separate calculation
                    'spec_holiday_ot_minutes' => 0, // Needs separate calculation
                    'leave_type_id' => $leaveTypeId,
                    'updated_at' => now(),
                ]
            );
        }

        return redirect()->back()->with('success', 'DTR records processed successfully!');
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
    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required',
            'date' => 'required|date',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
        ]);

        DB::table('attendance')
            ->where('employee_id', $id)
            ->whereDate('transindate', $request->date)
            ->update([
                'time_in' => $request->time_in ? Carbon::parse($request->date.' '.$request->time_in) : null,
                'time_out' => $request->time_out ? Carbon::parse($request->date.' '.$request->time_out) : null,
                'updated_at' => now(),
            ]);

        return redirect()->route('payroll.process-dtr.index')
                         ->with('success', 'Actual Time updated successfully!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}