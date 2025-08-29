@extends('adminlte::page')

@section('title', 'Dashboard')

@section('head')
    <link rel="icon" type="image/x-icon" href="{{ asset('LOGO.ico') }}">
@endsection

@section('content_header')
    <h1 class="ml-1">Create New Employee</h1>
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
            <form action="{{ route('HR.manage_employee.store_employee') }}" method="POST">
                
                @csrf

                <div class="tab-content" id="employeeTabsContent">
                    <!-- Personal Info Tab -->
                    <div class="tab-pane fade show active" id="personal" role="tabpanel">
                        <div class="form-group">
                            <label for="employee_id">Employee ID</label>
                            <input type="text" name="employee_id" class="form-control" placeholder="Enter Employee ID" required>
                        </div>

                        <div class="form-group">
                            <label>Name</label>
                            <div class="form-row">
                                <div class="col">
                                    <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                                </div>
                                <div class="col">
                                    <input type="text" name="middle_name" class="form-control" placeholder="Middle Name">
                                </div>
                                <div class="col">
                                    <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="birthday">Birthday</label>
                            <input type="date" name="birthday" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" name="contact_number" class="form-control" placeholder="Contact Number">
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Enter address"></textarea>
                        </div>
                    </div>

                    <!-- Government IDs Tab -->
                    <div class="tab-pane fade" id="gov" role="tabpanel">
                        <div class="form-group">
                            <label for="sss">SSS Number</label>
                            <input type="text" name="sss" class="form-control" placeholder="SSS Number">
                        </div>
                        <div class="form-group">
                            <label for="philhealth">PhilHealth Number</label>
                            <input type="text" name="philhealth" class="form-control" placeholder="PhilHealth Number">
                        </div>
                        <div class="form-group">
                            <label for="tin">TIN Number</label>
                            <input type="text" name="tin" class="form-control" placeholder="TIN Number">
                        </div>
                        <div class="form-group">
                            <label for="pagibig">Pag-IBIG Number</label>
                            <input type="text" name="pagibig" class="form-control" placeholder="Pag-IBIG Number">
                        </div>
                    </div>

                    <!-- Job Info Tab -->
                    <div class="tab-pane fade" id="job" role="tabpanel">
                        <div class="form-group">
                            <label for="status">Employment Status</label>
                            <select name="status" class="form-control" required>
                                <option value="">Select Status</option>
                                <option value="Probationary">Probationary</option>
                                <option value="Regular">Regular</option>
                                <option value="Resigned">Resigned</option>
                            </select>
                        </div>

                        <div class="form-group">
    <label for="department">Department</label>
    <select name="department" id="department" class="form-control department-select" required>
        <option value="">Select Department</option>
        @foreach($departments as $dept)
            <option value="{{ $dept->name }}">{{ $dept->name }}</option>
        @endforeach
    </select>
</div>




                        <div class="form-group">
                            <label for="salary">Salary</label>
                            <input type="number" name="salary" class="form-control" placeholder="Salary" step="0.01" min="0">
                        </div>
                    </div>
                </div>

                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-success">Create Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


@section('js')
{{-- Select2 CSS & JS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.department-select').select2({
            placeholder: "Select Department",
            allowClear: true,
            width: '100%',
            theme: 'classic' // or 'bootstrap4' if you’re using bootstrap4
        });
    });
</script>


<script>
    document.getElementById('resume')?.addEventListener('change', function(event) {
        const inputFile = event.target;
        const fileName = inputFile.files[0]?.name || 'Choose file';
        document.getElementById('fileName').textContent = `Selected file: ${fileName}`;
    });
</script>
@endsection
