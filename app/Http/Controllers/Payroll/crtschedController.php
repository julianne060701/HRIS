<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Schedule;

class crtschedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schedules = Schedule::all(); // get all schedules from DB
    return view('HR.attendance.create_sched', compact('schedules'));
            
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
       //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
    'shift_code'      => 'required|string|max:50',
    'shiftdesc'      => 'nullable|string|max:100',
    'shifttime_in'   => 'required|date_format:H:i',
    'shifttime_out'  => 'required|date_format:H:i',
    'break_in'       => 'nullable|date_format:H:i',
    'break_out'      => 'nullable|date_format:H:i',
    'totalhours'     => 'required|integer|max:20',
    'status'         => 'required|string|max:20',
]);

        schedule::create([
            'shift_code'=>          $request->shift_code,
            'desc'=>           $request->shiftdesc,
            'xptd_time_in'=>        $request->shifttime_in,
            'xptd_time_out'=>       $request->shifttime_out,
            'xptd_brk_in'=>    $request->break_in,
            'xptd_brk_out'=>   $request->break_out,
            'wrkhrs'=>         $request->totalhours,
            'stat'=>         $request->status,
        ]);

        return redirect()->back()->with('success', 'Payroll added successfully!');
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
