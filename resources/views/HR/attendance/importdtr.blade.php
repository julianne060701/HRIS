@extends('adminlte::page')

@section('title', 'Employee Attendance')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

@section('content_header')
    <h1 class="ml-1">Process DTR</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <label for="minDate">From:</label>
                <input type="date" id="minDate" class="form-control d-inline w-auto mx-2">
                <label for="maxDate">To:</label>
                <input type="date" id="maxDate" class="form-control d-inline w-auto mx-2">
            </div>       
            <table id="attendanceTable" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
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
    $(document).ready(function () {
        // Initialize DataTable
        var table = $('#attendanceTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('attendance.data') }}',
                data: function (d) {
                    // Add filter params (minDate and maxDate) to the request
                    d.minDate = $('#minDate').val();
                    d.maxDate = $('#maxDate').val();
                }
            },
            columns: [
                { data: 'employee_id' },
                { data: 'transdate' },
                { data: 'time_in_full' },
                { data: 'time_out_full' }
            ],
            responsive: true,
            autoWidth: false,
            ordering: true,
            pageLength: 10,
        });

        // Apply filter on date change
        $('#minDate, #maxDate').on('change', function () {
            table.ajax.reload();
        });

        // Click event for import button (optional)
        $('#importButton').on('click', function () {
            var attendanceData = table.rows().data().toArray();
            $.ajax({
                url: '{{ route('attendance.store') }}',
                method: 'POST',
                 data: {
                    _token: '{{ csrf_token() }}',
                    attendance_data: attendanceData // Send the attendance data
                },
                success: function (response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        table.ajax.reload(); // Reload table after storing
                    } else {
                        alert(response.message);
                    }
                },
                error: function () {
                    alert('An error occurred while saving the data.');
                }
            });
        });
   });
</script>
@stop
