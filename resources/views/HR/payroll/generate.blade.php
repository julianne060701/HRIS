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
                        @foreach ($payrolls as $payroll_code)
                            <option value="{{ $payroll_code }}">{{ $payroll_code }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Employee Dropdown -->
                <div class="col-md-6 form-group">
                    <label>Employee</label>
                    <select class="form-control" name="employee_id" id="employee_select" required>
                        <option value="">Please Select Here</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" data-salary="{{ $employee->salary }}">
                                {{ $employee->employee_id }} - {{ $employee->first_name }} {{ $employee->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label>Present <small>days</small></label>
                    <input type="number" class="form-control" id="present_days" value="0">
                </div>
                <div class="col-md-3 form-group">
                    <label>Undertime <small>mins</small></label>
                    <input type="number" class="form-control" id="undertime_mins" value="0">
                </div>
                <div class="col-md-3 form-group">
                    <label>Tardy <small>mins</small></label>
                    <input type="number" class="form-control" id="tardy_mins" value="0">
                </div>
            </div>

            <div class="row">
                <!-- Earnings -->
                <div class="col-md-6">
                    <h5><strong>Earnings</strong></h5>
                    <table class="table table-bordered" id="earnings_table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Amount</th>
                                <th><button type="button" class="btn btn-sm btn-success" onclick="addRow('earnings')">+</button></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="text" class="form-control" name="earnings[0][name]"></td>
                                <td><input type="number" class="form-control" name="earnings[0][amount]" value="0"></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Deductions -->
                <div class="col-md-6">
                    <h5><strong>Deductions</strong></h5>
                    <table class="table table-bordered" id="deductions_table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Amount</th>
                                <th><button type="button" class="btn btn-sm btn-success" onclick="addRow('deductions')">+</button></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="text" class="form-control" name="deductions[0][name]" value="SSS"></td>
                                <td><input type="number" class="form-control" name="deductions[0][amount]" value=""></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><input type="text" class="form-control" name="deductions[1][name]" value="Pag-IBIG"></td>
                                <td><input type="number" class="form-control" name="deductions[1][amount]" value=""></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><input type="text" class="form-control" name="deductions[2][name]" value="PhilHealth"></td>
                                <td><input type="number" class="form-control" name="deductions[2][amount]" value=""></td>
                                <td></td>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6 form-group">
                    <label>Withholding Tax</label>
                    <input type="number" class="form-control" id="withholding_tax" value="0">
                </div>
                <div class="col-md-6 form-group">
                    <label>Net</label>
                    <input type="text" class="form-control" id="net_pay" readonly>
                </div>
            </div>

            <div class="text-center mt-3">
                <button type="button" class="btn btn-primary"><i class="fas fa-save"></i> Save Payslip</button>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
    let earningCount = 1;
    let deductionCount = 2;

    function addRow(type) {
        const table = document.getElementById(`${type}_table`).getElementsByTagName('tbody')[0];
        const index = type === 'earnings' ? earningCount++ : deductionCount++;

        const row = table.insertRow();
        row.innerHTML = `
            <td><input type="text" class="form-control" name="${type}[${index}][name]"></td>
            <td><input type="number" class="form-control" name="${type}[${index}][amount]" value="0"></td>
            <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">×</button></td>
        `;
    }

    function removeRow(button) {
        const row = button.closest('tr');
        row.remove();
        computeNetPay();
    }

    function getTotalAmount(namePrefix) {
        let total = 0;
        document.querySelectorAll(`input[name^="${namePrefix}"][name$="[amount]"]`).forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        return total;
    }

    function computeNetPay() {
        const empSelect = document.getElementById('employee_select');
        const salary = parseFloat(empSelect.options[empSelect.selectedIndex]?.dataset.salary) || 0;
        const presentDays = parseFloat(document.getElementById('present_days').value) || 0;
        const dailyRate = salary / 22;

        const earnings = getTotalAmount('earnings');
        const deductions = getTotalAmount('deductions');
        const tax = parseFloat(document.getElementById('withholding_tax').value) || 0;

        const net = (presentDays * dailyRate) + earnings - (deductions + tax);
        document.getElementById('net_pay').value = net.toFixed(2);
    }

    // Add listeners
    document.getElementById('employee_select').addEventListener('change', computeNetPay);
    document.getElementById('present_days').addEventListener('input', computeNetPay);
    document.getElementById('withholding_tax').addEventListener('input', computeNetPay);

    document.addEventListener('input', function (e) {
        if (e.target.name?.includes('earnings') || e.target.name?.includes('deductions')) {
            computeNetPay();
        }
    });

    function computeNetPay() {
    const empSelect = document.getElementById('employee_select');
    const salary = parseFloat(empSelect.options[empSelect.selectedIndex]?.dataset.salary) || 0;
    const presentDays = parseFloat(document.getElementById('present_days').value) || 0;
    const undertime = parseFloat(document.getElementById('undertime_mins').value) || 0;
    const tardy = parseFloat(document.getElementById('tardy_mins').value) || 0;
    const withholdingTax = parseFloat(document.getElementById('withholding_tax').value) || 0;

    const dailyRate = salary / 22;
    const minuteRate = dailyRate / 480;

    const undertimeDeduction = undertime * minuteRate;
    const tardyDeduction = tardy * minuteRate;

    const totalEarnings = getTotalAmount('earnings');
    const totalDeductions = getTotalAmount('deductions');

    const net = (presentDays * dailyRate) + totalEarnings
        - (totalDeductions + withholdingTax + undertimeDeduction + tardyDeduction);

    document.getElementById('net_pay').value = net.toFixed(2);
}

['employee_select', 'present_days', 'undertime_mins', 'tardy_mins', 'withholding_tax']
    .forEach(id => document.getElementById(id)?.addEventListener('input', computeNetPay));

document.addEventListener('input', function (e) {
    if (e.target.name?.includes('earnings') || e.target.name?.includes('deductions')) {
        computeNetPay();
    }
});
</script>
@endsection
