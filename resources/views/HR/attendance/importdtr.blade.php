<!-- my importdtr.blade.php -->
@extends('adminlte::page')

@section('title', 'Employee Attendance')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

@section('content_header')
<h1 class="ml-1">IMPORT DTR</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            @if(isset($activePayroll))
            Payroll Period: {{ $activePayroll->title }} ({{ $activePayroll->payroll_code }})
            <input type="hidden" id="payrollSelect" value="{{ $activePayroll->id }}">
            @else
            Current Month Period
            @endif
        </h3>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label for="minDate">From:</label>
            <input type="date" id="minDate" class="form-control d-inline w-auto mx-2" value="{{ $startDate }}">
            <label for="maxDate">To:</label>
            <input type="date" id="maxDate" class="form-control d-inline w-auto mx-2" value="{{ $endDate }}">
            <button type="button" id="filterButton" class="btn btn-secondary mx-2">Filter</button>
            <button type="button" id="resetButton" class="btn btn-outline-secondary">Reset to Payroll Period</button>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Current payroll period: <strong>{{ $startDate }}</strong> to <strong>{{ $endDate }}</strong>
        </div>

        <table id="attendanceTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Date In</th>
                    <th>Time In</th>
                    <th>Date Out</th>
                    <th>Time Out</th>
                    <th>Total Hours</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <button type="button" id="importButton" class="btn btn-primary mt-3">IMPORT DTR</button>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Store the payroll dates for reset functionality
    const payrollStartDate = '{{ $startDate }}';
    const payrollEndDate = '{{ $endDate }}';

    var table = $('#attendanceTable').DataTable({
        ajax: {
            url: '/attendance/data',
            data: function(d) {
                d.minDate = $('#minDate').val();
                d.maxDate = $('#maxDate').val();
            },
            dataSrc: 'data'
        },
        columns: [{
                data: 'employee_id'
            },
            {
                data: 'transindate'
            },
            {
                data: 'time_in'
            },
            {
                data: 'transoutdate'
            },
            {
                data: 'time_out'
            },
            {
                data: 'total_hours',
                render: function(data, type, row) {
                    return data ? parseFloat(data).toFixed(2) : '0.00';
                }
            }
        ],
        responsive: true,
        autoWidth: false,
        ordering: true,
        pageLength: 10,
        processing: true
    });

    // Handle payroll selection change
    $('#payrollSelect').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var fromDate = selectedOption.data('from');
        var toDate = selectedOption.data('to');

        if (fromDate && toDate) {
            $('#minDate').val(fromDate);
            $('#maxDate').val(toDate);

            // Reload table with new date range
            table.ajax.reload();

            // Enable buttons when payroll is selected
            $('#importButton, #uploadButton').prop('disabled', false);
        } else {
            // Clear dates and disable buttons if no payroll selected
            $('#minDate, #maxDate').val('');

            // Clear table data
            table.clear().draw();

            $('#importButton, #uploadButton').prop('disabled', true);
        }
    });

    // Import button click handler
    $('#importButton').on('click', function() {
        var payrollId = $('#payrollSelect').val();
        var minDate = $('#minDate').val();
        var maxDate = $('#maxDate').val();

        if (!payrollId || !minDate || !maxDate) {
            alert('Please select a payroll first.');
            return;
        }

        $.ajax({
            url: '{{ route("attendance.import") }}',
            method: 'POST',
            data: {
                payroll_id: payrollId,
                minDate: minDate,
                maxDate: maxDate,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                alert(response.message);
                table.ajax.reload();
            },
            error: function(xhr) {
                console.error('Import error:', xhr.responseText);
                alert('Import failed: ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr
                    .statusText));
            },
            complete: function() {
                // Re-enable button
                $('#importButton').prop('disabled', false).text('IMPORT DTR');
            }
        });
    });

    // Upload button click handler (you can customize this as needed)
    $('#uploadButton').on('click', function() {
        var payrollId = $('#payrollSelect').val();
        var minDate = $('#minDate').val();
        var maxDate = $('#maxDate').val();

        if (!payrollId || !minDate || !maxDate) {
            alert('Please select a payroll first.');
            return;
        }

        // Add your upload logic here
        alert('Upload functionality - customize as needed');
    });
});
</script>
@stop