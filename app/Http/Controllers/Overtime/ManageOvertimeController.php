<?php

namespace App\Http\Controllers\Overtime;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Overtime;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // For logging during development/debugging

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
            // Re-parse Carbon instances for ot_in and ot_out from the stored record
            // It's crucial to create Carbon objects with full date and time context
            $otDate = Carbon::parse($ot->ot_date);
            $otInTime = Carbon::parse($otDate->format('Y-m-d') . ' ' . $ot->ot_in);
            $otOutTime = Carbon::parse($otDate->format('Y-m-d') . ' ' . $ot->ot_out);

            Log::debug("Approve - Original otInTime: " . $otInTime->toDateTimeString());
            Log::debug("Approve - Original otOutTime: " . $otOutTime->toDateTimeString());

            // Handle overtime crossing midnight by adding a day to ot_out if it's earlier than ot_in
            // This ensures correct duration calculation for overnight shifts
            if ($otOutTime->lt($otInTime)) {
                $otOutTime->addDay();
                Log::debug("Approve - otOutTime adjusted for midnight cross: " . $otOutTime->toDateTimeString());
            }

            // Recalculate total_ot_hours based on actual duration, ensuring a positive value
            $calculatedTotalOtHours = round($otInTime->diffInMinutes($otOutTime) / 60, 2);
            Log::debug("Approve - Calculated total_ot_hours: " . $calculatedTotalOtHours);


            // Recalculate all overtime details using the helper function
            $overtimeDetails = $this->calculateOvertimeDetails($otInTime, $otOutTime);

            $ot->is_approved = true;
            // When approving, it's reasonable to set approved_hours to the newly calculated total_ot_hours
            $ot->approved_hours = $calculatedTotalOtHours;
            // Also update total_ot_hours in case the original calculation was flawed or needed re-validation
            $ot->total_ot_hours = $calculatedTotalOtHours;


            // Update all the computed columns with fresh calculations
           
            $ot->ot_reg_holiday_hours = $overtimeDetails['ot_reg_holiday_hours'];
            $ot->ot_spec_holiday_hours = $overtimeDetails['ot_spec_holiday_hours'];
    

            $ot->save();

            DB::commit();
            return response()->json(['message' => 'Overtime approved successfully and components re-calculated.', 'data' => $ot]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error approving and recalculating overtime (ID: {$id}): " . $e->getMessage());
            return response()->json(['message' => 'Failed to approve and re-calculate overtime.', 'error' => $e->getMessage()], 500);
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
            'ot_in' => 'required|date_format:H:i:s', // Ensure time format is strict
            'ot_out' => 'required|date_format:H:i:s', // Ensure time format is strict
            // total_ot_hours is now computed, so not strictly required from request, but good for validation
            'total_ot_hours' => 'nullable|numeric|min:0', // Must be non-negative if provided
            'approved_hours' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ot = Overtime::findOrFail($id);

        // Parse ot_date, ot_in, ot_out into Carbon instances for calculation
        $otDate = Carbon::parse($request->ot_date);
        $otInTime = Carbon::parse($otDate->format('Y-m-d') . ' ' . $request->ot_in);
        $otOutTime = Carbon::parse($otDate->format('Y-m-d') . ' ' . $request->ot_out);

        Log::debug("Update - Original otInTime: " . $otInTime->toDateTimeString());
        Log::debug("Update - Original otOutTime: " . $otOutTime->toDateTimeString());

        // Handle overtime crossing midnight
        if ($otOutTime->lt($otInTime)) {
            $otOutTime->addDay();
            Log::debug("Update - otOutTime adjusted for midnight cross: " . $otOutTime->toDateTimeString());
        }

        // Calculate total_ot_hours based on actual duration.
        // This is important to ensure consistency, especially if ot_in/ot_out are changed.
        $calculatedTotalOtHours = round($otInTime->diffInMinutes($otOutTime) / 60, 2);
        Log::debug("Update - Calculated total_ot_hours: " . $calculatedTotalOtHours);


        // Call the helper to calculate night differential and holiday overtime components
        $overtimeDetails = $this->calculateOvertimeDetails($otInTime, $otOutTime);

        DB::beginTransaction();
        try {
            $ot->ot_date = $otDate->toDateString();
            $ot->ot_in = $request->ot_in;
            $ot->ot_out = $request->ot_out;
            $ot->total_ot_hours = $calculatedTotalOtHours; // Use calculated hours
            // If approved_hours is not provided in request, retain existing. Otherwise, use new.
            $ot->approved_hours = $request->approved_hours ?? $ot->approved_hours;

            // Store the newly calculated special overtime hours
            $ot->ot_reg_holiday_hours = $overtimeDetails['ot_reg_holiday_hours'];
            $ot->ot_spec_holiday_hours = $overtimeDetails['ot_spec_holiday_hours'];
           

            $ot->save();

            DB::commit();
            return response()->json(['message' => 'Overtime record updated successfully.', 'data' => $ot]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating overtime record (ID: {$id}): " . $e->getMessage());
            return response()->json(['message' => 'Failed to update overtime record.', 'error' => $e->getMessage()], 500);
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
    private function calculateOvertimeDetails(Carbon $otInCarbon, Carbon $otOutCarbon): array
    {
     
        $regHolidayOtMinutes = 0;
        $specHolidayOtMinutes = 0;
       


        $currentMinute = $otInCarbon->copy();

        Log::debug("Starting calculateOvertimeDetails loop from: " . $currentMinute->toDateTimeString() . " to " . $otOutCarbon->toDateTimeString());

        while ($currentMinute->lt($otOutCarbon)) {
            // FIX: Define $currentHour at the beginning of each loop iteration
            $currentHour = $currentMinute->hour;

            // Format the current minute's date to match the holiday table's format (YYYY/MM/DD)
            $minuteDateFormattedForHoliday = $currentMinute->format('Y/m/d');

            Log::debug("Checking for holiday on formatted date: " . $minuteDateFormattedForHoliday);


            // Check for Holiday on the current minute's date using the corrected format
            $holidayForMinute = Holiday::where('date', $minuteDateFormattedForHoliday)->first();
            $isMinuteOnRegularHoliday = ($holidayForMinute && $holidayForMinute->type === 'REGULAR HOLIDAY');
            $isMinuteOnSpecialHoliday = ($holidayForMinute && $holidayForMinute->type === 'SPECIAL NON-WORKING HOLIDAY');

            // Log holiday check results
            if ($isMinuteOnRegularHoliday) {
                Log::debug("HOLIDAY DETECTED (REGULAR) for: " . $currentMinute->toDateTimeString());
            } elseif ($isMinuteOnSpecialHoliday) {
                Log::debug("HOLIDAY DETECTED (SPECIAL) for: " . $currentMinute->toDateTimeString());
            }

            // Accumulate Holiday Overtime Minutes
            if ($isMinuteOnRegularHoliday) {
                $regHolidayOtMinutes++;
                // Log::debug("Minute on Regular Holiday: " . $currentMinute->toDateTimeString());

            } if ($isMinuteOnSpecialHoliday) {
                $specHolidayOtMinutes++;
                // Log::debug("Minute on Special Holiday: " . $currentMinute->toDateTimeString());
            }


            $currentMinute->addMinute();
        }

        return [
            'ot_reg_holiday_hours' => round($regHolidayOtMinutes / 60, 2),
            'ot_spec_holiday_hours' => round($specHolidayOtMinutes / 60, 2),      
        ];
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
        $overtimeDetails = $this->calculateOvertimeDetails($otInCarbon, $otOutCarbon);

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
                // Store the newly calculated special overtime hours
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