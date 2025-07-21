<?php

namespace App\Http\Controllers\Overtime;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class OvertimeFilingController extends Controller
{
    public function index()
    {
        return view('HR.overtime.overtime_filing');
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|max:255',
            'ot_date' => 'required|date',
            'ot_in' => 'required|date_format:H:i',
            'ot_out' => 'required|date_format:H:i|after:ot_in',
        ]);

        $employeeId = $request->input('employee_id');
        $otDate = $request->input('ot_date');

        Log::info('Attempting to file overtime:', [
            'employee_id_input' => $employeeId,
            'ot_date_input' => $otDate,
            'ot_in_input' => $request->input('ot_in'),
            'ot_out_input' => $request->input('ot_out'),
        ]);

        $existingOvertime = Overtime::where('employee_id', $employeeId)
                                    ->where('ot_date', $otDate)
                                    ->first();

        if ($existingOvertime) {
            Log::warning('!!! DUPLICATE OVERTIME FOUND IN DB !!!', [
                'existing_id' => $existingOvertime->id,
                'existing_employee_id' => $existingOvertime->employee_id,
                'existing_ot_date' => $existingOvertime->ot_date,
                'existing_ot_in' => $existingOvertime->ot_in,
                'existing_ot_out' => $existingOvertime->ot_out,
            ]);

            // THIS IS THE ONLY LINE YOU NEED HERE IF YOU WANT TO THROW A VALIDATION EXCEPTION
            throw ValidationException::withMessages([
                'ot_date' => ['An overtime request for this employee on this date already exists.'],
            ]);

            // return redirect()->back()->withInput()->withErrors(['error' => 'An overtime request for this employee on ' . $otDate . ' already exists.']);
        } else {
            Log::info('No existing overtime found for this employee and date. Proceeding with creation.');
        }

        // Calculate total overtime hours
        $otInTime = $request->input('ot_in');
        $otOutTime = $request->input('ot_out');

        $otDateCarbon = Carbon::parse($otDate);

        $otInCarbon = Carbon::parse($otDateCarbon->format('Y-m-d') . ' ' . $otInTime);
        $otOutCarbon = Carbon::parse($otDateCarbon->format('Y-m-d') . ' ' . $otOutTime);

        if ($otOutCarbon->lt($otInCarbon)) {
            $otOutCarbon->addDay();
            Log::info('Adjusted ot_out to next day for calculation.', [
                'ot_in_carbon' => $otInCarbon->toDateTimeString(),
                'ot_out_carbon_adjusted' => $otOutCarbon->toDateTimeString(),
            ]);
        }

        $totalOtHours = $otOutCarbon->diffInHours($otInCarbon);
        Log::info('Calculated total overtime hours:', ['total_ot_hours' => $totalOtHours]);

        Overtime::create([
            'employee_id' => $employeeId,
            'ot_date' => $otDate,
            'ot_in' => $request->ot_in,
            'ot_out' => $request->ot_out,
            'total_ot_hours' => $totalOtHours,
            'is_approved' => $request->is_approved ?? 0,
            'approved_hours' => $request->apporoved_hours ?? 0,
        ]);

        return redirect()->back()->with('success', 'Overtime filed successfully!');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}