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
    $schedules = $request->input('schedule');

    if (!$schedules || !is_array($schedules)) {
        return response()->json(['message' => 'No schedules provided.'], 422);
    }

    foreach ($schedules as $employeeId => $dates) {
        foreach ($dates as $date => $shiftCode) {
            if ($shiftCode === '') continue; // Skip empty selections

            \App\Models\EmployeeSchedule::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'date' => $date
                ],
                [
                    'shift_code' => $shiftCode
                ]
            );
        }
    }

    return response()->json(['message' => 'Schedule posted successfully!']);
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
                'employee_id' => $employee->employee_id, // ✅ Needed for the form input name
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'schedules' => $employee->getShiftsBetween($from, $to), // ✅ matches JS key
            ];
        }
    
        return response()->json($result);
    }
    
    
    

    public function getShifts()
    {
        $shifts = \App\Models\Schedule::select('shift_code')
            ->distinct()
            ->whereNotNull('shift_code')
            ->pluck('shift_code');
    
        return response()->json($shifts);
    }
    
    
}