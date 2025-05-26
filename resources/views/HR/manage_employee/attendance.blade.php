@extends('adminlte::page')

@section('title', 'Employee Attendance')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

<link rel="icon" type="image/x-icon" href="{{ asset('LOGO.ico') }}">

@section('content_header')
    <h1 class="ml-1">Employee Attendance</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="attendanceTable" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Static Data Rows -->
                    <tr>
                        <td>EMP001</td>
                        <td>Ariana Holmes</td>
                        <td>2025-05-08</td>
                        <td>08:00 AM</td>
                        <td>05:00 PM</td>
                        <td>Present</td>
                    </tr>
                    <tr>
                        <td>EMP002</td>
                        <td>John Doe</td>
                        <td>2025-05-08</td>
                        <td>08:30 AM</td>
                        <td>05:30 PM</td>
                        <td>Present</td>
                    </tr>
                    <tr>
                        <td>EMP003</td>
                        <td>Jane Smith</td>
                        <td>2025-05-08</td>
                        <td>09:00 AM</td>
                        <td>06:00 PM</td>
                        <td>Absent</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function () {
        $('#attendanceTable').DataTable({
            responsive: true,
            autoWidth: false,
            ordering: true,
            pageLength: 10,
        });
    });
</script>
@endsection
