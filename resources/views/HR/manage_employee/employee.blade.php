@extends('adminlte::page')

@section('title', 'Add Employee')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

<link rel="icon" type="image/x-icon" href="{{ asset('LOGO.ico') }}">

@section('content_header')
    <h1 class="ml-1">Employee List</h1>
@stop

@section('content')

<div class="container-fluid">

    <!-- Upload new employee -->
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('HR.manage_employee.create_employee') }}" class="btn btn-primary px-5">Add Employee</a>
    </div>

    <!-- Employee Data Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    @php
                        $heads = [
                            'Employee ID',
                            'Full Name',  
                            'Status',                       
                            'Department', 
                            'Salary',
                            'Date Joined',
                            'Actions',
                        ];

                        $config = [
                            'order' => [[5, 'desc']], // Sort by Date Joined (column index 5)
                            'columns' => [
                                null, // Employee ID
                                null, // Full Name
                                null, // Status
                                null, // Department
                                null, // Salary
                                null, // Date Joined
                                ['orderable' => false], // Actions (disable sorting)
                            ],
                        ];
                    @endphp

                    <x-adminlte-datatable id="employeeTable" :heads="$heads" :config="$config" hoverable class="table-custom">
                    @foreach ($data as $employee)
                        <tr>
                            <td>{{ $employee[0] }}</td>  <!-- employee_id -->
                            <td>{{ $employee[1] }}</td>  <!-- Full Name -->
                            <td>{{ $employee[2] }}</td>  <!-- Status -->
                            <td>{{ $employee[3] }}</td>  <!-- Department -->
                            <td>{{ $employee[4] }}</td>  <!-- Salary -->
                            <td>{{ $employee[5] }}</td>  <!-- Date Joined -->
                            <td>{!! $employee[6] !!}</td>  <!-- Actions (Edit and Delete) -->
                        </tr>
                    @endforeach
                    </x-adminlte-datatable>
                </div>
            </div>
        </div>
    </div>
</div>


@stop

@section('js')
<script>

</script>
@endsection

