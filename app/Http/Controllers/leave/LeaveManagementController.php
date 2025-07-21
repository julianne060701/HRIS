<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Leave;
use App\Models\LeaveCredit;
use App\Models\EmployeeSchedule;
use App\Models\LeaveType;
use App\Models\DTR;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class LeaveManagementController extends Controller
{
    /**
     * Display a listing of all leave records.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function data()
    {
        $leaves = Leave::with('leaveType')->get()->map(function ($leave) {
            $leave->start_date_display = Carbon::parse($leave->date_start)->format('Y-m-d');
            $leave->end_date_display = Carbon::parse($leave->date_end)->format('Y-m-d');
            $leave->leave_type_display = $leave->leaveType 
                ? $leave->leaveType->name 
                : 'N/A';
            return $leave;
        });
    
        return response()->json(['data' => $leaves]);
    }

    /**
     * Approve a specific leave request.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve(Request $request, $id)
    {
        $leave = Leave::with('leaveType')->findOrFail($id); 

        if ($leave->status === 'approved') {
            return response()->json(['message' => 'Leave is already approved.'], 400);
        } elseif ($leave->status === 'disapproved') {
            return response()->json(['message' => 'Cannot approve a disapproved leave. Please create a new request.'], 400);
        }

        DB::beginTransaction();
        try {
            // Step 1: Check and Deduct Leave Credits FIRST
            // This method correctly maps 'AM'/'PM' leaves to their parent
            // and deducts the exact total_days (e.g., 0.5 or 1.0) from the parent credit.
            $this->deductLeaveCredits($leave); 

            // Step 2: Update Leave Request Status
            $leave->status = 'approved';
            $leave->approved_by = Auth::check() ? Auth::user()->name : 'System';
            $leave->approved_at = Carbon::now();
            $leave->save();

            // --- Step 3: Update Employee Schedule and DTR for Approved Leave ---
            $this->updateEmployeeScheduleForLeave($leave);

            DB::commit();
            return response()->json([
                'message' => 'Leave approved successfully, credits deducted, and schedule & DTR updated.', 
                'data' => $leave->load('leaveType')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error approving leave (ID: {$id}): " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to approve leave.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper method to update employee's schedule and DTR for approved leave.
     *
     * @param \App\Models\Leave $leave The approved leave record.
     * @return void
     */
    private function updateEmployeeScheduleForLeave(Leave $leave)
    {
        $startDate = Carbon::parse($leave->date_start);
        $endDate = Carbon::parse($leave->date_end);

        $leaveTypeName = $leave->leaveType ? $leave->leaveType->name : 'Leave';
        $leaveHours = $this->getLeaveDeductionHours($leaveTypeName);
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $currentDateString = $date->toDateString();

            EmployeeSchedule::updateOrCreate(
                [
                    'employee_id' => $leave->employee_id,
                    'date' => $currentDateString,
                ],
                [
                    'shift_code' => null,
                    'leave_type_id' => $leave->leave_type_id,
                ]
            );
            
            DTR::updateOrCreate(
                [
                    'employee_id' => $leave->employee_id,
                    'transindate' => $currentDateString,
                ],
                [
                    'time_in' => null,
                    'time_out' => null,
                    'total_hours' => $leaveHours,
                    // 'status' => 'leave',
                    'leave_type_id' => $leave->leave_type_id,
                    // 'leave_id' => $leave->id,
                ]
            );

            Log::info("Schedule and DTR updated for employee {$leave->employee_id} on {$currentDateString}. Status: '{$leaveTypeName}' (Leave Type ID: {$leave->leave_type_id}). DTR total_hours: {$leaveHours}.");
        }
    }

    /**
     * Helper method to revert employee's schedule and DTR for disapproved leave.
     *
     * @param \App\Models\Leave $leave The disapproved leave record.
     * @return void
     */
    private function revertEmployeeScheduleForLeave(Leave $leave)
    {
        $startDate = Carbon::parse($leave->date_start);
        $endDate = Carbon::parse($leave->date_end);

        // Revert EmployeeSchedule entries
        EmployeeSchedule::where('employee_id', $leave->employee_id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('leave_type_id', $leave->leave_type_id) // Only revert if it was this specific leave type
            ->update([
                'shift_code' => null, // Or revert to original shift if known
                'leave_type_id' => null
            ]);

        // Revert DTR entries
        DTR::where('employee_id', $leave->employee_id)
            ->whereBetween('transindate', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('leave_id', $leave->id) // Only revert if it was linked to this specific leave
            ->update([
                'time_in' => null, // Or revert to original values if known
                'time_out' => null,
                'total_hours' => 0, // Assuming 0 for absent, or calculate based on original shift
                'status' => 'absent', // Mark as absent if leave is disapproved
                'leave_id' => null,
                'leave_type_id' => null,
                'shift_code' => null, // Or revert to original shift if known
            ]);
            
        Log::info("Schedule and DTR reverted for employee {$leave->employee_id} from {$startDate->toDateString()} to {$endDate->toDateString()} due to disapproved leave (Leave ID: {$leave->id}).");
    }

    /**
     * Disapprove a specific leave request.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function disapprove(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->status === 'disapproved') {
            return response()->json(['message' => 'Leave is already disapproved.'], 400);
        }

        DB::beginTransaction();
        try {
            // Only re-add credits and revert schedule if the leave was previously approved
            if ($leave->status === 'approved') {
                $this->readdLeaveCredits($leave); 
                $this->revertEmployeeScheduleForLeave($leave);
            }

            $leave->status = 'disapproved';
            $leave->approved_by = Auth::check() ? Auth::user()->name : 'System'; // Record who disapproved
            $leave->approved_at = Carbon::now(); // Record when it was disapproved
            $leave->save();

            DB::commit();
            return response()->json(['message' => 'Leave disapproved successfully.', 'data' => $leave]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error disapproving leave (ID: {$id}): " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to disapprove leave.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Deducts `total_days` from the `rem_leave` in the `leave_credits` table.
     *
     * @param \App\Models\Leave|\stdClass $leave The approved leave record.
     * @throws \Exception If leave credit record not found or insufficient credits.
     * @return bool True if deduction was successful.
     */
    private function deductLeaveCredits($leave): bool
    {
        // Map the specific leave type (e.g., 'Vacation Leave AM') to its parent (e.g., 'Vacation Leave')
        $mappedLeaveTypeId = $this->mapToParentLeaveTypeId($leave->leave_type_id);

        // Find the employee's leave credit for the mapped leave type
        // IMPORTANT: Added orderBy('updated_at', 'desc') to ensure the latest record is picked
        $leaveCredit = LeaveCredit::where('employee_id', $leave->employee_id)
                                   ->where('leave_type_id', $mappedLeaveTypeId)
                                   ->orderBy('updated_at', 'desc') // <--- ADDED THIS LINE
                                   ->first();

        if (!$leaveCredit) {
            $message = "Leave credit record not found for employee ID: {$leave->employee_id} and mapped leave type ID: {$mappedLeaveTypeId}. Cannot deduct leave credits.";
            Log::error($message);
            throw new \Exception($message);
        }

        // Check if enough remaining leave credits
        if ($leaveCredit->rem_leave < $leave->total_days) {
            $message = "Insufficient leave credits for employee ID: {$leave->employee_id} for leave type ID: {$mappedLeaveTypeId}. Remaining: {$leaveCredit->rem_leave}, Requested: {$leave->total_days}.";
            Log::warning($message);
            throw new \Exception($message);
        }

        try {
            $leaveCredit->rem_leave -= $leave->total_days;
            $leaveCredit->save();
            Log::info("Successfully deducted {$leave->total_days} days from leave credits for employee ID: {$leave->employee_id}, Leave Type ID: {$mappedLeaveTypeId}. New remaining: {$leaveCredit->rem_leave}.");
            return true;
        } catch (\Exception $e) {
            $message = "Failed to deduct leave credit for employee ID: {$leave->employee_id}, Leave Type ID: {$mappedLeaveTypeId}. Error: " . $e->getMessage();
            Log::error($message, ['exception' => $e]);
            throw new \Exception($message, 0, $e);
        }
    }

    /**
     * Re-adds `total_days` back to the `rem_leave` in the `leave_credits` table.
     *
     * @param \App\Models\Leave|\stdClass $leave The disapproved leave record that was previously approved, or a dummy object for credit adjustment.
     * @throws \Exception If leave credit record not found.
     * @return bool True if re-addition was successful.
     */
    private function readdLeaveCredits($leave): bool
    {
        // Map the specific leave type (e.g., 'Vacation Leave AM') to its parent (e.g., 'Vacation Leave')
        $mappedLeaveTypeId = $this->mapToParentLeaveTypeId($leave->leave_type_id);

        $leaveCredit = LeaveCredit::where('employee_id', $leave->employee_id)
                                 ->where('leave_type_id', $mappedLeaveTypeId)
                                 ->orderBy('updated_at', 'desc') // This was already correct here
                                 ->first();

        if (!$leaveCredit) {
            $message = "Leave credit record not found for employee ID: {$leave->employee_id} and leave type ID: {$mappedLeaveTypeId}. Cannot re-add leave credits.";
            Log::error($message);
            throw new \Exception($message);
        }

        try {
            $leaveCredit->rem_leave += $leave->total_days;
            $leaveCredit->save();
            Log::info("Successfully re-added {$leave->total_days} days to leave credits for employee ID: {$leave->employee_id}, Leave Type ID: {$mappedLeaveTypeId}. New remaining: {$leaveCredit->rem_leave}.");
            return true;
        } catch (\Exception $e) {
            $message = "Failed to re-add leave credit for employee ID: {$leave->employee_id}, Leave Type ID: {$mappedLeaveTypeId}. Error: " . $e->getMessage();
            Log::error($message, ['exception' => $e]);
            throw new \Exception($message, 0, $e);
        }
    }

    /**
     * Maps a specific leave type ID (especially for AM/PM variants) to its parent leave type ID.
     * This is crucial for correctly managing leave credits, which are usually tracked per main leave type.
     *
     * @param int $leaveTypeId The ID of the leave type being processed.
     * @return int The ID of the parent leave type.
     * @throws \Exception If the leave type or its parent cannot be found.
     */
    private function mapToParentLeaveTypeId(int $leaveTypeId): int
    {
        $leaveType = LeaveType::find($leaveTypeId);

        if (!$leaveType) {
            throw new \Exception("LeaveType with ID {$leaveTypeId} not found during mapping for credit deduction/re-addition.");
        }

        // Define a mapping from specific leave type names to their parent names.
        // This array maps the 'child' leave type name to its 'parent' leave type name.
        $parentLeaveMap = [
            'Vacation Leave AM' => 'Vacation Leave',
            'Vacation Leave PM' => 'Vacation Leave',
            'Sick Leave AM' => 'Sick Leave',
            'Sick Leave PM' => 'Sick Leave',
            'Emergency Leave AM' => 'Emergency Leave',
            'Emergency Leave PM' => 'Emergency Leave',
            // Add other half-day mappings as needed
            // Example: 'Maternity Leave AM' => 'Maternity Leave' if applicable
        ];

        // Determine the parent type name. If not in map, the leave type itself is its parent.
        $parentTypeName = $parentLeaveMap[$leaveType->name] ?? $leaveType->name;

        // Find the ID of the parent leave type
        $parentLeaveType = LeaveType::where('name', $parentTypeName)->first();

        if (!$parentLeaveType) {
            throw new \Exception("Parent LeaveType '{$parentTypeName}' not found during mapping for original LeaveType ID {$leaveTypeId}. Ensure parent leave types exist in the database.");
        }

        return $parentLeaveType->id;
    }

    /**
     * Update leave entry.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Manual Validation
        $validator = Validator::make($request->all(), [
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'leave_type' => 'required|string', // Assuming 'leave_type' name is passed
            'reason' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $leaveType = LeaveType::where('name', 'LIKE', '%' . $request->leave_type . '%')->first();

        if (!$leaveType) {
            Log::error("Leave Type not found for name: " . $request->leave_type);
            return response()->json(['message' => 'Invalid Leave Type provided.'], 422); 
        }

        $leave = Leave::findOrFail($id);

        DB::beginTransaction();
        try {
            $originalTotalDays = $leave->total_days;
            $originalLeaveTypeId = $leave->leave_type_id;
            $originalStatus = $leave->status;
            $originalDateStart = $leave->date_start;
            $originalDateEnd = $leave->date_end;

            // --- IMPORTANT: Calculate total_days based on leave type name ---
            $calculatedTotalDays = 0.5; // Default to a full day
            $halfDayLeaveTypes = [
                'Vacation Leave AM', 'Vacation Leave PM',
                'Sick Leave AM', 'Sick Leave PM',
                'Emergency Leave AM', 'Emergency Leave PM',
            ];

            if (in_array($leaveType->name, $halfDayLeaveTypes)) {
                // For half-day leaves, total_days should be 0.5
                // Ensure date_start and date_end are the same for half-day leaves during validation
                if (Carbon::parse($request->date_start)->toDateString() !== Carbon::parse($request->date_end)->toDateString()) {
                    throw new \Exception('Half-day leaves must have the same start and end date for updates.');
                }
                $calculatedTotalDays = 0.5;
            } else {
                // For full-day leaves, calculate based on date difference
                $startDate = Carbon::parse($request->date_start);
                $endDate = Carbon::parse($request->date_end);
                $calculatedTotalDays = $startDate->diffInDays($endDate) + 1;
            }
            // --- END IMPORTANT LOGIC ---

            $leave->date_start = $request->date_start;
            $leave->date_end = $request->date_end;
            $leave->leave_type_id = $leaveType->id;
            $leave->reason = $request->reason;
            $leave->total_days = $calculatedTotalDays; // Use the calculated value
            $leave->save();

            // Handle credit and schedule adjustments if the leave was already approved
            if ($originalStatus === 'approved') {
                $needsCreditAdjustment = ($originalLeaveTypeId !== $leave->leave_type_id || $originalTotalDays !== $leave->total_days);
                $needsScheduleReversion = ($originalDateStart !== $leave->date_start || $originalDateEnd !== $leave->date_end || $needsCreditAdjustment);

                if ($needsCreditAdjustment) {
                    // Create a dummy object representing the old leave state for re-adding credits
                    $dummyOldLeaveForCredits = (object)[
                        'employee_id' => $leave->employee_id,
                        'leave_type_id' => $originalLeaveTypeId,
                        'total_days' => $originalTotalDays,
                    ];
                    $this->readdLeaveCredits($dummyOldLeaveForCredits); // Re-add old credits

                    $this->deductLeaveCredits($leave); // Deduct new credits
                }
                
                if ($needsScheduleReversion) {
                    // Create a dummy object representing the old leave state for schedule reversion
                    $dummyOldLeaveForSchedule = (object)[
                        'employee_id' => $leave->employee_id,
                        'leave_type_id' => $originalLeaveTypeId,
                        'id' => $leave->id, // Pass original leave ID for DTR lookup
                        'date_start' => $originalDateStart,
                        'date_end' => $originalDateEnd,
                    ];
                    $this->revertEmployeeScheduleForLeave($dummyOldLeaveForSchedule); // Revert old schedule entries
                    
                    $this->updateEmployeeScheduleForLeave($leave); // Apply new schedule entries
                }
            }

            DB::commit();
            return response()->json(['message' => 'Leave record updated successfully.', 'data' => $leave->load('leaveType')]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating leave record (ID: {$id}): " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to update leave record.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Retrieves all leave types.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLeaveTypes()
    {
        $leaveTypes = LeaveType::all(['id', 'name']);
        return response()->json(['data' => $leaveTypes]);
    }

    /**
     * Displays the leave management view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('HR.leave.leave_manage');
    }

    /**
     * Store a newly created leave record in storage.
     * This method now includes logic to calculate total_days based on leave type.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Manual Validation for new leave creation
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,employee_id', // Assuming 'employees' table and 'employee_id' column
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'leave_type' => 'required|string', // Assuming 'leave_type' name is passed
            'reason' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $leaveType = LeaveType::where('name', 'LIKE', '%' . $request->leave_type . '%')->first();

        if (!$leaveType) {
            Log::error("Leave Type not found for name: " . $request->leave_type);
            return response()->json(['message' => 'Invalid Leave Type provided.'], 422); 
        }

        DB::beginTransaction();
        try {
            // --- IMPORTANT: Calculate total_days based on leave type name ---
            $calculatedTotalDays = 1.0; // Default to a full day
            $halfDayLeaveTypes = [
                'Vacation Leave AM', 'Vacation Leave PM',
                'Sick Leave AM', 'Sick Leave PM',
                'Emergency Leave AM', 'Emergency Leave PM',
            ];

            if (in_array($leaveType->name, $halfDayLeaveTypes)) {
                // For half-day leaves, total_days should be 0.5
                // Ensure date_start and date_end are the same for half-day leaves
                if (Carbon::parse($request->date_start)->toDateString() !== Carbon::parse($request->date_end)->toDateString()) {
                    throw new \Exception('Half-day leaves must have the same start and end date.');
                }
                $calculatedTotalDays = 0.5;
            } else {
                // For full-day leaves, calculate based on date difference
                $startDate = Carbon::parse($request->date_start);
                $endDate = Carbon::parse($request->date_end);
                $calculatedTotalDays = $startDate->diffInDays($endDate) + 1;
            }
            // --- END IMPORTANT LOGIC ---

            $leave = new Leave();
            $leave->employee_id = $request->employee_id;
            $leave->date_start = $request->date_start;
            $leave->date_end = $request->date_end;
            $leave->leave_type_id = $leaveType->id;
            $leave->reason = $request->reason;
            $leave->total_days = $calculatedTotalDays; // Use the calculated value
            $leave->status = 'pending'; // Default status for new requests
            $leave->save();

            DB::commit();
            return response()->json(['message' => 'Leave request created successfully.', 'data' => $leave->load('leaveType')], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating leave record: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to create leave record.', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Determines the number of hours for DTR based on the leave type name.
     * Half-day leaves are 4.0 hours, full-day leaves are 8.0 hours, and
     * non-paid/non-creditable leaves are 0.0 hours.
     *
     * @param string $leaveTypeName The name of the leave type.
     * @return float The total hours to record in DTR.
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

    // Unused resource methods (show, edit, destroy)
    public function show(string $id) { /* ... */ }
    public function edit(string $id) { /* ... */ }
    public function destroy(string $id) { /* ... */ }
}
