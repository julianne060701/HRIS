<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HR\EmployeeController;
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

Auth::routes();

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