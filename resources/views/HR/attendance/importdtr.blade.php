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
                        <th>Date In</th>
                        <th>Time In</th>
                        <th>Date Out</th>
                        <th>Time Out</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <button type="button" id="importButton" class="btn btn-primary mt-3">IMPORT DTR</button>
            <button type="button" id="uploadButton" class="btn btn-primary mt-3">UPLOAD DTR</button>
        </div>
    </div>
@stop

@section('js')
<script>
   $(document).ready(function () {
    var table = $('#attendanceTable').DataTable({
        ajax: {
            url: '/attendance/data', // or "{{ route('attendance.data') }}"
            dataSrc: 'data'
        },
        columns: [
            { data: 'employee_id' },
            { data: 'transindate' },
            { data: 'time_in' },
            { data: 'transoutdate' },
            { data: 'time_out' }
        ],
        responsive: true,
        autoWidth: false,
        ordering: true,
        pageLength: 10
    });

    // Your other code, e.g. button click handlers, import logic, etc.

    $('#importButton').on('click', function() {
        var minDate = $('#minDate').val();
        var maxDate = $('#maxDate').val();

        $.ajax({
            url: '{{ route("attendance.import") }}',
            method: 'POST',
            data: {
                minDate: minDate,
                maxDate: maxDate,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                alert(response.message);
                table.ajax.reload();  // reload the table using the stored instance
            },
            error: function(xhr) {
                alert('Import failed: ' + xhr.statusText);
            }
        });
    });
});

</script>
@stop
