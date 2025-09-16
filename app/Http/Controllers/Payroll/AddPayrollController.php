<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payroll;

class AddPayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Payroll::all();
    
        // Process the payroll data for display and actions (edit, delete, show)
        $payrollData = $data->map(function ($payroll) {
            $isDisabled = $payroll->status === 'Processed' ? 'disabled' : ''; // Example of how to disable buttons based on status
    
            $btnEdit = '<button class="btn btn-xs btn-default text-primary mx-1 shadow edit-payroll ' . $isDisabled . '" 
                title="Edit" data-id="' . $payroll->id . '">
                <i class="fa fa-lg fa-fw fa-pen"></i>
            </button>';
    
            $btnDelete = '<button class="btn btn-xs btn-default text-danger mx-1 shadow delete-payroll ' . $isDisabled . '" 
                title="Delete" data-id="' . $payroll->id . '">
                <i class="fa fa-lg fa-fw fa-trash"></i>
            </button>';
    
            $btnShow = '<button class="btn btn-xs btn-default text-info mx-1 shadow view-payroll" 
                title="View" data-id="' . $payroll->id . '">
                <i class="fas fa-lg fa-fw fa-eye"></i>
            </button>';
    
            return [
                'id'           => $payroll->id,
                'payroll_code' => $payroll->payroll_code,
                'title'        => $payroll->title,
                'from_date'    => $payroll->from_date,
                'to_date'      => $payroll->to_date,
                'status'       => $payroll->status,
                'actions'      => '<nobr>' . $btnShow . $btnDelete . $btnEdit . '</nobr>',
            ];
        });
    
        return view('HR.payroll.add_payroll', compact('payrollData'));
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
        $request->validate([
            'payroll_code'  => 'nullable|string|max:50',
            'title'         => 'nullable|string|max:100',
            'from_date'     => 'required|date',
            'to_date'       => 'required|date',
            'status'        => 'required|string|max:20',
        ]);

        Payroll::create([
            'payroll_code'  => $request->payroll_code,
            'title'         => $request->title,
            'from_date'     => $request->from_date,
            'to_date'       => $request->to_date,
            'status'        => $request->status,
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
        try {
            $payroll = Payroll::findOrFail($id);
            
            // Format dates for HTML date inputs (Y-m-d format)
            return response()->json([
                'id' => $payroll->id,
                'payroll_code' => $payroll->payroll_code,
                'title' => $payroll->title,
                'from_date' => $payroll->from_date ? $payroll->from_date->format('Y-m-d') : null,
                'to_date' => $payroll->to_date ? $payroll->to_date->format('Y-m-d') : null,
                'status' => $payroll->status,
                'created_at' => $payroll->created_at,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Payroll not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Debug: Log the incoming request
            \Log::info('Update request received', [
                'id' => $id,
                'data' => $request->all()
            ]);

            $request->validate([
                'payroll_code'  => 'nullable|string|max:50',
                'title'         => 'nullable|string|max:100',
                'from_date'     => 'required|date',
                'to_date'       => 'required|date',
                'status'        => 'required|string|max:20',
            ]);

            $payroll = Payroll::findOrFail($id);
            $payroll->update([
                'payroll_code'  => $request->payroll_code,
                'title'         => $request->title,
                'from_date'     => $request->from_date,
                'to_date'       => $request->to_date,
                'status'        => $request->status,
            ]);

            \Log::info('Payroll updated successfully', ['id' => $id]);
            return response()->json(['message' => 'Payroll updated successfully']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in update', ['errors' => $e->errors()]);
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating payroll', ['error' => $e->getMessage(), 'id' => $id]);
            return response()->json(['error' => 'Failed to update payroll: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            \Log::info('Delete request received', ['id' => $id]);
            
            $payroll = Payroll::findOrFail($id);
            \Log::info('Payroll found', ['payroll' => $payroll->toArray()]);
            
            // Check if there are any related records that might prevent deletion
            $relatedPayrollData = \DB::table('payrolldata')->where('payroll_id', $id)->count();
            \Log::info('Related payroll data count', ['count' => $relatedPayrollData]);
            
            $payroll->delete();

            \Log::info('Payroll deleted successfully', ['id' => $id]);
            return response()->json(['message' => 'Payroll deleted successfully']);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error deleting payroll', [
                'error' => $e->getMessage(), 
                'id' => $id,
                'sql_state' => $e->getSqlState(),
                'error_code' => $e->getCode()
            ]);
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            \Log::error('Error deleting payroll', ['error' => $e->getMessage(), 'id' => $id]);
            return response()->json(['error' => 'Failed to delete payroll: ' . $e->getMessage()], 500);
        }
    }
    public function getCurrentCutoff()
{
    $latestPayroll = Payroll::latest('created_at')->first(); // or use ->orderBy('id', 'desc')

    if (!$latestPayroll) {
        return response()->json(['error' => 'No payroll data found'], 404);
    }

    return response()->json([
        'start_date' => $latestPayroll->from_date,
        'end_date'   => $latestPayroll->to_date,
    ]);
}

    /**
     * Test method to check if delete functionality works
     */
    public function testDelete(string $id)
    {
        try {
            $payroll = Payroll::findOrFail($id);
            return response()->json([
                'message' => 'Payroll found and can be deleted',
                'payroll' => $payroll->toArray(),
                'related_data_count' => \DB::table('payrolldata')->where('payroll_id', $id)->count()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



}
