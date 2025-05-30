<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchedPost;
use App\Models\Schedule; 

class postschedulecontroller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('HR.attendance.postsched');
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
         // Save the start and end dates to sched_post table
    $schedulePost = new SchedPost();
    $schedulePost->start_date = $request->start_date;
    $schedulePost->end_date = $request->end_date;
    $schedulePost->save();

    // Save each employee's schedule
    $scheduleData = $request->input('schedule', []);

    foreach ($scheduleData as $employeeId => $dates) {
        foreach ($dates as $date => $shift) {
            if (!empty($shift)) {
                Schedule::updateOrCreate(
                    ['employee_id' => $employeeId, 'date' => $date],
                    ['shift' => $shift]
                );
            }
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Schedule successfully posted!',
        'data' => $schedulePost
    ]);
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
    public function getScheduleData(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $department = $request->query('department');
    
        $query = \App\Models\Employee::query();
    
        if ($department) {
            $query->whereRaw('LOWER(department) = ?', [strtolower($department)]);
        }
    
        $employees = $query->get();
    
        $result = [];
        foreach ($employees as $employee) {
            $result[] = [
                'id' => $employee->id, // ✅ Needed for the form input name
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'schedules' => $employee->getShiftsBetween($from, $to), // ✅ matches JS key
            ];
        }
    
        return response()->json($result);
    }
    
    
    

    public function getShifts()
    {
        $shifts = \App\Models\Schedule::select('shift')
            ->distinct()
            ->whereNotNull('shift')
            ->pluck('shift');
    
        return response()->json($shifts);
    }
    
    
}