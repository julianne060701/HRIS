<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $data = []; // Replace this with your actual data fetching logic
    return view('hr.employee', compact('data'));
    }
}
