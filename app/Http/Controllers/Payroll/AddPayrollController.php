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
    
            $btnEdit = '<a href=" " 
                class="btn btn-xs btn-default text-primary mx-1 shadow ' . $isDisabled . '" 
                title="Edit">
                <i class="fa fa-lg fa-fw fa-pen"></i>
            </a>';
    
            $btnDelete = '<button class="btn btn-xs btn-default text-danger mx-1 shadow Delete" 
                title="Delete" data-toggle="modal" data-target="#deleteModalBed" 
                data-delete="' . $payroll->id . '">
                <i class="fa fa-lg fa-fw fa-trash"></i>
            </button>';
    
            $btnShow = '<button class="btn btn-xs btn-default text-info mx-1 shadow view-purchase" 
                title="View" data-id="' . $payroll->id . '" data-toggle="modal" data-target="#purchaseModal">
                <i class="fas fa-lg fa-fw fa-eye"></i>
            </button>';
    
            return [
                'id'           => $payroll->id,
                'payroll_code' => $payroll->payroll_code,
                'title'        => $payroll->title,
                'from_date'    => $payroll->from_date,
                'to_date'      => $payroll->to_date,
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
