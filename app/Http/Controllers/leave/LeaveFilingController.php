<?php

namespace App\Http\Controllers\leave;

use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use App\Models\Leavecredit;
use App\Models\Employee;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class LeaveFilingController extends Controller
{
    /**
     * Display a listing of leave types for the filing form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $leaveTypes = LeaveType::all();
        return view('HR.leave.leave_filing', compact('leaveTypes'));
    }

    /**
     * Show the form for creating a new leave application.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $leaveTypes = LeaveType::all();
        return view('HR.leave.leave_filing', compact('leaveTypes'));
    }

    /**
     * Store a newly created leave application in storage.
     * Handles validation, total_days calculation for half-day leaves,
     * and checks for existing applications.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate incoming request data
            $validatedData = $request->validate([
                'employee_id' => 'required|string|max:255', // Assuming this is the employee's external ID
                'date_start' => 'required|date',
                'date_end' => 'required|date|after_or_equal:date_start',
                'leave_type' => 'required|string|exists:leave_types,name', // Validate that the name exists in leave_types table
                'reason' => 'required|string|max:255'
            ]);

            $dateStart = Carbon::parse($validatedData['date_start'])->startOfDay();
            $dateEnd = Carbon::parse($validatedData['date_end'])->startOfDay();
            
            // Fetch the LeaveType model based on the name provided
            $leaveType = LeaveType::where('name', $validatedData['leave_type'])->first();

            if (!$leaveType) {
                // This should ideally be caught by the 'exists' validation rule, but as a safeguard
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Leave Type provided.'
                ], 422);
            }

            // --- IMPORTANT: Calculate total_days based on leave type name ---
            $calculatedTotalDays = 1.0; // Default to a full day
            $halfDayLeaveTypes = [
                'Vacation Leave AM', 'Vacation Leave PM',
                'Sick Leave AM', 'Sick Leave PM',
                'Emergency Leave AM', 'Emergency Leave PM',
            ];

            if (in_array($leaveType->name, $halfDayLeaveTypes)) {
                // For half-day leaves, total_days should be 0.5
                // Enforce that half-day leaves must have the same start and end date
                if ($dateStart->toDateString() !== $dateEnd->toDateString()) {
                     return response()->json([
                        'success' => false,
                        'message' => 'Half-day leaves must have the same start and end date.'
                    ], 422);
                }
                $calculatedTotalDays = 0.5;
            } else {
                // For full-day leaves, calculate based on date difference
                $calculatedTotalDays = $dateStart->diffInDays($dateEnd) + 1;
            }
            // --- END IMPORTANT LOGIC ---

            // Ensure calculated total days is positive
            if ($calculatedTotalDays <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'The leave duration must be at least 0.5 day for half-day leaves or 1 day for full-day leaves.'
                ], 422);
            }

            // Check for existing leave application for the same period and type for the same employee
            $existingLeave = Leave::where('employee_id', $validatedData['employee_id'])
                ->where('date_start', $validatedData['date_start'])
                ->where('date_end', $validatedData['date_end'])
                ->where('leave_type_id', $leaveType->id) // Use leave_type_id for consistency
                ->first();

            if ($existingLeave) {
                return response()->json([
                    'success' => false,
                    'message' => 'A leave application for this period and type already exists.'
                ], 409);
            }

            // Create the new leave record
            Leave::create([
                'employee_id' => $validatedData['employee_id'],
                'date_start' => $dateStart->toDateString(),
                'date_end' => $dateEnd->toDateString(),
                'leave_type_id' => $leaveType->id, // Use the ID from the fetched LeaveType model
                'reason' => $validatedData['reason'],
                'total_days' => $calculatedTotalDays, // Store the calculated total days
                'status' => 'pending', // Default status for newly filed leaves
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Leave successfully filed for ' . $calculatedTotalDays . ' day(s).'
            ]);
        } catch (ValidationException $e) {
            // Catch Laravel's validation exceptions
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            // Catch any other general exceptions
            \Log::error('Leave Filing Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit leave application. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leave credits for a specific employee.
     *
     * @param string $employee_id The external employee ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLeaveCredits($employee_id)
    {
        // Find the employee by the given "employee_id" (the external code)
        $employee = Employee::where('employee_id', $employee_id)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found.'
            ], 404);
        }

        // Fetch ALL leave types so the frontend can always show all columns
        $allLeaveTypes = LeaveType::pluck('name', 'id');

        // Get existing credits for the employee
        $leaveCredits = Leavecredit::where('employee_id', $employee->employee_id)
            ->get()
            ->keyBy('leave_type_id'); // Easier to look up by leave_type_id

        // Build formatted response
        $formattedCredits = [];
        foreach ($allLeaveTypes as $typeId => $typeName) {
            if ($leaveCredits->has($typeId)) {
                $credit = $leaveCredits[$typeId];
                $formattedCredits[$typeName] = [
                    'all_leave' => $credit->all_leave,
                    'rem_leave' => $credit->rem_leave,
                ];
            } else {
                // If no credit record exists for this leave type, default to 0
                $formattedCredits[$typeName] = [
                    'all_leave' => 0,
                    'rem_leave' => 0,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'leave_credits' => $formattedCredits
        ]);
    }

    // Unused resource methods (show, edit, update, destroy)
    public function show(string $id) { /* ... */ }
    public function edit(string $id) { /* ... */ }
    public function update(Request $request, string $id) { /* ... */ }
    public function destroy(string $id) { /* ... */ }
}
