@extends('adminlte::page')

@section('title', 'Leave Credits')

@section('plugins.Datatables', true)
{{-- No need for @section('plugins.Select2', true) if you're including it manually as below --}}

@section('css')
    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    {{-- Optional: AdminLTE's own Select2 theme for better integration (if you prefer this theme) --}}
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css"> --}}
    <style>
        /* Adjust Select2 width if needed, or it might be too narrow in modals */
        .select2-container {
            width: 100% !important;
        }
    </style>
@stop

@section('content_header')
    <h1>Leave Credits</h1>
@endsection

@section('content')
    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Error Messages for Validation --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- Leave Credits Card --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Leave Credit List</h3>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addLeaveCreditModal">
                Add Leave Credit
            </button>
        </div>
        <div class="card-body">
            <table id="leave-credits-table" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee ID</th> {{-- Added Employee ID column --}}
                        <th>Employee Name</th>
                        <th>Leave Type</th>
                        <th>Total Leave</th>
                        <th>Remaining Leave</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaveCredits as $index => $leaveCredit)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            {{-- Display Employee ID --}}
                            <td>{{ $leaveCredit->employee->employee_id ?? 'N/A' }}</td>
                            {{-- Concatenate name parts for display --}}
                            <td>
                                @if($leaveCredit->employee)
                                        {{ $leaveCredit->employee->first_name }}
                                        @if($leaveCredit->employee->middle_name)
                                            {{ substr($leaveCredit->employee->middle_name, 0, 1) }}.
                                        @endif
                                        {{ $leaveCredit->employee->last_name }}
                                @else
                                        N/A
                                @endif
                            </td>

                            <td>{{ $leaveCredit->leaveType->name ?? 'N/A' }}</td>
                            <td>{{ $leaveCredit->all_leave }}</td>
                            <td>{{ $leaveCredit->rem_leave }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Leave Credit Modal --}}
   <div class="modal fade" id="addLeaveCreditModal" tabindex="-1" role="dialog" aria-labelledby="addLeaveCreditModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('leave_credit.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addLeaveCreditModalLabel">Add Leave Credit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="employee_id">Employee</label>
                        <select name="employee" id="employee_id" class="form-control select2-employee @error('employee') is-invalid @enderror" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                                {{-- THIS IS THE KEY CHANGE: SEND THE CUSTOM employee_id STRING --}}
                                <option value="{{ $employee->employee_id }}" {{ old('employee') == $employee->employee_id ? 'selected' : '' }}>
                                    {{ $employee->first_name }}
                                    @if($employee->middle_name)
                                        {{ substr($employee->middle_name, 0, 1) }}.
                                    @endif
                                    {{ $employee->last_name }}
                                    ({{ $employee->employee_id }}) {{-- Display the custom employee_id for user clarity --}}
                                </option>
                            @endforeach
                        </select>
                        @error('employee')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        </div>
                        <div class="form-group">
                            <label for="vcleave_id">Vacation Leave</label>
                            <input type="number" name="vcleave" id="vcleave_id" class="form-control @error('vcleave') is-invalid @enderror" value="{{ old('vcleave') }}" required>
                            @error('vcleave')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="skleave_id">Sick Leave</label>
                            <input type="number" name="skleave" id="skleave_id" class="form-control @error('skleave') is-invalid @enderror" value="{{ old('skleave') }}" required>
                            @error('skleave')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="bdleave_id">Birthday Leave</label>
                            <input type="number" name="bdleave" id="bdleave_id" class="form-control @error('bdleave') is-invalid @enderror" value="{{ old('bdleave') }}" required>
                            @error('bdleave')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="mtleave_id">Maternity Leave</label>
                            <input type="number" name="mtleave" id="mtleave_id" class="form-control @error('mtleave') is-invalid @enderror" value="{{ old('mtleave') }}" required>
                            @error('mtleave')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="ptleave_id">Paternity Leave</label>
                            <input type="number" name="ptleave" id="ptleave_id" class="form-control @error('ptleave') is-invalid @enderror" value="{{ old('ptleave') }}" required>
                            @error('ptleave')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('#leave-credits-table').DataTable();
        });
    </script>

    {{-- Select2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 on your employee dropdown
            $('.select2-employee').select2({
                placeholder: "Search for an Employee", // Optional: Add a placeholder
                allowClear: true // Optional: Allow clearing the selection
            });

            // Re-initialize Select2 when the modal is shown (important for modals)
            // This ensures Select2 works correctly every time the modal opens.
            $('#addLeaveCreditModal').on('shown.bs.modal', function () {
                $('.select2-employee').select2({
                    placeholder: "Search for an Employee",
                    allowClear: true,
                    dropdownParent: $('#addLeaveCreditModal') // Important for Select2 in Bootstrap Modals
                });
            });

            // Destroy Select2 instance when modal is hidden to prevent issues on re-opening
            $('#addLeaveCreditModal').on('hidden.bs.modal', function () {
                $('.select2-employee').select2('destroy');
            });
        });
    </script>
@stop