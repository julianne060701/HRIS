<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeSchedule;
use App\Models\DTR;
use App\Models\Shift;

class UploadDtrController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function importSchedulesToDtr()
{
    // Fetch all schedules with related shift info (assuming Shift model has 'shift_code', 'time_in', 'time_out')
    $schedules = EmployeeSchedule::with('shift')->get();

    foreach ($schedules as $schedule) {
        // Get related shift info, fallback to null if not found
        $shift = $schedule->shift;

        $dtrData = [
            'employee_id' => $schedule->employee_id,
            'transdate' => $schedule->date
        ];

        // Insert or update dtr record
        DTR::updateOrCreate(
            [
                'employee_id' => $schedule->employee_id,
                'transdate' => $schedule->date,
            ],
            $dtrData
        );
    }

    return response()->json(['message' => 'Schedules imported successfully to DTR.']);
}
}
