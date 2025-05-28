<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Payroll; // Add Payroll model
use App\Models\Earning; // Assuming you have an Earning model
use App\Models\PayslipDeduction;

class GenerateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::all();

        // Fetch all payrolls (adjust based on your data structure)
        $payrolls = Payroll::pluck('payroll_code'); // ✅ fetches only the payroll_code column


        // Assuming earnings and deductions are related to the payroll and employee
        $earnings = Earning::all(); // Fetch all earnings (you can adjust this based on your data structure)
        $deductions = PayslipDeduction::all();
 // Fetch all deductions

        // Pass all the necessary data to the view
        return view('HR.payroll.generate', compact('employees', 'payrolls', 'earnings', 'deductions'));
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
