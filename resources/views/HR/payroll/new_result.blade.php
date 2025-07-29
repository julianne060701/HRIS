@extends('adminlte::page')

@section('title', 'Payroll Computation Results')

@section('content_header')
    <h1 class="text-2xl font-semibold text-gray-800">Payroll Computation Results</h1>
@stop

@section('css')
<style>
    .payroll-container {
        background-color: #f8fafc;
        min-height: 100vh;
        padding: 20px 0;
    }
    
    .payroll-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }
    
    .summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 24px;
    }
    
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-top: 16px;
    }
    
    .summary-item {
        background: rgba(255, 255, 255, 0.1);
        padding: 12px;
        border-radius: 6px;
        backdrop-filter: blur(10px);
    }
    
    .table-container {
        overflow-x: auto;
        border-radius: 8px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }
    
    .payroll-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }
    
    .payroll-table th {
        background: #f7fafc;
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        color: #2d3748;
        border-bottom: 2px solid #e2e8f0;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .payroll-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 14px;
    }
    
    .payroll-table tr:hover {
        background-color: #f7fafc;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    .btn-secondary {
        background: #e2e8f0;
        border: 1px solid #cbd5e0;
        color: #2d3748;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-secondary:hover {
        background: #cbd5e0;
    }
    
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .modal.show {
        opacity: 1;
        visibility: visible;
    }
    
    .modal-content {
        background: white;
        border-radius: 12px;
        max-width: 800px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        transform: translateY(-20px);
        transition: transform 0.3s ease;
    }
    
    .modal.show .modal-content {
        transform: translateY(0);
    }
    
    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: between;
        align-items: center;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .modal-footer {
        padding: 20px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
    }
    
    .close-btn {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #718096;
        margin-left: auto;
    }
    
    .close-btn:hover {
        color: #2d3748;
    }
    
    .detail-section {
        margin-bottom: 20px;
    }
    
    .detail-section h4 {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 8px;
        font-size: 16px;
    }
    
    .detail-list {
        background: #f7fafc;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 12px;
    }
    
    .detail-list li {
        padding: 4px 0;
        display: flex;
        justify-content: space-between;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .detail-list li:last-child {
        border-bottom: none;
    }
    
    .amount-positive {
        color: #38a169;
        font-weight: 600;
    }
    
    .amount-negative {
        color: #e53e3e;
        font-weight: 600;
    }
    
    .amount-total {
        color: #2d3748;
        font-weight: 700;
        font-size: 16px;
    }
    
    .no-data {
        text-align: center;
        padding: 40px;
        color: #718096;
    }
    
    .action-buttons {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid #e2e8f0;
    }
    
    @media (max-width: 768px) {
        .payroll-table th,
        .payroll-table td {
            padding: 8px 12px;
            font-size: 12px;
        }
        
        .summary-grid {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
        }
    }
</style>
@stop

@section('content')
<div class="payroll-container">
    <div class="container-fluid">
        <div class="payroll-card">
            <div style="padding: 24px;">
                <h2 style="font-size: 20px; font-weight: 600; color: #2d3748; margin-bottom: 8px;">
                    Payroll Period: {{ $payrollPeriod->title }}
                </h2>
                <p style="color: #718096; margin-bottom: 24px;">
                    {{ \Carbon\Carbon::parse($payrollPeriod->from_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($payrollPeriod->to_date)->format('M d, Y') }}
                </p>

                @if (empty($payrollResults))
                    <div class="no-data">
                        <svg style="width: 64px; height: 64px; margin: 0 auto 16px; color: #cbd5e0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 style="font-size: 18px; color: #4a5568; margin-bottom: 8px;">No Payroll Data</h3>
                        <p>No payroll data has been computed for this period yet.</p>
                    </div>
                @else
                    <!-- Summary Card -->
                    <div class="summary-card">
                        <div style="display: flex; align-items: center; margin-bottom: 16px;">
                            <svg style="width: 24px; height: 24px; margin-right: 12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <h3 style="font-size: 18px; font-weight: 600;">Payroll Summary</h3>
                        </div>
                        <div class="summary-grid">
                            <div class="summary-item">
                                <div style="font-size: 14px; opacity: 0.9;">Total Employees</div>
                                <div style="font-size: 24px; font-weight: 700;">{{ count($payrollResults) }}</div>
                            </div>
                            <div class="summary-item">
                                <div style="font-size: 14px; opacity: 0.9;">Total Gross Pay</div>
                                <div style="font-size: 24px; font-weight: 700;">₱{{ number_format(array_sum(array_column($payrollResults, 'gross_pay')), 2) }}</div>
                            </div>
                            <div class="summary-item">
                                <div style="font-size: 14px; opacity: 0.9;">Total Net Pay</div>
                                <div style="font-size: 24px; font-weight: 700;">₱{{ number_format(array_sum(array_column($payrollResults, 'net_pay')), 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Payroll Table -->
                    <div class="table-container">
                        <table class="payroll-table">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Employee Name</th>
                                    <th>Basic Pay</th>
                                    <th>OT Pay</th>
                                    <th>Deductions</th>
                                    <th>Gross Pay</th>
                                    <th>Net Pay</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($payrollResults as $result)
                                    <tr>
                                        <td style="font-weight: 600;">{{ $result['employee_id'] }}</td>
                                        <td>{{ $result['employee_name'] }}</td>
                                        <td>₱{{ number_format($result['basic_hours_pay'], 2) }}</td>
                                        <td class="amount-positive">₱{{ number_format($result['overtime_pay'], 2) }}</td>
                                        <td class="amount-negative">(₱{{ number_format($result['total_deductions'], 2) }})</td>
                                        <td style="font-weight: 600;">₱{{ number_format($result['gross_pay'], 2) }}</td>
                                        <td class="amount-total">₱{{ number_format($result['net_pay'], 2) }}</td>
                                        <td>
                                            <button type="button" class="btn-primary view-details-btn"
                                                data-employee-id="{{ $result['employee_id'] }}"
                                                data-employee-name="{{ $result['employee_name'] }}"
                                                data-details="{{ json_encode($result) }}">
                                                View Details
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <a href="{{ route('HR.payroll.process') }}" class="btn-secondary">
                            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
                            Back to Payroll Periods
                        </a>
                        <button id="savePayrollBtn" class="btn-primary" style="padding: 12px 24px;">
                            <i class="fas fa-save" style="margin-right: 8px;"></i>
                            Save Payroll
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Employee Details Modal -->
<div id="employeeDetailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalEmployeeName" style="font-size: 20px; font-weight: 600; color: #2d3748;"></h3>
            <button class="close-btn close-modal-btn">&times;</button>
        </div>
        <div class="modal-body">
            <div id="modalEmployeeDetails"></div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary close-modal-btn">Close</button>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('employeeDetailsModal');
    const modalEmployeeName = document.getElementById('modalEmployeeName');
    const modalEmployeeDetails = document.getElementById('modalEmployeeDetails');
    const closeButtons = document.querySelectorAll('.close-modal-btn');
    const viewDetailButtons = document.querySelectorAll('.view-details-btn');
    const savePayrollBtn = document.getElementById('savePayrollBtn');

    // Fixed: Directly embed PHP data into JavaScript
    let payrollResultsData = @json($payrollResults ?? []);

    // Debug: Log the data to console to verify it's loaded
    console.log('Payroll Results Data:', payrollResultsData);

    function openModal(employeeName, details) {
        modalEmployeeName.textContent = `Payroll Details - ${employeeName}`;
        
        let detailsHtml = `
            <div class="detail-section">
                <h4>📅 Payroll Period</h4>
                <div class="detail-list">
                    <div style="padding: 8px 0; color: #4a5568;">
                        ${details.payroll_start_date} to ${details.payroll_end_date}
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h4>💰 Rate Information</h4>
                <div class="detail-list">
                    <ul style="list-style: none; margin: 0; padding: 0;">
                        <li><span>Daily Rate:</span> <span>₱${details.daily_rate.toFixed(2)}</span></li>
                        <li><span>Hourly Rate:</span> <span>₱${details.hourly_rate.toFixed(2)}</span></li>
                    </ul>
                </div>
            </div>

            <div class="detail-section">
                <h4>⏰ Attendance & Hours</h4>
                <div class="detail-list">
                    <ul style="list-style: none; margin: 0; padding: 0;">
                        <li><span>Total Hours Worked:</span> <span>${details.total_hours_worked} hrs</span></li>
                        <li><span>Total Late:</span> <span class="amount-negative">${details.total_late_minutes} mins</span></li>
                        <li><span>Total Undertime:</span> <span class="amount-negative">${details.total_undertime_minutes} mins</span></li>
                    </ul>
                </div>
            </div>

            <div class="detail-section">
                <h4>🌙 Overtime & Special Hours</h4>
                <div class="detail-list">
                    <ul style="list-style: none; margin: 0; padding: 0;">
                        <li><span>Approved Overtime:</span> <span>${details.total_approved_overtime_hours} hrs</span></li>
                        <li><span>Regular Holiday Hours:</span> <span>${details.total_reg_holiday_hours} hrs</span></li>
                        <li><span>Special Holiday Hours:</span> <span>${details.total_spec_holiday_hours} hrs</span></li>
                        <li><span>Night Differential Hours:</span> <span>${details.total_night_diff_hours} hrs</span></li>
                        <li><span>Night Diff (Reg. Holiday):</span> <span>${details.total_night_diff_reg_hours} hrs</span></li>
                        <li><span>Night Diff (Spec. Holiday):</span> <span>${details.total_night_diff_spec_hours} hrs</span></li>
                    </ul>
                </div>
            </div>

            <div class="detail-section">
                <h4>💵 Earnings Breakdown</h4>
                <div class="detail-list">
                    <ul style="list-style: none; margin: 0; padding: 0;">
                        <li><span>Basic Hours Pay:</span> <span class="amount-positive">₱${details.basic_hours_pay.toFixed(2)}</span></li>
                        <li><span>Regular OT Pay:</span> <span class="amount-positive">₱${details.regular_overtime_sub_pay.toFixed(2)}</span></li>
                        <li><span>Regular Holiday Pay:</span> <span class="amount-positive">₱${details.regular_holiday_pay.toFixed(2)}</span></li>
                        <li><span>Special Holiday Pay:</span> <span class="amount-positive">₱${details.special_holiday_pay.toFixed(2)}</span></li>
                        <li><span>Night Differential Pay:</span> <span class="amount-positive">₱${details.night_differential_pay.toFixed(2)}</span></li>
                        <li style="border-top: 2px solid #e2e8f0; margin-top: 8px; padding-top: 8px;">
                            <span><strong>Total OT/Holiday Pay:</strong></span> 
                            <span class="amount-total">₱${details.overtime_pay.toFixed(2)}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="detail-section">
                <h4>📉 Deductions Breakdown</h4>
                <div class="detail-list">
                    <ul style="list-style: none; margin: 0; padding: 0;">
                        <li><span>Late Deduction:</span> <span class="amount-negative">₱${details.late_deduction.toFixed(2)}</span></li>
                        <li><span>Undertime Deduction:</span> <span class="amount-negative">₱${details.undertime_deduction.toFixed(2)}</span></li>
                        <li><span>SSS Contribution:</span> <span class="amount-negative">₱${details.sss_contribution.toFixed(2)}</span></li>
                        <li><span>PhilHealth:</span> <span class="amount-negative">₱${details.philhealth_contribution.toFixed(2)}</span></li>
                        <li><span>Pag-IBIG:</span> <span class="amount-negative">₱${details.pagibig_contribution.toFixed(2)}</span></li>
                        <li><span>Tax Withheld:</span> <span class="amount-negative">₱${details.tax_withheld.toFixed(2)}</span></li>
                        <li><span>Other Deductions:</span> <span class="amount-negative">₱${details.other_deductions.toFixed(2)}</span></li>
                        <li style="border-top: 2px solid #e2e8f0; margin-top: 8px; padding-top: 8px;">
                            <span><strong>Total Deductions:</strong></span> 
                            <span class="amount-total amount-negative">₱${details.total_deductions.toFixed(2)}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="detail-section">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 16px; border-radius: 8px; text-align: center;">
                    <div style="margin-bottom: 8px;">
                        <span style="font-size: 16px; opacity: 0.9;">GROSS PAY</span><br>
                        <span style="font-size: 24px; font-weight: 700;">₱${details.gross_pay.toFixed(2)}</span>
                    </div>
                    <div style="border-top: 1px solid rgba(255,255,255,0.3); padding-top: 8px;">
                        <span style="font-size: 16px; opacity: 0.9;">NET PAY</span><br>
                        <span style="font-size: 28px; font-weight: 700;">₱${details.net_pay.toFixed(2)}</span>
                    </div>
                </div>
            </div>
        `;
        
        modalEmployeeDetails.innerHTML = detailsHtml;
        modal.classList.add('show');
        document.body.style.overflow = 'auto';
    }

    function closeModal() {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }

    viewDetailButtons.forEach(button => {
        button.addEventListener('click', function () {
            const employeeName = this.dataset.employeeName;
            const details = JSON.parse(this.dataset.details);
            openModal(employeeName, details);
        });
    });

    closeButtons.forEach(button => {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    
    if (savePayrollBtn) {
    savePayrollBtn.addEventListener('click', function () {
        // Check if payrollResultsData is available and not empty
        if (!payrollResultsData || payrollResultsData.length === 0) {
            alert('No payroll data available to save. Please compute payroll first.');
            console.log('Payroll data check failed:', payrollResultsData);
            return;
        }

        // Debug: Log the data being sent
        console.log('Sending payroll data:', payrollResultsData);

        if (confirm('Are you sure you want to save this payroll? This action cannot be undone.')) {
            // Show loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Saving...';
            this.disabled = true;

            fetch('/payroll/save/{{ $payrollPeriod->id }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json', // Important: Tell server we expect JSON
                },
                body: JSON.stringify({ payroll_results: payrollResultsData })
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                // Check if response is HTML (error page) instead of JSON
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('text/html')) {
                    throw new Error('Server returned HTML instead of JSON. This usually indicates a server error.');
                }
                
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Server error response:', text);
                        throw new Error(`Server responded with status ${response.status}: ${text.substring(0, 200)}...`);
                    });
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Success response:', data);
                if (data.success) {
                    alert('Payroll saved successfully!');
                    window.location.href = "{{ route('HR.payroll.process') }}";
                } else {
                    alert('Error saving payroll: ' + data.message);
                    // Reset button
                    this.innerHTML = '<i class="fas fa-save" style="margin-right: 8px;"></i>Save Payroll';
                    this.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error details:', error);
                alert('An error occurred while saving payroll: ' + error.message);
                // Reset button
                this.innerHTML = '<i class="fas fa-save" style="margin-right: 8px;"></i>Save Payroll';
                this.disabled = false;
            });
        }
    });
    }
});
</script>
@stop 