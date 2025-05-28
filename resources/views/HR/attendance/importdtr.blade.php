@extends('adminlte::page')

@section('title', 'Employee Attendance')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

@section('content_header')
    <h1 class="ml-1">IMPORT DTR</h1>
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
                        <th>Name</th>
                        <th>Date</th>
                        <th>Time In (Full)</th>
                        <th>Time Out (Full)</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <button id="importButton" class="btn btn-primary mt-3">IMPORT DTR</button>

        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function () {
        // Initialize DataTable
        var table = $('#attendanceTable').DataTable({
            ajax: '/attendance/data',
            columns: [
                { data: 'employee_id' },
                { data: 'name' },
                { data: 'transdate' },
                { data: 'time_in_full' },
                { data: 'time_out_full' }
            ],
            responsive: true,
            autoWidth: false,
            ordering: true,
            pageLength: 10,
        });

        // Custom filtering function
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            var min = $('#minDate').val();
            var max = $('#maxDate').val();
            var date = data[2]; // "transdate" column

            if (min) min = new Date(min);
            if (max) max = new Date(max);
            var rowDate = new Date(date);

            return (!min || rowDate >= min) && (!max || rowDate <= max);
        });

        // Apply filter on date change
        $('#minDate, #maxDate').on('change', function () {
            table.draw();
        });

        // Click event for import button (optional)
        $('#importButton').on('click', function () {
            // You can trigger an import request or just refresh table
            table.ajax.reload();
        });
    });
</script>
@stop
