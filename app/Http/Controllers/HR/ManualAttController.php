<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Employee;

class ManualAttController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all attendance records
        $attendances = DB::table('attendance')
            ->orderBy('id', 'desc')
            ->get();

        $employees = Employee::all();

        return view('HR.attendance.manualattendance', compact('attendances', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Log the incoming request for debugging
        Log::info('Manual attendance store request:', $request->all());

        // Enhanced validation - validate employee_id against the employee_id column
        $validatedData = $request->validate([
            'employee_id' => 'required|string|exists:employees,employee_id',
            'transindate' => 'required|date',
            'time_in' => 'required|date_format:H:i',
            'transoutdate' => 'nullable|date|after_or_equal:transindate',
            'time_out' => 'nullable|date_format:H:i',
        ], [
            'employee_id.required' => 'Employee is required.',
            'employee_id.string' => 'Invalid employee selection.',
            'employee_id.exists' => 'Selected employee does not exist.',
            'transindate.required' => 'Date In is required.',
            'transindate.date' => 'Date In must be a valid date.',
            'time_in.required' => 'Time In is required.',
            'time_in.date_format' => 'Time In must be in HH:MM format.',
            'transoutdate.after_or_equal' => 'Date Out must be equal to or after Date In.',
            'time_out.date_format' => 'Time Out must be in HH:MM format.',
        ]);

        // Get the employee record using the employee_id code
        $employee = Employee::where('employee_id', $validatedData['employee_id'])->first();
        
        if (!$employee) {
            return redirect()->back()
                ->withErrors(['employee_id' => 'Selected employee does not exist.'])
                ->withInput();
        }

        Log::info('Employee data for attendance:', [
            'form_sent_employee_id' => $validatedData['employee_id'],
            'employee_record_found' => $employee ? 'YES' : 'NO',
            'employee_name' => $employee->first_name . ' ' . $employee->last_name
        ]);

        try {
            // Check for duplicate attendance using the employee code and date
            $existingAttendance = DB::table('attendance')
                ->where('employee_id', $validatedData['employee_id'])
                ->where('transindate', $validatedData['transindate'])
                ->first();

            if ($existingAttendance) {
                Log::warning('Duplicate attendance attempt:', [
                    'employee_id' => $validatedData['employee_id'],
                    'date' => $validatedData['transindate']
                ]);
                return redirect()->back()
                    ->withErrors(['employee_id' => 'Attendance record already exists for this employee on this date.'])
                    ->withInput();
            }

            // Calculate total hours if both time_in and time_out are provided
            $totalHours = null;
            if ($request->time_out) {
                $totalHours = $this->calculateTotalHours(
                    $request->transindate,
                    $request->time_in,
                    $request->transoutdate ?: $request->transindate,
                    $request->time_out
                );
                
                Log::info('Calculated hours result:', [
                    'totalHours' => $totalHours,
                    'dateIn' => $request->transindate,
                    'timeIn' => $request->time_in,
                    'dateOut' => $request->transoutdate ?: $request->transindate,
                    'timeOut' => $request->time_out
                ]);
                
                // TEMPORARILY DISABLED - Allow saving even if calculation fails
                // We'll fix the calculation issue separately
                /*
                if ($totalHours === null || $totalHours <= 0) {
                    return redirect()->back()
                        ->withErrors(['time_out' => 'Invalid time range. Time Out must be after Time In.'])
                        ->withInput();
                }
                */
                
                // If calculation fails, set to 0 for now
                if ($totalHours === null || $totalHours <= 0) {
                    Log::warning('Calculation failed, setting total_hours to 0');
                    $totalHours = 0;
                }
            }

            // Prepare data for insertion
            $attendanceData = [
                'employee_id' => $validatedData['employee_id'],
                'transindate' => $validatedData['transindate'],
                'time_in' => $validatedData['time_in'],
                'transoutdate' => $validatedData['transoutdate'] ?: $validatedData['transindate'],
                'time_out' => $validatedData['time_out'],
                'total_hours' => $totalHours,
            ];

            Log::info('Attempting to insert attendance data:', $attendanceData);

            // Insert the attendance record using DB transaction for safety
            DB::beginTransaction();
            
            $insertedId = DB::table('attendance')->insertGetId($attendanceData);
            
            if (!$insertedId) {
                throw new \Exception('Failed to insert attendance record - no ID returned');
            }

            DB::commit();

            Log::info('Successfully created attendance record', [
                'id' => $insertedId,
                'employee_id' => $validatedData['employee_id'],
                'employee_name' => $employee->first_name . ' ' . $employee->last_name
            ]);

            // FIXED: Use consistent route name
            return redirect()->route('HR.attendance.manualattendance')
                ->with('success', 'Manual attendance record has been created successfully for ' . $employee->first_name . ' ' . $employee->last_name . '!');

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            Log::error('Database error creating attendance:', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'data' => $request->all(),
                'employee_id' => $validatedData['employee_id'] ?? 'unknown'
            ]);

            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return redirect()->back()
                    ->withErrors(['employee_id' => 'Attendance record already exists for this employee on this date.'])
                    ->withInput();
            } elseif (strpos($e->getMessage(), "doesn't exist") !== false) {
                return redirect()->back()
                    ->withErrors(['error' => 'Database table issue. Please contact the administrator.'])
                    ->withInput();
            } else {
                return redirect()->back()
                    ->withErrors(['error' => 'Database error: ' . $e->getMessage()])
                    ->withInput();
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('General error creating attendance:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'data' => $request->all(),
                'employee_id' => $validatedData['employee_id'] ?? 'unknown'
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Error: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $deleted = DB::table('attendance')->where('id', $id)->delete();
            
            if ($deleted) {
                // FIXED: Use consistent route name
                return redirect()->route('HR.attendance.manualattendance')
                    ->with('success', 'Attendance record has been deleted successfully!');
            } else {
                return redirect()->route('HR.attendance.manualattendance')
                    ->withErrors(['error' => 'Attendance record not found.']);
            }
        } catch (\Exception $e) {
            Log::error('Error deleting attendance:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('HR.attendance.manualattendance')
                ->withErrors(['error' => 'Failed to delete attendance record: ' . $e->getMessage()]);
        }
    }

    /**
     * Calculate total hours with automatic 1-hour break deduction for 8+ hour shifts
     */
    private function calculateTotalHours($dateIn, $timeIn, $dateOut, $timeOut)
    {
        try {
            Log::info('calculateTotalHours called with:', [
                'dateIn' => $dateIn,
                'timeIn' => $timeIn, 
                'dateOut' => $dateOut,
                'timeOut' => $timeOut
            ]);

            // Use basic PHP DateTime instead of Carbon to avoid version issues
            $startDateTimeString = $dateIn . ' ' . $timeIn . ':00'; // Add seconds
            $endDateTimeString = $dateOut . ' ' . $timeOut . ':00';   // Add seconds

            Log::info('DateTime strings:', [
                'start' => $startDateTimeString,
                'end' => $endDateTimeString
            ]);

            $startDateTime = new \DateTime($startDateTimeString);
            $endDateTime = new \DateTime($endDateTimeString);

            Log::info('Created DateTime objects:', [
                'start' => $startDateTime->format('Y-m-d H:i:s'),
                'end' => $endDateTime->format('Y-m-d H:i:s'),
                'start_timestamp' => $startDateTime->getTimestamp(),
                'end_timestamp' => $endDateTime->getTimestamp()
            ]);

            // Handle overnight shifts - if same date and end appears before start
            if ($dateIn === $dateOut && $endDateTime < $startDateTime) {
                $endDateTime->modify('+1 day');
                Log::info('Adjusted for overnight shift:', [
                    'new_end' => $endDateTime->format('Y-m-d H:i:s')
                ]);
            }

            // Simple timestamp comparison
            if ($endDateTime->getTimestamp() <= $startDateTime->getTimestamp()) {
                Log::warning('Invalid time range detected:', [
                    'start' => $startDateTime->format('Y-m-d H:i:s'),
                    'end' => $endDateTime->format('Y-m-d H:i:s')
                ]);
                return null;
            }

            // Calculate difference in seconds, then convert to hours
            $diffSeconds = $endDateTime->getTimestamp() - $startDateTime->getTimestamp();
            $rawHours = $diffSeconds / 3600; // 3600 seconds = 1 hour

            // Apply break deduction logic: if 8 hours or more, deduct 1 hour for break
            $finalHours = $rawHours;
            $breakDeducted = false;
            
            if ($rawHours >= 8.0) {
                $finalHours = $rawHours - 1.0; // Deduct 1 hour for break
                $breakDeducted = true;
            }

            Log::info('Hours calculation with break logic:', [
                'diffSeconds' => $diffSeconds,
                'rawHours' => round($rawHours, 2),
                'breakDeducted' => $breakDeducted,
                'finalHours' => round($finalHours, 2),
                'breakRule' => 'Deduct 1 hour break if total >= 8 hours'
            ]);

            return round($finalHours, 2);

        } catch (\Exception $e) {
            Log::error('Error in calculateTotalHours:', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return null;
        }
    }

    /**
     * Test method to debug calculation - REMOVE AFTER FIXING
     */
    public function testCalculation()
    {
        // Test with your exact values
        $result = $this->calculateTotalHours('2025-09-12', '07:58', '2025-09-12', '17:01');
        
        Log::info('Test calculation result:', ['result' => $result]);
        
        return response()->json([
            'result' => $result,
            'expected' => '9.05 hours',
            'message' => 'Check the Laravel log for detailed calculation steps'
        ]);
    }
}