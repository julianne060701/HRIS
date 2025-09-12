@extends('adminlte::page')

@section('title', 'Manual Attendance')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)
@section('plugins.Sweetalert2', true)

<link rel="icon" type="image/x-icon" href="{{ asset('LOGO.ico') }}">
@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content_header')
    <h1 class="ml-1">Manual Attendance</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addAttendanceModal">
                <i class="fas fa-plus"></i> Add Manual Attendance
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Manual Attendance Records</h3>
                </div>
                <div class="card-body">
                    <table id="attendanceTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Employee ID</th>
                                <th>Date In</th>
                                <th>Time In</th>
                                <th>Date Out</th>
                                <th>Time Out</th>
                                <th>Total Hours</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($attendances))
                                @foreach($attendances as $attendance)
                                    <tr>
                                        <td>{{ $attendance->id }}</td>
                                        <td>{{ $attendance->employee_id }}</td>
                                        <td>{{ date('M d, Y', strtotime($attendance->transindate)) }}</td>
                                        <td>{{ date('h:i A', strtotime($attendance->time_in)) }}</td>
                                        <td>
                                            @if($attendance->transoutdate)
                                                {{ date('M d, Y', strtotime($attendance->transoutdate)) }}
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attendance->time_out)
                                                {{ date('h:i A', strtotime($attendance->time_out)) }}
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attendance->total_hours)
                                                {{ number_format($attendance->total_hours, 2) }} hrs
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-danger delete-attendance-btn" 
                                                        data-id="{{ $attendance->id }}"
                                                        data-employee="{{ $attendance->employee_id }}"
                                                        data-date="{{ date('M d, Y', strtotime($attendance->transindate)) }}">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="delete-form" action="" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<div class="modal fade" id="addAttendanceModal" tabindex="-1" role="dialog" aria-labelledby="addAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="attendanceForm" action="{{ route('HR.attendance.manualattendance.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="addAttendanceModalLabel">
                        <i class="fas fa-clock"></i> Add Manual Attendance
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label>Employee <span class="text-danger">*</span></label>
                            <select class="form-control @error('employee_id') is-invalid @enderror" 
                                    name="employee_id" 
                                    id="employee_id" 
                                    required>
                                <option value="">Please Select Here</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->employee_id ?? $employee->id }}" 
                                            {{ old('employee_id') == ($employee->employee_id ?? $employee->id) ? 'selected' : '' }}>
                                        {{ $employee->employee_id ?? $employee->id }} - {{ $employee->first_name }} {{ $employee->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="transindate">Date In <span class="text-danger">*</span></label>
                                <input type="date" 
                                        class="form-control @error('transindate') is-invalid @enderror" 
                                        id="transindate" 
                                        name="transindate" 
                                        value="{{ old('transindate', date('Y-m-d')) }}"
                                        required>
                                @error('transindate')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="time_in">Time In <span class="text-danger">*</span></label>
                                <input type="time" 
                                        class="form-control @error('time_in') is-invalid @enderror" 
                                        id="time_in" 
                                        name="time_in" 
                                        value="{{ old('time_in') }}"
                                        required>
                                @error('time_in')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="transoutdate">Date Out</label>
                                <input type="date" 
                                        class="form-control @error('transoutdate') is-invalid @enderror" 
                                        id="transoutdate" 
                                        name="transoutdate" 
                                        value="{{ old('transoutdate') }}">
                                @error('transoutdate')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="time_out">Time Out</label>
                                <input type="time" 
                                        class="form-control @error('time_out') is-invalid @enderror" 
                                        id="time_out" 
                                        name="time_out" 
                                        value="{{ old('time_out') }}">
                                @error('time_out')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="calculated_hours">Calculated Hours</label>
                                <input type="text" 
                                        class="form-control" 
                                        id="calculated_hours" 
                                        readonly
                                        placeholder="Hours will be calculated automatically">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="reset" class="btn btn-warning">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                    <button type="button" class="btn btn-primary" id="save-attendance-btn">
                        <i class="fas fa-save"></i> Save Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTable
        const attendanceDt = $('#attendanceTable').DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: true,
            order: [[ 0, 'desc' ]],
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            pageLength: 10,
            buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"]
        });
        // Append DataTable buttons only if the Buttons extension is available
        if ($.fn.dataTable && $.fn.dataTable.Buttons && attendanceDt.buttons) {
            attendanceDt.buttons().container().appendTo('#attendanceTable_wrapper .col-md-6:eq(0)');
        }

        // Auto-set date out to same as date in when date in changes
        $('#transindate').on('change', function() {
            if (!$('#transoutdate').val()) {
                $('#transoutdate').val($(this).val());
            }
            calculateTotalHours();
        });

        // Calculate total hours when any time field changes
        $('#time_out, #time_in, #transoutdate').on('change', function() {
            calculateTotalHours();
        });

        // Calculate total hours with break deduction logic
        function calculateTotalHours() {
            const timeIn = $('#time_in').val();
            const timeOut = $('#time_out').val();
            const dateIn = $('#transindate').val();
            const dateOut = $('#transoutdate').val() || dateIn;

            if (timeIn && timeOut && dateIn) {
                let startDateTime = new Date(dateIn + 'T' + timeIn);
                let endDateTime = new Date(dateOut + 'T' + timeOut);
                
                // Handle overnight shifts - if same date and end time is before start time
                if (dateIn === dateOut && endDateTime < startDateTime) {
                    endDateTime.setDate(endDateTime.getDate() + 1);
                }
                
                // Check for valid time range after adjustments
                if (endDateTime <= startDateTime) {
                    $('#calculated_hours').val('Invalid time range - Please check your entries');
                    $('#time_out').addClass('is-invalid');
                    return;
                } else {
                    $('#time_out').removeClass('is-invalid');
                    // Remove any existing feedback to prevent duplication
                    $('#time_out').siblings('.invalid-feedback').remove(); 
                }

                const diffMs = endDateTime - startDateTime;
                const rawHours = diffMs / (1000 * 60 * 60);
                
                // Apply break deduction logic: if 8 hours or more, deduct 1 hour for break
                let finalHours = rawHours;
                let breakDeducted = false;
                
                if (rawHours >= 8.0) {
                    finalHours = rawHours - 1.0; // Deduct 1 hour for break
                    breakDeducted = true;
                }

                if (finalHours > 0) {
                    let displayText = finalHours.toFixed(2) + ' hours';
                    if (breakDeducted) {
                        displayText += ' (1hr break deducted)';
                    }
                    $('#calculated_hours').val(displayText);
                } else {
                    $('#calculated_hours').val('Invalid time range');
                    $('#time_out').addClass('is-invalid');
                }
            } else {
                $('#calculated_hours').val('');
                $('#time_out').removeClass('is-invalid');
            }
        }

        // Show modal if there are validation errors
        @if($errors->any())
            $('#addAttendanceModal').modal('show');
        @endif

        // Reset form when modal is closed
        $('#addAttendanceModal').on('hidden.bs.modal', function () {
            $('#attendanceForm')[0].reset();
            $('#calculated_hours').val('');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
        });

        // Set current time when modal opens
        $('#addAttendanceModal').on('shown.bs.modal', function () {
            if (!$('#time_in').val()) {
                const now = new Date();
                const currentTime = now.getHours().toString().padStart(2, '0') + ':' + 
                                    now.getMinutes().toString().padStart(2, '0');
                $('#time_in').val(currentTime);
            }
            
            // Focus on employee field
            $('#employee_id').focus();
        });

        // Reset button functionality within modal
        $('#addAttendanceModal button[type="reset"]').on('click', function() {
            $('#calculated_hours').val('');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
        });

        // FIXED: SweetAlert for Save Attendance Button
        $('#save-attendance-btn').on('click', function(e) {
            e.preventDefault();
            
            const form = $('#attendanceForm')[0];
            const timeIn = $('#time_in').val();
            const timeOut = $('#time_out').val();
            const calculatedHours = $('#calculated_hours').val();
            const employeeId = $('#employee_id').val();
            const employeeName = $('#employee_id option:selected').text();
            const dateIn = $('#transindate').val();
            
            // Perform form validation
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Get employee name for confirmation
            const employeeDisplayName = employeeName.split(' - ')[1] || employeeName;
            
            // Format date for display
            const formattedDate = new Date(dateIn).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            // Format times for display
            const formatTime = (timeStr) => {
                const [hours, minutes] = timeStr.split(':');
                const hour12 = parseInt(hours) % 12 || 12;
                const ampm = parseInt(hours) >= 12 ? 'PM' : 'AM';
                return `${hour12}:${minutes} ${ampm}`;
            };

            // Build confirmation HTML
            let confirmationHtml = `
                <div class="text-left" style="font-size: 14px;">
                    <div class="mb-2"><strong>Employee:</strong> ${employeeDisplayName}</div>
                    <div class="mb-2"><strong>Date:</strong> ${formattedDate}</div>
                    <div class="mb-2"><strong>Time In:</strong> ${formatTime(timeIn)}</div>
            `;
            
            if (timeOut) {
                confirmationHtml += `<div class="mb-2"><strong>Time Out:</strong> ${formatTime(timeOut)}</div>`;
            }
            
            if (calculatedHours && calculatedHours !== '' && !calculatedHours.includes('Invalid')) {
                confirmationHtml += `<div class="mb-2"><strong>Total Hours:</strong> ${calculatedHours}</div>`;
            }
            
            confirmationHtml += '</div>';

            // SweetAlert confirmation with proper form submission
            Swal.fire({
                title: 'Save Attendance Record?',
                html: confirmationHtml,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-save"></i> Yes, Save Attendance',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    // Manually submit the form to trigger Laravel's normal request/response cycle
                    return new Promise((resolve, reject) => {
                        form.submit();
                    });
                }
            });
        });

        // FIXED: SweetAlert for Delete Button
        $(document).on('click', '.delete-attendance-btn', function(e) {
            e.preventDefault();
            
            const attendanceId = $(this).data('id');
            const employeeId = $(this).data('employee');
            const attendanceDate = $(this).data('date');
            
            Swal.fire({
                title: 'Delete Attendance Record?',
                html: `
                    <div class="text-left" style="font-size: 14px;">
                        <div class="mb-2"><strong>Employee ID:</strong> ${employeeId}</div>
                        <div class="mb-3"><strong>Date:</strong> ${attendanceDate}</div>
                        <div class="alert alert-warning" style="font-size: 13px;">
                            <i class="fas fa-exclamation-triangle"></i> 
                            This action cannot be undone!
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash"></i> Yes, Delete It!',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve, reject) => {
                        Swal.showLoading();
                        const deleteUrl = '{{ route("HR.attendance.manualattendance.destroy", "__ID__") }}'.replace('__ID__', attendanceId);
                        $('#delete-form').attr('action', deleteUrl).submit();
                        resolve();
                    });
                }
            });
        });

        // FIXED: Show success message with SweetAlert
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ addslashes(session("success")) }}',
                confirmButtonColor: '#28a745',
                timer: 4000,
                timerProgressBar: true,
                allowOutsideClick: true
            });
        @endif

        // FIXED: Show error messages with SweetAlert if there are errors
        @if($errors->any())
            const errors = [
                @foreach($errors->all() as $error)
                    '{{ addslashes($error) }}',
                @endforeach
            ];
            
            let errorHtml = '<ul class="text-left" style="font-size: 14px; list-style-type: none; padding-left: 0;">';
            errors.forEach(error => {
                errorHtml += `<li class="mb-1"><i class="fas fa-exclamation-circle text-danger mr-2"></i>${error}</li>`;
            });
            errorHtml += '</ul>';
            
            Swal.fire({
                icon: 'error',
                title: 'Validation Errors',
                html: errorHtml,
                confirmButtonColor: '#d33',
                allowOutsideClick: false,
                backdrop: `rgba(0,0,0,0.4)`
            });
        @endif
    });
</script>
@endsection