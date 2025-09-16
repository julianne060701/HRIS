<?php

namespace App\Http\Controllers\Overtime;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Overtime;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; 
use App\Models\EmployeeSchedule;

class ManageOvertimeController extends Controller
{
    /**
     * Display a listing of all overtime records.
     */
    public function data()
    {
        $overtimes = Overtime::all();

        return response()->json(['data' => $overtimes]);
    }

    /**
     * Approve overtime.
     * When approved, re-calculate all detailed overtime components based on recorded ot_in/ot_out.
     */
   public function approve(Request $request, $id)
    {
        $ot = Overtime::findOrFail($id);

        DB::beginTransaction();
        try {
            $otDate   = Carbon::parse($ot->ot_date);
            $otInTime = Carbon::parse($otDate->format('Y-m-d') . ' ' . $ot->ot_in);
            $otOutTime = Carbon::parse($otDate->format('Y-m-d') . ' ' . $ot->ot_out);

            if ($otOutTime->lt($otInTime)) {
                $otOutTime->addDay();
            }

            $calculatedTotalOtHours = round($otInTime->diffInMinutes($otOutTime) / 60, 2);

            $overtimeDetails = $this->calculateOvertimeDetails($ot->employee_id, $otInTime, $otOutTime);

            $ot->update([
                'is_approved'          => true,
                'approved_hours'       => $calculatedTotalOtHours,
                'total_ot_hours'       => $calculatedTotalOtHours,
                'ot_reg_holiday_hours' => $overtimeDetails['ot_reg_holiday_hours'],
                'ot_spec_holiday_hours'=> $overtimeDetails['ot_spec_holiday_hours'],
                'ot_reg_ho_rdr'        => $overtimeDetails['ot_reg_ho_rdr'],
                'ot_spec_ho_rdr'       => $overtimeDetails['ot_spec_ho_rdr'],
                'ot_night_diff_rdr'    => $overtimeDetails['ot_night_diff_rdr'],
            ]);

            DB::commit();
            return response()->json(['message' => 'Overtime approved successfully.', 'data' => $ot]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Overtime approve error (ID: {$id}): " . $e->getMessage());
            return response()->json(['message' => 'Approval failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Disapprove overtime.
     */
    public function disapprove(Request $request, $id)
    {
        $ot = Overtime::findOrFail($id);
        $ot->is_approved = false;
        $ot->approved_hours = 0; // When disapproved, approved hours should be 0
        $ot->save();

        return response()->json(['message' => 'Overtime disapproved.']);
    }

    /**
     * Update overtime entry and re-calculate night differential and holiday OT.
     */
   public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'ot_date' => 'required|date',
            'ot_in'   => 'required|date_format:H:i:s',
            'ot_out'  => 'required|date_format:H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ot = Overtime::findOrFail($id);

        $otDate   = Carbon::parse($request->ot_date);
        $otInTime = Carbon::parse($otDate->format('Y-m-d') . ' ' . $request->ot_in);
        $otOutTime = Carbon::parse($otDate->format('Y-m-d') . ' ' . $request->ot_out);

        if ($otOutTime->lt($otInTime)) {
            $otOutTime->addDay();
        }

        $calculatedTotalOtHours = round($otInTime->diffInMinutes($otOutTime) / 60, 2);
        $overtimeDetails = $this->calculateOvertimeDetails($ot->employee_id, $otInTime, $otOutTime);

        DB::beginTransaction();
        try {
            $ot->update([
                'ot_date'             => $otDate->toDateString(),
                'ot_in'               => $request->ot_in,
                'ot_out'              => $request->ot_out,
                'total_ot_hours'      => $calculatedTotalOtHours,
                'approved_hours'      => $request->approved_hours ?? $ot->approved_hours,
                'ot_reg_holiday_hours'=> $overtimeDetails['ot_reg_holiday_hours'],
                'ot_spec_holiday_hours'=> $overtimeDetails['ot_spec_holiday_hours'],
                'ot_reg_ho_rdr'       => $overtimeDetails['ot_reg_ho_rdr'],
                'ot_spec_ho_rdr'      => $overtimeDetails['ot_spec_ho_rdr'],
                'ot_rest_day'            => $overtimeDetails['ot_rest_day'],
                'ot_night_diff_rdr'   => $overtimeDetails['ot_night_diff_rdr'],
            ]);

            DB::commit();
            return response()->json(['message' => 'Overtime updated successfully.', 'data' => $ot]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Overtime update error (ID: {$id}): " . $e->getMessage());
            return response()->json(['message' => 'Update failed', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Helper function to calculate night differential and holiday overtime minutes
     * for a given overtime period.
     *
     * @param Carbon $otInCarbon Carbon instance of the overtime start time (with date context)
     * @param Carbon $otOutCarbon Carbon instance of the overtime end time (with date context)
     * @return array An associative array containing:
     * - 'ot_night_diff_hours' (float)
     * - 'ot_reg_holiday_hours' (float)
     * - 'ot_spec_holiday_hours' (float)
     * - 'ot_reg_holiday_nd_hours' (float)
     * - 'ot_spec_holiday_nd_hours' (float)
     */
     private function calculateOvertimeDetails($employeeId, Carbon $otInCarbon, Carbon $otOutCarbon): array
    {
        $regHolidayOtMinutes = 0;
        $specHolidayOtMinutes = 0;
        $regHolidayRdrMinutes = 0;
        $specHolidayRdrMinutes = 0;
        $nightDiffRdrMinutes = 0;

        $isRestDay = $this->isRestDay($employeeId, $otInCarbon->toDateString());
        $currentMinute = $otInCarbon->copy();

        while ($currentMinute->lt($otOutCarbon)) {
            $minuteDate = $currentMinute->format('Y-m-d');
            $holiday = Holiday::where('date', $minuteDate)->first();

            $isRegularHoliday = ($holiday && $holiday->type === 'REGULAR HOLIDAY');
            $isSpecialHoliday = ($holiday && $holiday->type === 'SPECIAL NON-WORKING HOLIDAY');

            // Count holiday overtime
            if ($isRegularHoliday) {
                $regHolidayOtMinutes++;
                if ($isRestDay) $regHolidayRdrMinutes++;
            }
            if ($isSpecialHoliday) {
                $specHolidayOtMinutes++;
                if ($isRestDay) $specHolidayRdrMinutes++;
            }

            // Night differential (10PM - 6AM)
            if ($isRestDay && ($currentMinute->hour >= 22 || $currentMinute->hour < 6)) {
                $nightDiffRdrMinutes++;
            }

            $currentMinute->addMinute();
        }

        return [
            'ot_reg_holiday_hours' => round($regHolidayOtMinutes / 60, 2),
            'ot_spec_holiday_hours'=> round($specHolidayOtMinutes / 60, 2),
            'ot_reg_ho_rdr'        => round($regHolidayRdrMinutes / 60, 2),
            'ot_spec_ho_rdr'       => round($specHolidayRdrMinutes / 60, 2),
            'ot_night_diff_rdr'    => round($nightDiffRdrMinutes / 60, 2),
        ];
    }

    private function isRestDay($employeeId, $date): bool
    {
        $schedule = EmployeeSchedule::where('employee_id', $employeeId)
            ->where('date', $date)
            ->first();

        return $schedule && strtoupper($schedule->shift_code) === 'RDR';
    }



    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('HR.overtime.manage_overtime');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not implemented in this snippet
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id_input' => 'required|exists:employees,employee_id', // Assuming 'employees' table
            'ot_date_input' => 'required|date',
            'ot_in_input' => 'required|date_format:H:i', // Adjusted to H:i based on your log example
            'ot_out_input' => 'required|date_format:H:i', // Adjusted to H:i
        ]);

        if ($validator->fails()) {
            Log::info('Overtime filing validation failed: ' . json_encode($validator->errors()));
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $employeeId = $request->employee_id_input;
        $otDate = Carbon::parse($request->ot_date_input);
        $otInTimeRaw = $request->ot_in_input;
        $otOutTimeRaw = $request->ot_out_input;

        Log::info("Attempting to file overtime: " . json_encode($request->all()));

        // Check for existing overtime on the same date for the same employee
        $existingOvertime = Overtime::where('employee_id', $employeeId)
                                    ->where('ot_date', $otDate->toDateString())
                                    ->first();

        if ($existingOvertime) {
            Log::info("Existing overtime found for employee " . $employeeId . " on " . $otDate->toDateString() . ". Preventing duplicate.");
            return response()->json(['message' => 'Overtime already filed for this employee on this date.'], 409); // Conflict
        }

        Log::info("No existing overtime found for this employee and date. Proceeding with creation.");

        // Create Carbon instances with full date and time context for calculations
        $otInCarbon = Carbon::parse($otDate->format('Y-m-d') . ' ' . $otInTimeRaw . ':00'); // Add seconds for strict format
        $otOutCarbon = Carbon::parse($otDate->format('Y-m-d') . ' ' . $otOutTimeRaw . ':00'); // Add seconds

        Log::debug("Store - Parsed otInCarbon: " . $otInCarbon->toDateTimeString());
        Log::debug("Store - Parsed otOutCarbon: " . $otOutCarbon->toDateTimeString());

        // Handle overtime crossing midnight
        if ($otOutCarbon->lt($otInCarbon)) {
            $otOutCarbon->addDay();
            Log::debug("Store - otOutCarbon adjusted for midnight cross: " . $otOutCarbon->toDateTimeString());
        }

        // Calculate total_ot_hours
        $totalOtHours = round($otInCarbon->diffInMinutes($otOutCarbon) / 60, 2);
        Log::info('Calculated total overtime hours: ' . json_encode(["total_ot_hours" => $totalOtHours]));

        // Calculate all detailed overtime components
        $overtimeDetails = $this->calculateOvertimeDetails($employeeId, $otInCarbon, $otOutCarbon);


        DB::beginTransaction();
        try {
            $overtime = Overtime::create([
                'employee_id' => $employeeId,
                'ot_date' => $otDate->toDateString(),
                'ot_in' => $otInTimeRaw . ':00', // Store with seconds for consistency if your DB expects it
                'ot_out' => $otOutTimeRaw . ':00', // Store with seconds
                'total_ot_hours' => $totalOtHours,
                'is_approved' => false, // New overtime is typically not approved initially
                'approved_hours' => 0,
                'ot_night_diff_hours' => $overtimeDetails['ot_night_diff_hours'],
                'ot_reg_holiday_hours' => $overtimeDetails['ot_reg_holiday_hours'],
                'ot_spec_holiday_hours' => $overtimeDetails['ot_spec_holiday_hours'],
                'ot_reg_holiday_nd_hours' => $overtimeDetails['ot_reg_holiday_nd_hours'],
                'ot_spec_holiday_nd_hours' => $overtimeDetails['ot_spec_holiday_nd_hours'],
            ]);

            DB::commit();
            Log::info('Overtime filed successfully: ' . $overtime->id);
            return response()->json(['message' => 'Overtime filed successfully!', 'data' => $overtime], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error filing overtime: " . $e->getMessage());
            return response()->json(['message' => 'Failed to file overtime.', 'error' => $e->getMessage()], 500);
        }
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}