@extends('adminlte::page')

@section('title', 'Payroll')
@section('plugins.Datatables', true)
@section('plugins.Select2', true)

@section('content_header')
<h1 class="ml-1">Payroll List</h1>
@stop

@section('content')
<div class="container">
    <h2 class="mb-4">Batch Payslip Printing</h2>

    @if ($message)
    <div class="alert alert-warning">
        {{ $message }}
    </div>
    @else
    <div class="card mb-4">
        <div class="card-header">
            <strong>Active Payroll Period</strong>
        </div>
        <div class="card-body">
            <p>
                <strong>Title:</strong> {{ $payroll->title }} <br>
                <strong>Cutoff:</strong> {{ $payroll->from_date->format('M d, Y') }}
                to {{ $payroll->to_date->format('M d, Y') }} <br>
                <strong>Status:</strong> {{ ucfirst($payroll->status) }}
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <strong>Payslips</strong>
        </div>

        {{-- START OF NEW CODE --}}
        <div class="card-body">
            @if ($payslips->isEmpty())
            <p class="text-muted">No payslips found for this payroll period.</p>
            @else
            <div>
                <form id="department-form" method="GET" action="{{ route('HR.payslip.batch') }}">
                    <div class="form-group" style="width: 300px;">
                        <label for="department">Department:</label>
                        <select name="department" id="department" class="form-control select2">
                            <option value="">All Departments</option>
                            @foreach ($departments as $department)
                            <option value="{{ $department->department }}"
                                {{ $selectedDepartment == $department->department ? 'selected' : '' }}>
                                {{ $department->department }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Gross Pay</th>
                        <th>Total Deductions</th>
                        <th>Net Pay</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payslips as $payslip)
                    <tr>
                        <td>{{ $payslip->employee ? $payslip->employee->last_name . ', ' . $payslip->employee->first_name : 'N/A' }}
                        </td>
                        <td>{{ number_format($payslip->gross_pay, 2) }}</td>
                        <td>{{ number_format($payslip->total_deductions, 2) }}</td>
                        <td><strong>{{ number_format($payslip->net_pay, 2) }}</strong></td>
                        <td>
                            <a href="{{ route('batch.show', $payslip->id) }}" class="btn btn-sm btn-primary"
                                target="_blank">
                                Print
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
        {{-- END OF NEW CODE --}}

    </div>
    @endif
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Initialize Select2 on the department dropdown
    $('.select2').select2({
        placeholder: "Select a department",
        allowClear: true
    });

    // Submit the form when a new department is selected
    $('#department').on('change', function() {
        $('#department-form').submit();
    });
});
</script>
@stop