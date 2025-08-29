@extends('adminlte::page')

@section('title', 'Add Employee')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

<link rel="icon" type="image/x-icon" href="{{ asset('LOGO.ico') }}">

@section('content_header')
    <h1 class="ml-1">Department List</h1>
@stop

@section('content')
<div class="container-fluid">

    <!-- Upload new employee and department -->
    <div class="d-flex justify-content-end mb-3">
        <!-- Add Department Button -->
        <button type="button" class="btn btn-success px-5" data-toggle="modal" data-target="#addDepartmentModal">
            Add Department
        </button>
    </div>

    <!-- Employee Data Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    @php
                        $heads = [
                            'ID',
                            'Department Name',  
                            'Actions',
                        ];

                        $config = [
                            'order' => [[0, 'desc']],
                            'columns' => [
                                null,
                                null,
                                ['orderable' => false],
                            ],
                        ];
                    @endphp

                    <x-adminlte-datatable id="employeeTable" :heads="$heads" :config="$config" hoverable class="table-custom">
                        @foreach ($data as $employee)
                            <tr>
                                <td>{{ $employee[0] }}</td>
                                <td>{{ $employee[1] }}</td>
                                <td>{!! $employee[2] !!}</td>
                            </tr>
                        @endforeach
                    </x-adminlte-datatable>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Department Modal -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1" role="dialog" aria-labelledby="addDepartmentLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('HR.departments.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDepartmentLabel">Add Department</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label for="department_name">Department Name</label>
                        <input type="text" name="department_name" id="department_name" class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Department</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: '{{ session("success") }}',
        showConfirmButton: false,
        timer: 2000
    });
</script>
@endif
@endsection
