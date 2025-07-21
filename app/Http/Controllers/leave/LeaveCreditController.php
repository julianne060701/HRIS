<?php
    namespace App\Http\Controllers\leave;
    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use App\Models\Employee;
    use App\Models\LeaveType;
    use App\Models\Leavecredit;


    class LeaveCreditController extends Controller
    {
        /**
         * Display a listing of the resource.
         */
        public function index()
        {
            $leaveCredits = LeaveCredit::with(['employee', 'leaveType'])->get();
            $employees = Employee::all();
            $leaveTypes = LeaveType::all();

            return view('HR.leave.leave_credit', compact('leaveCredits', 'employees', 'leaveTypes'));
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
            //  dd($request->all());
            // Validate the inputs
            $request->validate([
            'employee' => 'required|exists:employees,employee_id', // This validation will now correctly check against the 'id' column
            'vcleave' => 'required|numeric|min:0',
            'skleave' => 'required|numeric|min:0',  
            'bdleave' => 'required|numeric|min:0',
            'mtleave' => 'required|numeric|min:0',
            'ptleave' => 'required|numeric|min:0',
        ]);

            // Get the employee ID directly
            $employeePrimaryKey = $request->input('employee');

            // Get leave types
            $vacationLeaveType = LeaveType::where('name', 'Vacation Leave')->first();
            $sickLeaveType = LeaveType::where('name', 'Sick Leave')->first();
            $birthdayLeaveType = LeaveType::where('name', 'Birthday Leave')->first();
            $maternityLeaveType = LeaveType::where('name', 'Maternity Leave')->first();
            $paternityLeaveType = LeaveType::where('name', 'Paternity Leave')->first();

            // Ensure all leave types are present
            if (!$vacationLeaveType || !$sickLeaveType || !$birthdayLeaveType || !$maternityLeaveType || !$paternityLeaveType) {
                return redirect()->back()->withErrors('One or more leave types not found. Please configure them.');
            }

            // Create leave credits
            LeaveCredit::create([
                'employee_id' => $employeePrimaryKey,
                'leave_type_id' => $vacationLeaveType->id,
                'all_leave' => $request->input('vcleave'),
                'rem_leave' => $request->input('vcleave'),
            ]);

            LeaveCredit::create([
                'employee_id' => $employeePrimaryKey,
                'leave_type_id' => $sickLeaveType->id,
                'all_leave' => $request->input('skleave'),
                'rem_leave' => $request->input('skleave'),
            ]);

            LeaveCredit::create([
                'employee_id' => $employeePrimaryKey,
                'leave_type_id' => $birthdayLeaveType->id,
                'all_leave' => $request->input('bdleave'),
                'rem_leave' => $request->input('bdleave'),
            ]);

            LeaveCredit::create([
                'employee_id' => $employeePrimaryKey,
                'leave_type_id' => $maternityLeaveType->id,
                'all_leave' => $request->input('mtleave'),
                'rem_leave' => $request->input('mtleave'),
            ]);

            LeaveCredit::create([
                'employee_id' => $employeePrimaryKey,
                'leave_type_id' => $paternityLeaveType->id,
                'all_leave' => $request->input('ptleave'),
                'rem_leave' => $request->input('ptleave'),
            ]);

            return redirect()->route('leave_credit.index')->with('success', 'Leave credits added successfully!');
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
