@extends('adminlte::page')

@section('title', 'Give Permission')

@section('content_header')
    <h1>Assign Module Permissions</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
    <form action="{{ route('permissions.store') }}" method="POST">

            @csrf

            <div class="form-group">
                <label for="employee_id">Select Employee (Search by ID or Name)</label>
                <select name="employee_id" id="employee_id" class="form-control select2" required>
                    <option value="">-- Select Employee --</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">
                            {{ $employee->employee_id }} - {{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }} ({{ $employee->department }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Module Permissions</label>
                <div class="form-check">
                    <input type="checkbox" name="modules[]" class="form-check-input" value="HR" id="module_hr">
                    <label class="form-check-label" for="module_hr">HR</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="modules[]" class="form-check-input" value="Payroll" id="module_payroll">
                    <label class="form-check-label" for="module_payroll">Payroll</label>
                </div>
                <!-- Add other modules as needed -->
            </div>

            <button type="submit" class="btn btn-primary">Assign</button>
        </form>
    </div>
</div>
@endsection

@section('css')
    <!-- Include Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('js')
    <!-- Include Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#employee_id').select2({
                placeholder: "-- Select Employee --",
                allowClear: true
            });
        });
    </script>
@endsection
