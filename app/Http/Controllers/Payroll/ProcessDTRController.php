<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\DTR;
use App\Models\Employee;

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
        DB::raw("CONCAT(employees.first_name, ' ', employees.last_name) AS employee_name"),
        'employee_schedules.date',
        'schedule.xptd_time_in AS plotted_time_in',
        'schedule.xptd_time_out AS plotted_time_out',
        'attendance.time_in AS actual_time_in',
        'attendance.time_out AS actual_time_out'
    )
    ->join('employees', 'employee_schedules.employee_id', '=', 'employees.id')
    ->leftJoin('schedule', 'employee_schedules.shift_code', '=', 'schedule.shift')
    ->leftJoin('attendance', function ($join) {
        $join->on('employee_schedules.employee_id', '=', 'attendance.employee_id')
             ->whereRaw('DATE(attendance.transindate) = employee_schedules.date');
    })
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
