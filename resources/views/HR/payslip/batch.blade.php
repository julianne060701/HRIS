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
            <strong>Select Payroll Period</strong>
        </div>
        <div class="card-body">
            <form id="payroll-form" method="GET" action="{{ route('HR.payslip.batch') }}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="payroll_id">Payroll Period:</label>
                            <select name="payroll_id" id="payroll_id" class="form-control select2">
                                <option value="">Select a payroll period</option>
                                @foreach ($allPayrolls as $payrollOption)
                                <option value="{{ $payrollOption->id }}"
                                    {{ $selectedPayrollId == $payrollOption->id ? 'selected' : '' }}>
                                    {{ $payrollOption->title }} - {{ $payrollOption->from_date->format('M d, Y') }} to {{ $payrollOption->to_date->format('M d, Y') }} ({{ ucfirst($payrollOption->status) }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
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
                    </div>
                </div>
            </form>
            
            @if ($payroll)
            <div class="mt-3 p-3 bg-light rounded">
                <h5>Selected Payroll Period:</h5>
                <p class="mb-0">
                    <strong>Title:</strong> {{ $payroll->title }} <br>
                    <strong>Cutoff:</strong> {{ $payroll->from_date->format('M d, Y') }}
                    to {{ $payroll->to_date->format('M d, Y') }} <br>
                    <strong>Status:</strong> {{ ucfirst($payroll->status) }}
                </p>
            </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Payslips</strong>
            @if (!$payslips->isEmpty())
            <div>
                <a href="{{ route('HR.payslip.batch.print', ['payroll_id' => $selectedPayrollId, 'department' => $selectedDepartment]) }}" 
                   class="btn btn-success btn-sm me-2" target="_blank">
                    <i class="fas fa-print"></i> Print All Payslips
                </a>
                <button onclick="saveAsPDF()" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf"></i> Save as PDF
                </button>
            </div>
            @endif
        </div>

        <div class="card-body">
            @if ($payslips->isEmpty())
            <p class="text-muted">No payslips found for this payroll period.</p>
            @else

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
    </div>
    @endif
</div>

@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 on both dropdowns
    $('.select2').select2({
        placeholder: "Select an option",
        allowClear: true
    });

    // Submit the form when either dropdown changes
    $('#payroll_id, #department').on('change', function() {
        $('#payroll-form').submit();
    });
});

function saveAsPDF() {
    // Get the current payroll and department parameters
    const payrollId = document.getElementById('payroll_id').value;
    const department = document.getElementById('department').value;
    
    // Build the URL for the print view
    let url = "{{ route('HR.payslip.batch.print') }}";
    const params = new URLSearchParams();
    if (payrollId) params.append('payroll_id', payrollId);
    if (department) params.append('department', department);
    if (params.toString()) {
        url += '?' + params.toString();
    }
    
    // Show loading message
    const loadingDiv = document.createElement('div');
    loadingDiv.innerHTML = '<div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.8); color: white; padding: 20px; border-radius: 5px; z-index: 9999;">Generating PDF... Please wait</div>';
    document.body.appendChild(loadingDiv);
    
    // Open the print view in a hidden iframe to get the payslips
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = url;
    document.body.appendChild(iframe);
    
    iframe.onload = function() {
        try {
            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            // Allow styles/fonts to finish rendering
            setTimeout(() => {
                const payslips = iframeDoc.querySelectorAll('.payslip-container');
                if (payslips.length === 0) {
                    alert('No payslips found to save as PDF');
                    document.body.removeChild(loadingDiv);
                    document.body.removeChild(iframe);
                    return;
                }

                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: [127, 178] });

                const tasks = Array.from(payslips).map((payslip, index) =>
                    html2canvas(payslip, { scale: 2, useCORS: true, backgroundColor: '#ffffff' })
                        .then(canvas => {
                            const imgData = canvas.toDataURL('image/png');
                            if (index > 0) pdf.addPage([127, 178]);
                            const x = 5, y = 5, imgWidth = 117, imgHeight = 168;
                            pdf.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight);
                        })
                );

                Promise.all(tasks).then(() => {
                    document.body.removeChild(loadingDiv);
                    document.body.removeChild(iframe);
                    const now = new Date();
                    const dateStr = now.toISOString().split('T')[0];
                    pdf.save(`payslips_${dateStr}.pdf`);
                }).catch(error => {
                    console.error('Error generating PDF:', error);
                    document.body.removeChild(loadingDiv);
                    document.body.removeChild(iframe);
                    alert('Error generating PDF. Please try again.');
                });
            }, 300);
        } catch (error) {
            console.error('Error accessing iframe content:', error);
            document.body.removeChild(loadingDiv);
            document.body.removeChild(iframe);
            alert('Error generating PDF. Please try again.');
        }
    };
    
    iframe.onerror = function() {
        document.body.removeChild(loadingDiv);
        document.body.removeChild(iframe);
        alert('Error loading payslips. Please try again.');
    };
}
</script>
@stop