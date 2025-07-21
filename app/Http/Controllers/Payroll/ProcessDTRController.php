<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

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
                // Ensure the join condition is robust against leading/trailing spaces
                $join->on(DB::raw('TRIM(employee_schedules.shift_code)'), '=', DB::raw('TRIM(schedule.shift_code)'));
            })
            ->leftJoin('attendance', function ($join) {
                $join->on('employee_schedules.employee_id', '=', 'attendance.employee_id')
                    ->whereRaw('DATE(attendance.transindate) = employee_schedules.date');
            })
            ->leftJoin('leave_types', 'employee_schedules.leave_type_id', '=', 'leave_types.id')
            ->whereBetween('employee_schedules.date', [$startDate, $endDate]) // Filter by date range
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

        if ($end->lt($start)) {
            $end->addDay(); // Ensure end is after start for accurate duration
        }

        $totalNightDiffMinutes = 0;

        // Iterate through days covered by the shift
        $current = $start->copy()->startOfDay();
        while ($current->lte($end->copy()->startOfDay())) {
            $nightStartOfDay = $current->copy()->setTime(22, 0, 0); // 10 PM on current day
            $nightEndOfDay = $current->copy()->addDay()->setTime(6, 0, 0); // 6 AM on next day

            // Determine overlap with the shift
            $overlapStart = $start->max($nightStartOfDay);
            $overlapEnd = $end->min($nightEndOfDay);

            if ($overlapStart->lt($overlapEnd)) {
                $totalNightDiffMinutes += $overlapEnd->diffInMinutes($overlapStart);
            }

            $current->addDay(); // Move to the next day
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
            'dtrs.*.xptd_time_in' => 'nullable|date_format:H:i:s',
            'dtrs.*.xptd_time_out' => 'nullable|date_format:H:i:s',
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

            // Fallback for expected times if not already passed from the form
            if ((!$expectedIn || !$expectedOut) && $shiftCodeFromForm) {
                $scheduleData = DB::table('schedule')
                    ->whereRaw('TRIM(shift_code) = ?', [trim($shiftCodeFromForm)])
                    ->select('xptd_time_in', 'xptd_time_out')
                    ->first();

                if ($scheduleData) {
                    $expectedIn = $expectedIn ?: $scheduleData->xptd_time_in;
                    $expectedOut = $expectedOut ?: $scheduleData->xptd_time_out;
                }
            }

            // --- Initialize Carbon objects ---
            $actualTimeInCarbon = $actualTimeIn ? Carbon::parse($baseDate->toDateString() . ' ' . $actualTimeIn) : null;
            $actualTimeOutCarbon = $actualTimeOut ? Carbon::parse($baseDate->toDateString() . ' ' . $actualTimeOut) : null;

            if ($actualTimeInCarbon && $actualTimeOutCarbon && $actualTimeOutCarbon->lt($actualTimeInCarbon)) {
                $actualTimeOutCarbon->addDay();
            }

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
            $totalHours = 0; // Initialize total_hours to 0
            $totalMinutesWorked = 0;

            $nightDiffRegMinutes = 0;
            $nightDiffSpecMinutes = 0;
            $nightDiffMinutes = 0;

            $accumulatedRegHolidayHours = 0.0;
            $accumulatedSpecHolidayHours = 0.0;

            // --- Determine the shift_code to store in the DTR table for THIS DTR entry ---
            // Start by assuming it's the schedule's shift code
            $shiftCodeToStore = $shiftCodeFromForm;

            Log::info("Processing DTR for Employee ID: {$employeeId} on Date: {$baseDate->toDateString()}");
            Log::info("Shift Code (from form): {$shiftCodeFromForm}, Expected In: {$expectedIn}, Expected Out: {$expectedOut}, Leave Type ID: {$leaveTypeId}");


            if ($leaveTypeId) {
                $leaveType = LeaveType::find($leaveTypeId);
                if ($leaveType) {
                    // If on leave, set the shiftCodeToStore to the leave type name
                    $shiftCodeToStore = $leaveType->name;

                    // Use the getLeaveDeductionHours function to determine total_hours for this leave type
                    $totalHours = $this->getLeaveDeductionHours($leaveType->name);
                }

                // Zero out other time-related calculations for leave days
                $lateMinutes = 0;
                $undertimeMinutes = 0;
                $nightDiffMinutes = 0;
                $nightDiffRegMinutes = 0;
                $nightDiffSpecMinutes = 0;
                $accumulatedRegHolidayHours = 0.0;
                $accumulatedSpecHolidayHours = 0.0;
                // Also set actual time in/out to null for leave days if they were somehow populated
                $actualTimeIn = null;
                $actualTimeOut = null;


                Log::info("Employee is on leave (ID: {$leaveTypeId}, Name: " . ($shiftCodeToStore ?? 'N/A') . "). Setting total_hours to leave type's deduction hours: {$totalHours}.");

            } else {
                // Normal processing for non-leave days
                // Calculate Lateness with Grace Period
                if ($actualTimeInCarbon && $expectedTimeInCarbon) {
                    $expectedTimeInWithGrace = $expectedTimeInCarbon->copy()->addMinutes($lateGracePeriodMinutes);

                    if ($actualTimeInCarbon->gt($expectedTimeInWithGrace)) {
                        $isLate = true;
                        $lateMinutes = $actualTimeInCarbon->diffInMinutes($expectedTimeInCarbon);
                    } else {
                        $isLate = false;
                        $lateMinutes = 0;
                    }
                }

                // Calculate Undertime
                if ($actualTimeOutCarbon && $expectedTimeOutCarbon) {
                    try {
                        if ($actualTimeOutCarbon->lt($expectedTimeOutCarbon)) {
                            $isUndertime = true;
                            $undertimeMinutes = $expectedTimeOutCarbon->diffInMinutes($actualTimeOutCarbon);
                        } else {
                            $isUndertime = false;
                            $undertimeMinutes = 0;
                        }
                    } catch (\Exception $e) {
                        report($e);
                        $undertimeMinutes = 0;
                        $isUndertime = false;
                        Log::error("Error during undertime calculation: " . $e->getMessage());
                    }
                }

                // --- Main Time Calculation Loop: Regular Hours, Holiday Hours, and Night Differential ---
                if ($actualTimeInCarbon && $actualTimeOutCarbon) {
                    $currentMinute = $actualTimeInCarbon->copy();
                    $totalMinutesWorked = 0;
                    $hourFraction = 1 / 60.0;

                    $ndWindowStartHour = 22; // 10 PM
                    $ndWindowEndHour = 6;    // 6 AM

                    while ($currentMinute->lt($actualTimeOutCarbon)) {
                        $minuteDate = $currentMinute->toDateString();
                        $holidayForMinute = Holiday::where('date', $minuteDate)->first();
                        $isMinuteOnRegularHoliday = ($holidayForMinute && $holidayForMinute->type === 'REGULAR HOLIDAY');
                        $isMinuteOnSpecialHoliday = ($holidayForMinute && $holidayForMinute->type === 'SPECIAL NON-WORKING HOLIDAY');

                        $currentHour = $currentMinute->hour;
                        $isWithinNDWindow = ($currentHour >= $ndWindowStartHour || $currentHour < $ndWindowEndHour);

                        if ($isMinuteOnRegularHoliday) {
                            $accumulatedRegHolidayHours += $hourFraction;
                        } elseif ($isMinuteOnSpecialHoliday) {
                            $accumulatedSpecHolidayHours += $hourFraction;
                        }

                        if ($isWithinNDWindow) {
                            if ($isMinuteOnRegularHoliday) {
                                $nightDiffRegMinutes++;
                            } elseif ($isMinuteOnSpecialHoliday) {
                                $nightDiffSpecMinutes++;
                            } else {
                                $nightDiffMinutes++;
                            }
                        }

                        $totalMinutesWorked++;
                        $currentMinute->addMinute();
                    }

                    $totalHours = round($totalMinutesWorked / 60, 2);

                    Log::info("Calculated totalMinutesWorked: {$totalMinutesWorked}");
                    Log::info("Calculated totalHours (total actual duration): {$totalHours}");

                } else {
                    Log::info("Main time calculation loop: Not enough data (missing actual times).");
                }
            } // End of else (not on leave) condition

            // --- Ensure all magnitude-based values are non-negative before saving ---
            $lateMinutes = abs($lateMinutes);
            $undertimeMinutes = abs($undertimeMinutes);

            // Logging final values for debugging
            Log::info("Final values for updateOrCreate for {$employeeId} on {$baseDate->toDateString()}:");
            Log::info(" Total Hours: " . $totalHours);
            Log::info(" Reg Holiday Hours: " . round($accumulatedRegHolidayHours, 2));
            Log::info(" Spec Holiday Hours: " . round(abs($accumulatedSpecHolidayHours), 2));
            Log::info(" Night Diff (Normal, minutes): " . $nightDiffMinutes);
            Log::info(" Night Diff Reg (minutes): " . $nightDiffRegMinutes);
            Log::info(" Night Diff Spec (minutes): " . $nightDiffSpecMinutes);
            Log::info("   Leave Type ID (to save): " . ($leaveTypeId ?? 'NULL'));
            Log::info("   Shift Code to Store: " . ($shiftCodeToStore ?? 'NULL')); // Now this should be correct

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
                    'total_hours' => $totalHours, // This will now be the leave type's default hours if on leave
                    'overtime_minutes' => 0,
                    'night_diff' => round(abs($nightDiffMinutes) / 60, 2),
                    'night_diff_reg' => round(abs($nightDiffRegMinutes) / 60, 2),
                    'night_diff_spec' => round(abs($nightDiffSpecMinutes) / 60, 2),
                    'reg_holiday_hours' => round(abs($accumulatedRegHolidayHours), 2),
                    'spec_holiday_hours' => round(abs($accumulatedSpecHolidayHours), 2),
                    'reg_holiday_ot_minutes' => 0,
                    'spec_holiday_ot_minutes' => 0,
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