<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchedPost;

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
        $schedule = new schedPost();
        $schedule->start_date = $request->start_date;
        $schedule->end_date = $request->end_date;
        $schedule->save();

    return response()->json(['success' => true, 'data' => $schedule]);
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

    $schedules = schedPost::whereBetween('start_date', [$from, $to])->get();

    // You can customize this mapping
    return response()->json($schedules->map(function ($sched) {
        return [
            'name' => 'Sample Name', // Replace with actual logic
            'start_date' => $sched->start_date,
            'end_date' => $sched->end_date,
        ];
    }));
}

}
