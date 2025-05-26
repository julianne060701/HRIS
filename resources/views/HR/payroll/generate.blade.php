@extends('adminlte::page')

@section('title', 'Generate Payslip')
@section('plugins.Select2', false)

<link rel="icon" type="image/x-icon" href="LOGO.ico">

@section('content_header')
    <h1 class="ml-1">New Payslip</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form>
            <div class="row">
                <!-- Payroll Dropdown -->
                <div class="col-md-6 form-group">
                    <label>Payroll</label>
                    <select class="form-control" name="payroll" required>
                        <option value="">Please Select Here</option>
                        @foreach ($payrolls as $payroll)
                            <option value="{{ $payroll }}">{{ $payroll }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Employee Dropdown -->
                <div class="col-md-6 form-group">
                    <label>Employee</label>
                    <select class="form-control" name="employee_id" required>
                        <option value="">Please Select Here</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">
                                {{ $employee->employee_id }} - {{ $employee->first_name }} {{ $employee->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 form-group">
                    <label>Present <small>days</small></label>
                    <input type="number" class="form-control" value="" >
                </div>
                <div class="col-md-6 form-group">
                    <label>Late/Undertime <small>mins</small></label>
                    <input type="number" class="form-control" value="" >
                </div>
            </div>

            <div class="row">
                <!-- Earnings -->
                <div class="col-md-6">
                    <h5><strong>Earnings</strong></h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($earnings as $earning)
                                <tr>
                                    <td>{{ $earning['name'] }}</td>
                                    <td>{{ number_format($earning['amount'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Deductions -->
                <div class="col-md-6">
                    <h5><strong>Deductions</strong></h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>SSS</td>
                                <td>500.00</td>
                            </tr>
                            <tr>
                                <td>Pag-IBIG</td>
                                <td>100.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6 form-group">
                    <label>Withholding Tax</label>
                    <input type="number" class="form-control" value="" >
                </div>
                <div class="col-md-6 form-group">
                    <label>Net</label>
                    <input type="text" class="form-control" value="" >
                </div>
            </div>

            <div class="text-center mt-3">
                <button type="button" class="btn btn-primary" ><i class="fas fa-save"></i> Save Payslip</button>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<!-- No JavaScript needed -->
@endsection
