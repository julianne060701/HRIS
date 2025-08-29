<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function index()
    {
        // You should fetch department data here if needed
        $data = DB::table('departments')
                ->select('id', 'name')
                ->get()
                ->map(function ($item) {
                    return [
                        $item->id,
                        $item->name,
                        '<a href="#" class="btn btn-sm btn-warning">Edit</a> <a href="#" class="btn btn-sm btn-danger">Delete</a>'
                    ];
                });

        return view('HR.manage_employee.department', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_name' => 'required|string|max:255',
        ]);

        DB::table('departments')->insert([
            'name' => $request->department_name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Department added successfully!');
    }
}
