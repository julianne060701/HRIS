    <?php

    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\ProfileController;
    use App\Http\Controllers\HR\EmployeeController;
    use App\Http\Controllers\Payroll\AddPayrollController;
    use App\Http\Controllers\Payroll\postschedulecontroller;
    use App\Http\Controllers\Payroll\crtschedController;
    use App\Http\Controllers\Payroll\Attendance;
    use App\Http\Controllers\Payroll\GenerateController;
    use App\Http\Controllers\Payroll\ProcessDTRController;
    use App\Http\Controllers\Holiday\HolidayController;
    use App\Http\Controllers\Permission\PermissionController;
    use App\Http\Controllers\HomeController;
    use App\Http\Controllers\Overtime\OvertimeFilingController;
    use App\Http\Controllers\Overtime\ManageOvertimeController;
    use App\Http\Controllers\leave\LeaveFilingController;
    use App\Http\Controllers\Leave\LeaveManagementController;
    use App\Http\Controllers\Leave\LeaveCreditController;
    use App\Http\Controllers\Payroll\ProcessPayrollController;







    // Public route
    Route::get('/', function () {
        return view('welcome');
    });

    // Auth-protected routes
    Route::middleware(['auth'])->group(function () {

        // Dashboard
        Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Home (redirect after login)
        Route::get('/home', [HomeController::class, 'index'])->name('home');

        // Permission
        Route::get('/Permission/index', [PermissionController::class, 'index'])->name('Permission.index');
        Route::post('/Permission/store', [PermissionController::class, 'store'])->name('Permission.store');

        /**
         * =====================
         * HR Routes
         * =====================
         */
        Route::prefix('HR/manage_employee')->name('HR.manage_employee.')->group(function () {
            Route::get('/employee', [EmployeeController::class, 'index'])->name('employee');
            Route::get('/create_employee', [EmployeeController::class, 'create'])->name('create_employee');
            Route::post('/create_employee', [EmployeeController::class, 'store'])->name('store_employee');
        });

        Route::get('/hr/employee/{id}/edit', [EmployeeController::class, 'edit'])->name('HR.manage_employee.edit_employee');
        Route::put('/hr/employee/{id}', [EmployeeController::class, 'update'])->name('HR.update_employee');
        Route::get('/employee/{id}', [EmployeeController::class, 'show']);
        Route::get('/HR/delete_employee/{id}', [EmployeeController::class, 'delete'])->name('HR.delete_employee');

        Route::get('/HR/manage_employee/attendance', [EmployeeController::class, 'attendance'])->name('HR.manage_employee.attendance');

        /**
         * =====================
         * Payroll Routes
         * =====================
         */
        Route::prefix('HR/payroll')->name('HR.payroll.')->group(function () {
            Route::get('/generate', [GenerateController::class, 'index'])->name('generate');
            Route::get('/add_payroll', [AddPayrollController::class, 'index'])->name('add_payroll');
        });

        Route::post('/HR/payroll/store', [AddPayrollController::class, 'store'])->name('add-payroll.store');
        Route::get('/cutoff-dates', [AddPayrollController::class, 'getCurrentCutoff'])->name('cutoff.dates');

        /**
         * =====================
         * Attendance Routes
         * =====================
         */
        Route::get('HR/attendance/importdtr', [Attendance::class, 'index'])->name('HR.attendance.importdtr');
        Route::get('/attendance/data', [Attendance::class, 'getAttendanceData'])->name('attendance.data');
        Route::post('/attendance/store', [Attendance::class, 'store'])->name('attendance.store');
        Route::post('/attendance/import', [Attendance::class, 'importFromAttendance'])->name('attendance.import');

        // Prevent misuse of GET for POST route
        Route::get('/attendance/store', fn () => response()->json(['error' => 'Use POST only'], 405));

        /**
         * =====================
         * Schedule Posting & Creation
         * =====================
         */
        Route::get('/HR/attendance/postsched', [postschedulecontroller::class, 'index'])->name('HR.attendance.postsched');
        Route::post('/schedule/store', [crtschedController::class, 'store'])->name('schedule.store'); // crt_sched route
        Route::post('/schedule/post', [postschedulecontroller::class, 'store'])->name('schedule.post');
        Route::get('/schedule/data', [postschedulecontroller::class, 'getScheduleData'])->name('schedule.data');
        Route::get('/schedule/get', [postschedulecontroller::class, 'getShifts'])->name('schedule.get');
        Route::get('/shifts', [postschedulecontroller::class, 'getShifts'])->name('schedule.shifts'); // Optional alias

        Route::get('HR/attendance/create_sched', [crtschedController::class, 'index'])->name('HR.attendance.create_sched');

        /**
         * =====================
         * Holiday Routes
         * =====================
         */
        Route::get('HR/holidays/index', [HolidayController::class, 'index'])->name('HR.holidays.index');
        Route::post('HR/holidays/store', [HolidayController::class, 'store'])->name('holidays.store');

        //import DTR 
        Route::get('/attendance/import', function () {
        return response()->json(['error' => 'This endpoint only accepts POST requests.'], 405);
    });




        /**
         * =====================
         * Process DTR Routes
         * =====================
         * 
         */
        Route::get('/HR/attendance/processdtr', [ProcessDTRController::class, 'index'])->name('HR.attendance.processdtr');
        Route::get('/attendance/processdata', [ProcessDTRController::class, 'getProcessedDTR'])->name('attendance.processdata');
        Route::post('/payroll/processdtr', [ProcessDTRController::class, 'store'])->name('processdtr.store');
       Route::resource('payroll/process-dtr', ProcessDTRController::class)->names([
    'index' => 'payroll.process-dtr.index',
    'store' => 'payroll.process-dtr.store',]); });

    //Overtime
    Route::get('/HR/overtime/overtime_filing', [OvertimeFilingController::class, 'index'])->name('HR.overtime.overtime_filing'); 
    Route::post('HR/overtime/store', [OvertimeFilingController::class, 'store'])->name('overtime.store');
    Route::get('/HR/overtime/manage_overtime', [ManageOvertimeController::class, 'index'])->name('HR.overtime.manage_overtime'); 
    Route::prefix('overtime')->group(function () {
        Route::get('/data', [ManageOvertimeController::class, 'data'])->name('overtime.data');
        Route::post('/approve/{id}', [ManageOvertimeController::class, 'approve'])->name('overtime.approve');
        Route::post('/disapprove/{id}', [ManageOvertimeController::class, 'disapprove'])->name('overtime.disapprove');
        Route::post('/update/{id}', [ManageOvertimeController::class, 'update'])->name('overtime.update');
    });


    //leave
    Route::get('/HR/leave/leave_filing', [LeaveFilingController::class, 'index'])->name('HR.leave.leave_filing');
    Route::get('/leave/file', [LeaveFilingController::class, 'create'])->name('leave.create');
    Route::post('/leave', [LeaveFilingController::class, 'store'])->name('leave.store');

    //leave management
    Route::prefix('HR/leave')->name('leavemgt.')->group(function () {
            Route::get('/leave_manage', [LeaveManagementController::class, 'index'])->name('index');
            Route::get('/data', [LeaveManagementController::class, 'data'])->name('data');
            Route::post('/approve/{id}', [LeaveManagementController::class, 'approve'])->name('approve');
            Route::post('/disapprove/{id}', [LeaveManagementController::class, 'disapprove'])->name('disapprove');
            Route::post('/update/{id}', [LeaveManagementController::class, 'update'])->name('update');
            // Route::post('/store', [LeaveManagementController::class, 'store'])->name('store');
        });
    Route::get('/HR/leave/types', [LeaveManagementController::class, 'getLeaveTypes'])->name('leave.types');
    Route::get('/api/leave-credits/{employee_id}', [LeaveFilingController::class, 'getLeaveCredits']);
    
    //leavecredit
    Route::prefix('HR/leave')->name('leave_credit.')->group(function () {
    Route::get('/leave_credit', [LeaveCreditController::class, 'index'])->name('index');
    Route::post('/store', [LeaveCreditController::class, 'store'])->name('store');
    });
    // Route::get('/api/employees/search', [EmployeeSearchController::class, 'search'])->name('api.employees.search');
    // Route::get('/api/leave_types/search', [LeaveTypeSearchController::class, 'search'])->name('api.leave_types.search');

    // Payroll Processing Routes
    Route::get('payroll/process', [ProcessPayrollController::class, 'index'])->name('HR.payroll.process');
    Route::get('payroll/process/{id}', [ProcessPayrollController::class, 'show'])->name('HR.payroll.show'); // Assuming you'll use the 'show' method for processing a specific ID
    Route::post('payroll/save/{payroll}', [ProcessPayrollController::class, 'savePayroll'])->name('HR.payroll.save');
    
    
    // The API routes you provided are separate and for AJAX calls
    Route::get('/payroll-date-ranges', [ProcessPayrollController::class, 'fetchPayrollDateRanges']);
    Route::get('/payroll-date-ranges/{id}', [ProcessPayrollController::class, 'fetchSpecificPayrollDateRange']);



    // Authentication routes (login, register, etc.)
    require __DIR__.'/auth.php';
