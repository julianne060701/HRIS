@extends('adminlte::page')

@section('title', 'Edit Employee')

@section('head')
    <link rel="icon" type="image/x-icon" href="{{ asset('LOGO.ico') }}">
@endsection

@section('content_header')
    <h1 class="ml-1">Edit Employee</h1>
@stop

@section('content')
<div class="container">
    <div class="card shadow">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="employeeTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="personal-tab" data-toggle="tab" href="#personal" role="tab">Personal Info</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="gov-tab" data-toggle="tab" href="#gov" role="tab">Government IDs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="job-tab" data-toggle="tab" href="#job" role="tab">Job Info</a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <form action="{{ route('hr.update_employee', $employee->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="tab-content" id="employeeTabsContent">
                    <!-- Personal Info -->
                    <div class="tab-pane fade show active" id="personal" role="tabpanel">
                        <div class="form-group">
                            <label for="employee_id">Employee ID</label>
                            <input type="text" name="employee_id" class="form-control" value="{{ $employee->employee_id }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Name</label>
                            <div class="form-row">
                                <div class="col">
                                    <input type="text" name="first_name" class="form-control" value="{{ $employee->first_name }}" required>
                                </div>
                                <div class="col">
                                    <input type="text" name="middle_name" class="form-control" value="{{ $employee->middle_name }}">
                                </div>
                                <div class="col">
                                    <input type="text" name="last_name" class="form-control" value="{{ $employee->last_name }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="birthday">Birthday</label>
                            <input type="date" name="birthday" class="form-control"
    value="{{ $employee->birthday->format('Y-m-d') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" name="contact_number" class="form-control" value="{{ $employee->contact_number }}">
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ $employee->address }}</textarea>
                        </div>
                    </div>

                    <!-- Government IDs -->
                    <div class="tab-pane fade" id="gov" role="tabpanel">
                        <div class="form-group">
                            <label for="sss">SSS Number</label>
                            <input type="text" name="sss" class="form-control" value="{{ $employee->sss }}">
                        </div>
                        <div class="form-group">
                            <label for="philhealth">PhilHealth Number</label>
                            <input type="text" name="philhealth" class="form-control" value="{{ $employee->philhealth }}">
                        </div>
                        <div class="form-group">
                            <label for="tin">TIN Number</label>
                            <input type="text" name="tin" class="form-control" value="{{ $employee->tin }}">
                        </div>
                        <div class="form-group">
                            <label for="pagibig">Pag-IBIG Number</label>
                            <input type="text" name="pagibig" class="form-control" value="{{ $employee->pagibig }}">
                        </div>
                    </div>

                    <!-- Job Info -->
                    <div class="tab-pane fade" id="job" role="tabpanel">
                        <div class="form-group">
                            <label for="status">Employment Status</label>
                            <select name="status" class="form-control" required>
                                <option value="Probationary" {{ $employee->status == 'Probationary' ? 'selected' : '' }}>Probationary</option>
                                <option value="Regular" {{ $employee->status == 'Regular' ? 'selected' : '' }}>Regular</option>
                                <option value="Resigned" {{ $employee->status == 'Resigned' ? 'selected' : '' }}>Resigned</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="department">Department</label>
                            <input type="text" name="department" class="form-control" value="{{ $employee->department }}" required>
                        </div>

                        <div class="form-group">
                            <label for="salary">Salary</label>
                            <input type="number" name="salary" class="form-control" value="{{ $employee->salary }}" step="0.01" min="0">
                        </div>
                    </div>
                </div>

                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
