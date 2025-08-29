<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index()
    {
    $employees = Employee::all(); 
    $data = [];
    
    foreach ($employees as $employee) {

        $btnShow = '<button class="btn btn-xs btn-default text-info mx-1 shadow view-ticket" 
    title="View" data-id="' . $employee->id . '">
    <i class="fa fa-lg fa-fw fa-eye"></i>
</button>';


      
        $btnEdit = '<a href="' . route('HR.manage_employee.edit_employee', $employee->id) . '" class="btn btn-xs btn-default text-primary mx-1 shadow" title="Edit">
            <i class="fa fa-lg fa-fw fa-pen"></i>
        </a>';


        $btnDelete = '<button class="btn btn-xs btn-default text-danger mx-1 shadow Delete" id="deleteEmployeeID" title="Delete" data-delete="' . $employee->id . '" data-toggle="modal" data-target="#deleteModal">
            <i class="fa fa-lg fa-fw fa-trash"></i>
        </button>';


        $rowData = [
            $employee->employee_id,  
            $employee->first_name . ' ' . $employee->last_name,
            $employee->status,
            $employee->department,
            $employee->salary,
            $employee->created_at->format('F d, Y'),
            '<nobr>' .$btnShow . $btnEdit . $btnDelete . '</nobr>', 
        ];

        $data[] = $rowData;  
    }
    
    return view('HR.manage_employee.employee', compact('data'));
}

    public function create()
    {
        $departments = DB::table('departments')->get();
        return view('HR.manage_employee.create_employee', compact('departments'));
    }
      public function store(Request $request)
    {
        
        $request->validate([
            'employee_id' => 'required|unique:employees|alpha_num|max:10',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'birthday' => 'required|date',
            'contact_number' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'sss' => 'nullable|string|max:20',
            'philhealth' => 'nullable|string|max:20',
            'tin' => 'nullable|string|max:20',
            'pagibig' => 'nullable|string|max:20',
            'status' => 'required|in:Probationary,Regular,Resigned',
            'department' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
        ]);

        Employee::create($request->only([
            'employee_id', 'first_name', 'middle_name', 'last_name',
            'birthday', 'contact_number', 'address',
            'sss', 'philhealth', 'tin', 'pagibig',
            'status', 'department', 'salary',
        ]));

        return redirect()->route('HR.manage_employee.employee')->with('success', 'Employee created successfully!');
    }
public function show($id)
{
    $employee = Employee::findOrFail($id);  // Find employee by ID
    return response()->json($employee);  // Return employee data as JSON
}

public function edit($id)
{
    $employee = Employee::findOrFail($id);
    return view('HR.manage_employee.edit_employee', compact('employee'));

}

public function update(Request $request, $id)
{
    $request->validate([
        'first_name' => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'last_name' => 'required|string|max:255',
        'birthday' => 'required|date',
        'contact_number' => 'nullable|string|max:15',
        'address' => 'nullable|string',
        'sss' => 'nullable|string|max:20',
        'philhealth' => 'nullable|string|max:20',
        'tin' => 'nullable|string|max:20',
        'pagibig' => 'nullable|string|max:20',
        'status' => 'required|in:Probationary,Regular,Resigned',
        'department' => 'required|string|max:255',
        'salary' => 'required|numeric|min:0',
    ]);

    $employee = Employee::findOrFail($id);
    $employee->update($request->all());

    return redirect()->route('HR.manage_employee.employee')->with('success', 'Employee updated successfully!');

}

public function attendance()
{
    return view('HR.manage_employee.attendance');
}
}
