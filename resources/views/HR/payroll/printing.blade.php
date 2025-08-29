@extends('adminlte::page')

@section('title', 'Print Payslip')
@section('plugins.Select2', false)

<link rel="icon" type="image/x-icon" href="LOGO.ico">

@section('content_header')
    <h1 class="ml-1">Print Payslip</h1>
@stop

@section('content')
<!-- Step 1: Payroll Selection -->
<div class="card" id="payroll-selection-card">
    <div class="card-header">
        <h3 class="card-title">Select Payroll to Print</h3>
    </div>
    <div class="card-body">
        <form id="payroll-form">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Payroll Code</label>
                        <select class="form-control" id="payroll_select" name="payroll" required>
                            <option value="">Please Select Payroll</option>
                            {{-- You'll need to pass payroll codes from controller --}}
                            @if(isset($payrolls))
                                @foreach ($payrolls as $payroll_code)
                                    <option value="{{ $payroll_code }}">{{ $payroll_code }}</option>
                                @endforeach
                            @else
                                {{-- Sample data - replace with actual data from controller --}}
                                <option value="2024-01-01">2024-01-01</option>
                                <option value="2024-01-15">2024-01-15</option>
                                <option value="2024-02-01">2024-02-01</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="button" class="btn btn-primary" id="load-employees-btn">
                                <i class="fas fa-search"></i> Load Employees
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Step 2: Employee List -->
<div class="card" id="employee-list-card" style="display: none;">
    <div class="card-header">
        <h3 class="card-title">Employees with Payslips - <span id="selected-payroll"></span></h3>
        <div class="card-tools">
            <button type="button" class="btn btn-secondary btn-sm" id="back-to-selection">
                <i class="fas fa-arrow-left"></i> Back to Selection
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped" id="employees-table">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Position</th>
                        <th>Present Days</th>
                        <th>Net Pay</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="employees-table-body">
                    <!-- Employee data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Step 3: Payslip Print View -->
<div class="card" id="payslip-print-card" style="display: none;">
    <div class="card-body">
        <!-- Print Button -->
        <div class="text-center mb-3 no-print">
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Payslip
            </button>
            <button type="button" class="btn btn-secondary ml-2" id="back-to-employees">
                <i class="fas fa-arrow-left"></i> Back to Employee List
            </button>
        </div>

        <!-- Payslip Preview -->
        <div id="payslip-content" class="payslip-container">
            <!-- Company Header -->
            <div class="company-header text-center mb-4">
                <h2><strong>COMPANY NAME</strong></h2>
                <p>Company Address<br>
                   Contact Information<br>
                   Email: company@email.com</p>
                <hr>
                <h4><strong>PAYSLIP</strong></h4>
            </div>

            <!-- Employee Information -->
            <div class="row employee-info mb-4">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Employee ID:</strong></td>
                            <td id="print_employee_id">-</td>
                        </tr>
                        <tr>
                            <td><strong>Employee Name:</strong></td>
                            <td id="print_employee_name">-</td>
                        </tr>
                        <tr>
                            <td><strong>Position:</strong></td>
                            <td id="print_position">-</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Payroll:</strong></td>
                            <td id="print_payroll">-</td>
                        </tr>
                        <tr>
                            <td><strong>Pay Period:</strong></td>
                            <td id="print_pay_period">-</td>
                        </tr>
                        <tr>
                            <td><strong>Pay Date:</strong></td>
                            <td id="print_pay_date">{{ date('Y-m-d') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Attendance Information -->
            <div class="attendance-info mb-4">
                <h5><strong>ATTENDANCE</strong></h5>
                <div class="row">
                    <div class="col-md-4">
                        <strong>Present Days:</strong> <span id="print_present_days">0</span> days
                    </div>
                    <div class="col-md-4">
                        <strong>Undertime:</strong> <span id="print_undertime">0</span> mins
                    </div>
                    <div class="col-md-4">
                        <strong>Tardy:</strong> <span id="print_tardy">0</span> mins
                    </div>
                </div>
                <hr>
            </div>

            <!-- Earnings and Deductions -->
            <div class="row">
                <!-- Earnings -->
                <div class="col-md-6">
                    <h5><strong>EARNINGS</strong></h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody id="print_earnings_body">
                            <tr>
                                <td>Basic Salary</td>
                                <td class="text-right" id="print_basic_salary">0.00</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td>TOTAL EARNINGS</td>
                                <td class="text-right" id="print_total_earnings">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Deductions -->
                <div class="col-md-6">
                    <h5><strong>DEDUCTIONS</strong></h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody id="print_deductions_body">
                            <!-- Deductions will be populated here -->
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td>TOTAL DEDUCTIONS</td>
                                <td class="text-right" id="print_total_deductions">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Net Pay Summary -->
            <div class="net-pay-summary mt-4">
                <div class="row">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <table class="table table-bordered">
                            <tr>
                                <td><strong>GROSS PAY:</strong></td>
                                <td class="text-right" id="print_gross_pay">0.00</td>
                            </tr>
                            <tr>
                                <td><strong>TOTAL DEDUCTIONS:</strong></td>
                                <td class="text-right" id="print_total_deductions_summary">0.00</td>
                            </tr>
                            <tr class="bg-light font-weight-bold">
                                <td><strong>NET PAY:</strong></td>
                                <td class="text-right" id="print_net_pay">0.00</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="signatures mt-5">
                <div class="row">
                    <div class="col-md-6 text-center">
                        <br><br>
                        <hr style="width: 200px;">
                        <p><strong>Employee Signature</strong></p>
                    </div>
                    <div class="col-md-6 text-center">
                        <br><br>
                        <hr style="width: 200px;">
                        <p><strong>HR/Payroll Officer</strong></p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer-note mt-4 text-center">
                <small>This is a computer-generated payslip and does not require a signature.</small>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #payslip-content, #payslip-content * {
            visibility: visible;
        }
        #payslip-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print, .btn, .card-header {
            display: none !important;
        }
        .content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
        }
    }

    .payslip-container {
        background: white;
        padding: 20px;
        font-family: Arial, sans-serif;
    }

    .company-header h2 {
        margin-bottom: 10px;
        color: #333;
    }

    .employee-info table td {
        padding: 5px 10px;
        border: none;
    }

    .attendance-info {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
    }

    .net-pay-summary .table {
        font-size: 16px;
    }

    .signatures hr {
        border-top: 1px solid #000;
        margin: 0 auto;
    }

    .footer-note {
        color: #666;
        font-size: 12px;
    }

    .table th {
        background-color: #f8f9fa;
    }

    .btn-print-individual {
        font-size: 12px;
        padding: 5px 10px;
    }
</style>
@endsection

@section('js')
<script>
// Sample data - Replace this with actual data from your database
let samplePayslipData = {
    '2024-01-01': [
        {
            id: 1,
            employeeId: 'EMP001',
            employeeName: 'John Doe',
            position: 'Software Engineer',
            salary: 50000,
            presentDays: 22,
            undertime: 0,
            tardy: 30,
            withholdingTax: 2500,
            earnings: [
                { name: 'Overtime', amount: 2000 },
                { name: 'Allowance', amount: 1500 }
            ],
            deductions: [
                { name: 'SSS', amount: 500 },
                { name: 'Pag-IBIG', amount: 200 },
                { name: 'PhilHealth', amount: 625 }
            ],
            netPay: 48675
        },
        {
            id: 2,
            employeeId: 'EMP002',
            employeeName: 'Jane Smith',
            position: 'HR Manager',
            salary: 45000,
            presentDays: 20,
            undertime: 120,
            tardy: 0,
            withholdingTax: 2000,
            earnings: [
                { name: 'Performance Bonus', amount: 3000 }
            ],
            deductions: [
                { name: 'SSS', amount: 450 },
                { name: 'Pag-IBIG', amount: 200 },
                { name: 'PhilHealth', amount: 562.50 }
            ],
            netPay: 42287.50
        }
    ],
    '2024-01-15': [
        {
            id: 3,
            employeeId: 'EMP003',
            employeeName: 'Mike Johnson',
            position: 'Accountant',
            salary: 40000,
            presentDays: 21,
            undertime: 0,
            tardy: 0,
            withholdingTax: 1800,
            earnings: [],
            deductions: [
                { name: 'SSS', amount: 400 },
                { name: 'Pag-IBIG', amount: 200 },
                { name: 'PhilHealth', amount: 500 }
            ],
            netPay: 36372.73
        }
    ]
};

document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    
    // Check if there's direct payslip data from generate page
    loadDirectPayslipData();
});

function initializeEventListeners() {
    // Load employees button
    document.getElementById('load-employees-btn').addEventListener('click', loadEmployees);
    
    // Back buttons
    document.getElementById('back-to-selection').addEventListener('click', backToSelection);
    document.getElementById('back-to-employees').addEventListener('click', backToEmployees);
}

function loadDirectPayslipData() {
    try {
        const payslipData = localStorage.getItem('payslipData');
        if (payslipData) {
            const data = JSON.parse(payslipData);
            showPayslipView(data);
            localStorage.removeItem('payslipData');
        }
    } catch (error) {
        console.error('Error loading direct payslip data:', error);
    }
}

function loadEmployees() {
    const payrollCode = document.getElementById('payroll_select').value;
    
    if (!payrollCode) {
        alert('Please select a payroll code');
        return;
    }
    
    // Show loading state
    const button = document.getElementById('load-employees-btn');
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    button.disabled = true;
    
    // Make AJAX request to get employees
    fetch('{{ route("printing.employees") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            payroll_code: payrollCode
        })
    })
    .then(response => response.json())
    .then(employees => {
        if (employees.length === 0) {
            alert('No employees found for this payroll');
            return;
        }
        
        populateEmployeeTable(employees, payrollCode);
        showEmployeeList(payrollCode);
    })
    .catch(error => {
        console.error('Error loading employees:', error);
        alert('Error loading employees. Please try again.');
    })
    .finally(() => {
        // Reset button state
        button.innerHTML = '<i class="fas fa-search"></i> Load Employees';
        button.disabled = false;
    });
}

function populateEmployeeTable(employees, payrollCode) {
    const tbody = document.getElementById('employees-table-body');
    tbody.innerHTML = '';
    
    employees.forEach(employee => {
        const fullName = `${employee.first_name} ${employee.last_name}`;
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${employee.employee_id}</td>
            <td>${fullName}</td>
            <td>${employee.position}</td>
            <td>${employee.present_days}</td>
            <td>₱${formatCurrency(employee.net_pay)}</td>
            <td>
                <button type="button" class="btn btn-primary btn-sm btn-print-individual" 
                        onclick="printEmployeePayslip(${employee.id}, '${payrollCode}')">
                    <i class="fas fa-print"></i> Print
                </button>
            </td>
        `;
    });
}

function printEmployeePayslip(employeeId, payrollCode) {
    // Show loading state
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    button.disabled = true;
    
    // Make AJAX request to get employee payslip data
    fetch('{{ route("printing.payslip") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            employee_id: employeeId,
            payroll_code: payrollCode
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data) {
            // Add payroll code to employee data
            data.payroll = payrollCode;
            showPayslipView(data);
        } else {
            alert('Payslip data not found');
        }
    })
    .catch(error => {
        console.error('Error loading payslip:', error);
        alert('Error loading payslip data. Please try again.');
    })
    .finally(() => {
        // Reset button state
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

function showEmployeeList(payrollCode) {
    document.getElementById('selected-payroll').textContent = payrollCode;
    document.getElementById('payroll-selection-card').style.display = 'none';
    document.getElementById('employee-list-card').style.display = 'block';
    document.getElementById('payslip-print-card').style.display = 'none';
}

function showPayslipView(data) {
    populatePayslip(data);
    document.getElementById('payroll-selection-card').style.display = 'none';
    document.getElementById('employee-list-card').style.display = 'none';
    document.getElementById('payslip-print-card').style.display = 'block';
}

function backToSelection() {
    document.getElementById('payroll-selection-card').style.display = 'block';
    document.getElementById('employee-list-card').style.display = 'none';
    document.getElementById('payslip-print-card').style.display = 'none';
}

function backToEmployees() {
    document.getElementById('payroll-selection-card').style.display = 'none';
    document.getElementById('employee-list-card').style.display = 'block';
    document.getElementById('payslip-print-card').style.display = 'none';
}

function populatePayslip(data) {
    // Employee Information
    document.getElementById('print_employee_id').textContent = data.employeeId || '-';
    document.getElementById('print_employee_name').textContent = data.employeeName || '-';
    document.getElementById('print_payroll').textContent = data.payroll || '-';
    document.getElementById('print_position').textContent = data.position || '-';
    
    // Attendance
    document.getElementById('print_present_days').textContent = data.presentDays || '0';
    document.getElementById('print_undertime').textContent = data.undertime || '0';
    document.getElementById('print_tardy').textContent = data.tardy || '0';
    
    // Calculate basic salary
    const dailyRate = (parseFloat(data.salary) || 0) / 22;
    const presentDays = parseFloat(data.presentDays) || 0;
    const basicSalary = dailyRate * presentDays;
    document.getElementById('print_basic_salary').textContent = formatCurrency(basicSalary);
    
    // Populate earnings
    populateEarningsTable(data.earnings || [], basicSalary);
    
    // Populate deductions
    populateDeductionsTable(data.deductions || [], data);
    
    // Calculate and display totals
    calculateTotals(data);
}

function populateEarningsTable(earnings, basicSalary) {
    const tbody = document.getElementById('print_earnings_body');
    
    // Remove existing additional earnings (keep basic salary row)
    const rows = tbody.querySelectorAll('tr');
    for (let i = rows.length - 1; i > 0; i--) {
        tbody.removeChild(rows[i]);
    }
    
    // Add additional earnings after basic salary
    earnings.forEach(earning => {
        if (earning.name && earning.amount > 0) {
            const row = tbody.insertRow();
            row.innerHTML = `
                <td>${earning.name}</td>
                <td class="text-right">${formatCurrency(earning.amount)}</td>
            `;
        }
    });
}

function populateDeductionsTable(deductions, data) {
    const tbody = document.getElementById('print_deductions_body');
    tbody.innerHTML = ''; // Clear existing content
    
    // Add regular deductions
    deductions.forEach(deduction => {
        if (deduction.name && (deduction.amount > 0 || deduction.name === 'SSS' || deduction.name === 'Pag-IBIG' || deduction.name === 'PhilHealth')) {
            const row = tbody.insertRow();
            row.innerHTML = `
                <td>${deduction.name}</td>
                <td class="text-right">${formatCurrency(deduction.amount || 0)}</td>
            `;
        }
    });
    
    // Add undertime and tardy deductions if applicable
    const salary = parseFloat(data.salary) || 0;
    const dailyRate = salary / 22;
    const minuteRate = dailyRate / 480;
    
    const undertimeDeduction = (parseFloat(data.undertime) || 0) * minuteRate;
    const tardyDeduction = (parseFloat(data.tardy) || 0) * minuteRate;
    
    if (undertimeDeduction > 0) {
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>Undertime Deduction</td>
            <td class="text-right">${formatCurrency(undertimeDeduction)}</td>
        `;
    }
    
    if (tardyDeduction > 0) {
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>Tardy Deduction</td>
            <td class="text-right">${formatCurrency(tardyDeduction)}</td>
        `;
    }
    
    // Add withholding tax
    const withholdingTax = parseFloat(data.withholdingTax) || 0;
    if (withholdingTax > 0) {
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>Withholding Tax</td>
            <td class="text-right">${formatCurrency(withholdingTax)}</td>
        `;
    }
}

function calculateTotals(data) {
    const salary = parseFloat(data.salary) || 0;
    const dailyRate = salary / 22;
    const presentDays = parseFloat(data.presentDays) || 0;
    const basicSalary = dailyRate * presentDays;
    
    // Calculate total earnings
    let totalEarnings = basicSalary;
    (data.earnings || []).forEach(earning => {
        totalEarnings += parseFloat(earning.amount) || 0;
    });
    
    // Calculate total deductions
    let totalDeductions = 0;
    (data.deductions || []).forEach(deduction => {
        totalDeductions += parseFloat(deduction.amount) || 0;
    });
    
    // Add undertime and tardy deductions
    const minuteRate = dailyRate / 480;
    const undertimeDeduction = (parseFloat(data.undertime) || 0) * minuteRate;
    const tardyDeduction = (parseFloat(data.tardy) || 0) * minuteRate;
    const withholdingTax = parseFloat(data.withholdingTax) || 0;
    
    totalDeductions += undertimeDeduction + tardyDeduction + withholdingTax;
    
    // Calculate net pay
    const netPay = totalEarnings - totalDeductions;
    
    // Update display
    document.getElementById('print_total_earnings').textContent = formatCurrency(totalEarnings);
    document.getElementById('print_total_deductions').textContent = formatCurrency(totalDeductions);
    document.getElementById('print_total_deductions_summary').textContent = formatCurrency(totalDeductions);
    document.getElementById('print_gross_pay').textContent = formatCurrency(totalEarnings);
    document.getElementById('print_net_pay').textContent = formatCurrency(netPay);
}

function formatCurrency(amount) {
    return parseFloat(amount || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
</script>
@endsection