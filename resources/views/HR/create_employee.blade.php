@extends('adminlte::page')

@section('title', 'Dashboard')
<link rel="icon" type="image/x-icon" href="{{ asset('LOGO.ico') }}">
@section('content_header')
    <h1 class="ml-1">Create New Employee</h1>
@stop

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employee_id">Employee ID</label>
                                <input type="text" name="employee_id" class="form-control" placeholder="Enter Employee ID" required>
                            </div>

                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
                            </div>

                            <div class="form-group">
                                <label for="birthday">Birthday</label>
                                <input type="date" name="birthday" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="sss">SSS Number</label>
                                <input type="text" name="sss" class="form-control" placeholder="Enter SSS number">
                            </div>

                            <div class="form-group">
                                <label for="philhealth">PhilHealth Number</label>
                                <input type="text" name="philhealth" class="form-control" placeholder="Enter PhilHealth number">
                            </div>

                            <div class="form-group">
                                <label for="tin">TIN Number</label>
                                <input type="text" name="tin" class="form-control" placeholder="Enter TIN number">
                            </div>

                            <div class="form-group">
                                <label for="pagibig">Pag-IBIG Number</label>
                                <input type="text" name="pagibig" class="form-control" placeholder="Enter Pag-IBIG number">
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="">Select status</option>
                                    <option value="Probationary">Probationary</option>
                                    <option value="Regular">Regular</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="department">Department to Assign</label>
                                <input type="text" name="department" class="form-control" placeholder="Enter department" required>
                            </div>

                            <div class="form-group">
                                <label for="contact_number">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control" placeholder="Enter contact number">
                            </div>

                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea name="address" class="form-control" rows="3" placeholder="Enter address"></textarea>
                            </div>

                           
                    </div>

                    <button type="submit" class="btn btn-primary mt-4">Create Employee</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    document.getElementById('resume').addEventListener('change', function(event) {
        const inputFile = event.target;
        const fileName = inputFile.files[0]?.name || 'Choose file';
        const fileLabel = inputFile.nextElementSibling;
        const fileNameDisplay = document.getElementById('fileName');

        fileLabel.textContent = fileName;
        fileNameDisplay.textContent = `Selected file: ${fileName}`;
    });
</script>
@endsection
