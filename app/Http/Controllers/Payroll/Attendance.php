<?php

namespace App\Http\Controllers\payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Attendance extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('hr.attendance.importdtr');
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

    public function getAttendanceData()
    {
        $data = DB::connection('biometrics')
        ->table('attendance')
        ->select(
            'emp_id as employee_id',
            DB::raw("'' as name"), // Placeholder if no name field
            'transindate as transdate',
            DB::raw("CONCAT(transindate, ' ', time_in) as time_in_full"),
            DB::raw("CONCAT(transoutdate, ' ', time_out) as time_out_full")
        )
        ->get();

    return response()->json(['data' => $data]);
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
