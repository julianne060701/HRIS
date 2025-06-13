<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\DTR;
use App\Models\Employee;
use Carbon\Carbon;

class ProcessDTRController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        
    $data = DB::table('employee_schedules')
    ->select(
        'employee_schedules.employee_id',
                    'employee_schedules.shift_code',
        DB::raw("CONCAT(COALESCE(employees.first_name, ''), ' ', COALESCE(employees.last_name, '')) AS employee_name"),
        'employee_schedules.date',
        'schedule.xptd_time_in AS plotted_time_in',
        'schedule.xptd_time_out AS plotted_time_out',
        'attendance.time_in AS actual_time_in',
        'attendance.time_out AS actual_time_out'
    )
    ->leftJoin('employees', 'employee_schedules.employee_id', '=', 'employees.employee_id')
    ->leftJoin('schedule', 'employee_schedules.shift_code', '=', 'schedule.shift_code')
    
    ->leftJoin('attendance', function ($join) {
        $join->on('employee_schedules.employee_id', '=', 'attendance.employee_id')
             ->whereRaw('DATE(attendance.transindate) = employee_schedules.date'); 
    })
    ->orderByDesc('employee_schedules.date')
    ->get();
    //  dd($data); // <--- ADD THIS LINE TEMPORARILY

    
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Added validation for xptd_time_in and xptd_time_out
        $validated = $request->validate([
            'dtrs.*.employee_id'    => 'required|exists:employees,employee_id',
            'dtrs.*.date'           => 'required|date',
            'dtrs.*.time_in'        => 'nullable|date_format:H:i:s', // Changed to H:i:s as per your dd output
            'dtrs.*.time_out'       => 'nullable|date_format:H:i:s', // Changed to H:i:s
            'dtrs.*.shift_code'     => 'nullable|string|max:20',
            'dtrs.*.xptd_time_in'   => 'nullable|date_format:H:i:s', // Validation for expected times from form
            'dtrs.*.xptd_time_out'  => 'nullable|date_format:H:i:s', // Validation for expected times from form
        ]);

        $dtrs = $request->input('dtrs', []);

        foreach ($dtrs as $dtr) {
            $employeeId = $dtr['employee_id'];
            $date = Carbon::parse($dtr['date'])->toDateString();
            $shiftCode = $dtr['shift_code'] ?: null; // Use null for empty string/value

            // Use the actual time_in/out from the form. These might be null.
            $actualTimeIn = $dtr['time_in'] ?: null;
            $actualTimeOut = $dtr['time_out'] ?: null;

            // IMPORTANT: Directly use the xptd_time_in/out values sent from the form
            $expectedIn = $dtr['xptd_time_in'] ?: null;
            $expectedOut = $dtr['xptd_time_out'] ?: null;

            // Fallback: If for some reason the expected times are missing from the form,
            // (e.g., if you later change the form structure or manually trigger a submission),
            // you can still fetch them from the database based on the shift code.
            // Based on your dd output, this fallback should rarely be hit now.
            if ((!$expectedIn || !$expectedOut) && $shiftCode) {
                $scheduleData = DB::table('employee_schedules')
                    ->join('schedule', 'employee_schedules.shift_code', '=', 'schedule.shift_code')
                    ->where('employee_schedules.employee_id', $employeeId)
                    ->where('employee_schedules.date', $date)
                    ->select('schedule.xptd_time_in', 'schedule.xptd_time_out')
                    ->first();

                if ($scheduleData) {
                    $expectedIn = $scheduleData->xptd_time_in;
                    $expectedOut = $scheduleData->xptd_time_out;
                }
            }

            $isLate = false;
            $lateMinutes = 0;

            if ($actualTimeIn && $expectedIn) {
                // Ensure correct parsing format 'H:i:s' to match the data "08:00:00"
                $actualTimeInCarbon = Carbon::createFromFormat('H:i:s', $actualTimeIn);
                $expectedTimeInCarbon = Carbon::createFromFormat('H:i:s', $expectedIn);

                // Check if actual time is after expected time
                if ($actualTimeInCarbon->gt($expectedTimeInCarbon)) {
                    $isLate = true;
                    // Calculate difference in minutes
                    $lateMinutes = $actualTimeInCarbon->diffInMinutes($expectedTimeInCarbon);
                } else {
                    // If not late, ensure lateMinutes is 0
                    $isLate = false;
                    $lateMinutes = 0;
                }
            } else {
                // If either time is missing, they are not considered late for this calculation
                $isLate = false;
                $lateMinutes = 0;
            }

            DTR::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'transindate' => $date,
                ],
                [
                    'time_in'        => $actualTimeIn,
                    'transoutdate'   => $date, // Assuming transoutdate is same as transindate for DTR entries
                    'time_out'       => $actualTimeOut,
                    'shift_code'     => $shiftCode,
                    'xptd_time_in'   => $expectedIn,  // This will now be populated directly from form
                    'xptd_time_out'  => $expectedOut, // This will now be populated directly from form
                    'is_late'        => $isLate,
                    'late_minutes'   => $lateMinutes,
                    'updated_at'     => now(), // Always update updated_at
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
