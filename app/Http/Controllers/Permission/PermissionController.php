<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
           $employees = Employee::select('id', 'employee_id', 'first_name', 'middle_name', 'last_name', 'department')->get();

    return view('Permission.index', compact('employees'));
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
        'employee_id' => 'required|exists:employees,id',
        'modules' => 'nullable|array',
    ]);

    $employee = Employee::findOrFail($request->employee_id);

    // Check if user already exists
    $existingUser = User::where('emp_id', $employee->employee_id)->first();

    if (!$existingUser) {
        // Create new user
        $user = new User();
        $user->emp_id = $employee->employee_id;
        $user->name = $employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->last_name;
        $user->email = $employee->employee_id . '@gensanmed';
        $user->password = Hash::make('123456789');
        $user->remember_token = Str::random(10);
        $user->save();
    } else {
        $user = $existingUser;
    }

    // Store module permissions if needed (e.g., in a separate table)
    // Example logic: insert into user_module_permissions table
    if ($request->has('modules')) {
        DB::table('user_module_permissions')->where('user_id', $user->id)->delete(); // Clear existing

        foreach ($request->modules as $module) {
            DB::table('user_module_permissions')->insert([
                'user_id' => $user->id,
                'module_name' => $module,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    return redirect()->back()->with('success', 'Permission assigned and user account created successfully!');
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
