<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\Payroll\AddPayrollController;
use App\Http\Controllers\Payroll\postschedulecontroller;
use App\Http\Controllers\Payroll\Attendance;



Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// HR Routes

Route::get('/HR/manage_employee/employee', [App\Http\Controllers\HR\EmployeeController::class, 'index'])->name('HR.manage_employee.employee');
Route::get('/HR/manage_employee/create_employee', [App\Http\Controllers\HR\EmployeeController::class, 'create'])->name('HR.manage_employee.create_employee');
Route::post('/HR/manage_employee/create_employee', [App\Http\Controllers\HR\EmployeeController::class, 'store'])->name('HR.manage_employee.store_employee');
Route::get('/hr/employee/{id}/edit', [EmployeeController::class, 'edit'])->name('HR.manage_employee.edit_employee');
Route::put('/hr/employee/{id}', [EmployeeController::class, 'update'])->name('HR.update_employee');
Route::get('/employee/{id}', [EmployeeController::class, 'show']);
Route::get('/HR/delete_employee/{id}', [EmployeeController::class, 'delete'])->name('HR.delete_employee');

Route::get('/HR/manage_employee/attendance', [App\Http\Controllers\HR\EmployeeController::class, 'attendance'])->name('HR.manage_employee.attendance');



// Payroll Routes
Route::get('/HR/payroll/generate', [App\Http\Controllers\Payroll\GenerateController::class, 'index'])->name('HR.payroll.generate');
Route::get('/HR/payroll/add_payroll', [App\Http\Controllers\Payroll\AddPayrollController::class, 'index'])->name('HR.payroll.add_payroll');
Route::post('/HR/payroll/store', [App\Http\Controllers\Payroll\AddPayrollController::class, 'store'])->name('add-payroll.store');

// Route::prefix('hr/manage_employee')->name('hr.manage_employee.')->group(function () {
//     Route::get('/HR/manage_employee/employee', [App\Http\Controllers\HR\EmployeeController::class, 'index'])->name('HR.manage_employee.employee');
//     Route::get('/HR/manage_employee/create_employee', [App\Http\Controllers\HR\EmployeeController::class, 'create'])->name('HR.manage_employee.create_employee');
//     Route::post('/store', [EmployeeController::class, 'store'])->name('store_employee');
//     Route::get('/employee/{id}', [EmployeeController::class, 'show']);
//     Route::get('/employee/{id}/edit', [EmployeeController::class, 'edit'])->name('edit_employee');
//     Route::put('/employee/{id}', [EmployeeController::class, 'update'])->name('update_employee');
// });

// Attendance Routes.
Route::get('HR/attendance/importdtr', [App\Http\Controllers\Payroll\Attendance::class, 'index'])->name('HR.attendance.importdtr');


//route of posting schedule
Route::get('/attendance/data', [Attendance::class, 'getAttendanceData'])->name('attendance.data');
// Route::get('/attendance/data', [App\Http\Controllers\Payroll\Attendance::class,'getAttendanceData']);
Route::get('/HR/attendance/postsched', [App\Http\Controllers\Payroll\postschedulecontroller::class,'index'])->name('HR.attendance.postsched');
Route::post('/schedule/store', [App\Http\Controllers\Payroll\postschedulecontroller::class,'store'])->name('schedule.store');
Route::get('/cutoff-dates', [App\Http\Controllers\Payroll\AddPayrollController::class, 'getCurrentCutoff'])->name('cutoff.dates');
Route::get('/schedule/data', [App\Http\Controllers\Payroll\postschedulecontroller::class, 'getScheduleData']);


// Holiday Routes
Route::get('HR/holidays/index', [App\Http\Controllers\Holiday\HolidayController::class, 'index'])->name('HR.holidays.index');
Route::post('HR/holidays/store', [App\Http\Controllers\Holiday\HolidayController::class, 'store'])->name('holidays.store');

Route::get('HR/attendance/create_sched', [App\Http\Controllers\Payroll\crtschedController::class, 'index'])->name('HR.attendance.create_sched');
Route::post('/schedule/store', [App\Http\Controllers\Payroll\crtschedController::class,'store'])->name('schedule.store');
Route::post('/schedule/post', [ScheduleController::class, 'post'])->name('schedule.post');
Route::get('/shifts', [\App\Http\Controllers\Payroll\postschedulecontroller::class, 'getShifts'])->name('schedule.get');
// In routes/web.php
// Route::get('/shifts', [PostScheduleController::class, 'getShifts'])->name('schedule.shifts');

Route::post('/schedule/post', [postschedulecontroller::class, 'store'])->name('schedule.post');
Route::get('/schedule/get', [postschedulecontroller::class, 'getShifts'])->name('schedule.get');
Route::get('/schedule/data', [postschedulecontroller::class, 'getScheduleData'])->name('schedule.data');


//importing attendance to DTR
// Route::post('attendance/store', [Attendance::class, 'storeAttendanceData'])->name('attendance.store');
Route::post('/attendance/store', [Attendance::class, 'store'])->name('attendance.store');
Route::get('/attendance/store', function () {
    logger('GET request made to /attendance/store');
    return response()->json(['error' => 'Use POST only'], 405);
});
Route::post('/attendance/import', [Attendance::class, 'importFromAttendance'])->name('attendance.import');


// Process DTR Routes
Route::get('/HR/attendance/processdtr', [App\Http\Controllers\Payroll\ProcessDTRController::class, 'index'])->name('HR.attendance.processdtr');