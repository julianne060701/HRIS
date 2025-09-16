@extends('adminlte::page')

@section('title', 'Batch Payslip Printing')

@section('content_header')
<h1 class="text-2xl font-semibold text-gray-800">Batch Payslip Printing</h1>
@stop

@section('css')
<style>
body {
    font-family: 'Arial', sans-serif;
}

.batch-print-container {
    max-width: 100%;
    margin: 0 auto;
    background: #ffffff;
    padding: 20px;
    text-align: center;
}

.payslip-container {
    width: 127mm; /* 5 inches */
    height: 178mm; /* 7 inches */
    margin: 15px auto;
    background: #ffffff;
    padding: 8px;
    border: 2px solid #000;
    font-size: 10px;
    line-height: 1.2;
    page-break-inside: avoid;
    display: block;
    overflow: hidden;
}

.header-section {
    text-align: center;
    margin-bottom: 8px;
    border-bottom: 1px solid #000;
    padding-bottom: 5px;
}

.header-section h2 {
    font-size: 12px;
    font-weight: bold;
    margin: 0;
    text-transform: uppercase;
}

.header-section p {
    margin: 2px 0 0;
    font-size: 8px;
}

.payslip-main {
    display: flex;
    gap: 8px;
}

.left-column {
    flex: 1;
}

.right-column {
    flex: 1;
}

.payslip-section {
    margin-bottom: 8px;
}

.section-title {
    font-weight: bold;
    font-size: 9px;
    margin-bottom: 3px;
    text-transform: uppercase;
    border-bottom: 1px solid #000;
    padding-bottom: 1px;
}

.payslip-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.payslip-list li {
    display: flex;
    justify-content: space-between;
    padding: 0.5px 0;
    font-size: 8px;
}

.totals-section {
    border-top: 1px solid #000;
    margin-top: 8px;
    padding-top: 5px;
}

.totals-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-weight: bold;
}

.signature-section {
    margin-top: 20px;
    text-align: center;
}

.btn-container {
    text-align: center;
    margin: 20px 0;
}

.print-btn {
    background-color: #1a73e8;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    margin: 0 10px;
}

.print-btn:hover {
    background-color: #155aab;
}

.pdf-btn {
    background-color: #dc3545;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    margin: 0 10px;
}

.pdf-btn:hover {
    background-color: #c82333;
}

.back-btn {
    background-color: #6c757d;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.back-btn:hover {
    background-color: #5a6268;
    color: white;
    text-decoration: none;
}

.print-hide {
    display: block;
}

/* PRINT MODE */
@media print {
    body,
    html {
        background: white !important;
    }

    .main-header,
    .main-sidebar,
    .main-footer,
    .content-header,
    .content-wrapper>*:not(.content) {
        display: none !important;
    }

    body * {
        visibility: hidden;
    }

    .batch-print-container,
    .batch-print-container * {
        visibility: visible;
    }

    .batch-print-container {
        position: relative;
        width: 100%;
        margin: 0;
        padding: 0 !important;
    }

    .payslip-container {
        width: 127mm; /* 5 inches */
        height: 178mm; /* 7 inches */
        margin: 0 auto;
        padding: 5mm !important;
        page-break-inside: avoid;
        font-size: 10px !important;
        position: relative;
        border: 1px solid #000;
        page-break-after: always;
        overflow: hidden;
        display: block;
    }

    .payslip-container:last-child {
        page-break-after: auto;
    }

    .btn-container {
        display: none !important;
    }
    
    .pdf-btn {
        display: none !important;
    }

    .print-hide {
        display: none !important;
    }

    * {
        color: black !important;
        background-color: white !important;
    }

    @page {
        size: 5R;
        margin: 0;
    }
    
    /* Force each payslip to its own page */
    .payslip-container {
        page-break-before: always;
    }
    
    .payslip-container:first-child {
        page-break-before: auto;
    }
}
</style>
@stop

@section('content')
<div class="batch-print-container">
    @if ($message)
    <div class="alert alert-warning">
        {{ $message }}
    </div>
    @else
    <div class="btn-container">
        <button onclick="window.print()" class="print-btn">
            <i class="fas fa-print"></i> Print All Payslips
        </button>
        <button onclick="saveAsPDF()" class="pdf-btn">
            <i class="fas fa-file-pdf"></i> Save as PDF
        </button>
        <a href="{{ route('HR.payslip.batch') }}" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Batch List
        </a>
    </div>

    @if ($payslips->isEmpty())
    <div class="alert alert-info">
        <h4>No payslips found for this payroll period.</h4>
    </div>
    @else
    <div class="mb-4 print-hide">
        <h3>Payroll Period: {{ $payroll->title }}</h3>
        <p><strong>Cutoff:</strong> {{ $payroll->from_date->format('M d, Y') }} to {{ $payroll->to_date->format('M d, Y') }}</p>
        <p><strong>Total Payslips:</strong> {{ $payslips->count() }}</p>
    </div>

    @foreach ($payslips as $payslip)
    @php
        $employee = $payslip->employee;
        $dailyrate = ($employee->salary ?? 0) / 22;
        $hourlyrate = $dailyrate / 8;
        $otherIncome = ($payslip->overtime_pay ?? 0)
            + ($payslip->night_differential_pay ?? 0)
            + ($payslip->regular_holiday_pay ?? 0)
            + ($payslip->special_holiday_pay ?? 0);
    @endphp
    <div class="payslip-container">
        <div class="header-section">
            <h2>{{ strtoupper($employee->company_name ?? 'COMPANY NAME') }}</h2>
            <h2>PAY SLIP</h2>
            <p>Payroll Date: {{ $payslip->payroll->to_date->format('m/d/Y') }}</p>
        </div>

        <div style="text-align: right; margin-bottom: 5px;">
            <div style="border: 1px solid #000; padding: 3px; display: inline-block;">
                <div style="font-size: 10px; font-weight: bold;">{{ number_format($payslip->net_pay, 2) }}</div>
                <div style="font-size: 7px;">TOTAL COMPENSATION</div>
            </div>
        </div>

        <div style="margin-bottom: 5px;">
            <strong style="font-size: 10px;">{{ strtoupper($employee->first_name . ' ' . $employee->last_name) }}</strong><br>
            <span style="font-size: 7px;">{{ $employee->employee_id ?? '210033' }},
                {{ $employee->department ?? 'HOSPITAL INFORMATION MANAGEMENT SYSTEM' }}</span>
        </div>

        <div class="payslip-main">
            <div class="left-column">
                <div class="payslip-section">
                    <div class="section-title">WORK DONE</div>
                    <ul class="payslip-list">
                        <li><span>BASIC PAY</span> <span>11D {{ number_format($dailyrate * 11, 2) }}</span></li>
                        <li><span>REST DAY</span> <span></span></li>
                        <li><span>LEG HOL</span> <span></span></li>
                        <li><span>RD</span> <span></span></li>
                        <li><span>LEG HOL + RD</span> <span></span></li>
                        <li><span>SPCL HOL + RD</span> <span></span></li>
                        <li><span>OT REG</span> <span></span></li>
                        <li><span>OT RD</span> <span></span></li>
                        <li><span>OT LEG HOL</span> <span></span></li>
                        <li><span>OT SPCL HOL</span> <span></span></li>
                        <li><span>OT LEG HOL + RD</span> <span></span></li>
                        <li><span>OT SPCL HOL + RD</span> <span></span></li>
                        <li><span>NIGHT PREMIUM</span> <span></span></li>
                        <li><span>ND LEGAL</span> <span>-</span></li>
                        <li><span>ND SPECIAL</span> <span></span></li>
                        <li><span>ND+OT_RD</span> <span>0.00</span></li>
                        <li><span>UNDERTIME</span> <span>({{ number_format($payslip->undertime_deduction, 2) }})</span></li>
                        <li><span>TARDY</span>
                            <span>({{ number_format($payslip->late_deduction, 2) }})</span>
                        </li>
                        <li><span>ABSENT</span> <span>{{ number_format(($payslip->absent_deduction ?? 0), 2) }}</span></li>
                    </ul>
                    <div style="border-top: 1px solid #000; margin-top: 5px; padding-top: 5px; font-weight: bold;">
                        <div style="text-align: right;">{{ number_format($payslip->gross_pay, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="right-column">
                <div style="text-align: right; margin-bottom: 10px;">
                    <div>Payroll Date : {{ $payslip->payroll->to_date->format('m/d/Y') }}</div>
                </div>

                <div style="border: 1px solid #000; padding: 3px; margin-bottom: 5px; text-align: right;">
                    <div style="font-size: 10px; font-weight: bold;">{{ number_format($payslip->gross_pay - $payslip->total_deductions, 2) }}</div>
                    <div style="font-size: 7px;">TOTAL DEDUCTIONS</div>
                </div>

                <div class="payslip-section">
                    <div class="section-title">OTHER INCOME</div>
                    <ul class="payslip-list">
                        <li><span>OVERTIME PAY</span> <span>{{ number_format(($payslip->overtime_pay ?? 0), 2) }}</span></li>
                        <li><span>NIGHT DIFF</span> <span>{{ number_format(($payslip->night_differential_pay ?? 0), 2) }}</span></li>
                        <li><span>REG HOLIDAY</span> <span>{{ number_format(($payslip->regular_holiday_pay ?? 0), 2) }}</span></li>
                        <li><span>SPECIAL HOLIDAY</span> <span>{{ number_format(($payslip->special_holiday_pay ?? 0), 2) }}</span></li>
                    </ul>
                    <div style="border-top: 1px solid #000; margin-top: 5px; padding-top: 5px; text-align: right; font-weight: bold;">
                        {{ number_format($otherIncome, 2) }}
                    </div>
                </div>

                <div class="payslip-section">
                    <div class="section-title">BASIC DEDUCTIONS</div>
                    <ul class="payslip-list">
                        <li><span>PAG-IBIG PREM.</span> <span>{{ number_format(($payslip->pagibig_contribution ?? 0), 2) }}</span></li>
                        <li><span>PHIC PREM.</span> <span>{{ number_format(($payslip->philhealth_contribution ?? 0), 2) }}</span></li>
                        <li><span>SSS PREM.</span> <span>{{ number_format(($payslip->sss_contribution ?? 0), 2) }}</span></li>
                        <li><span>WITHHOLDING TAX</span> <span>{{ number_format(($payslip->tax_withheld ?? 0), 2) }}</span></li>
                    </ul>
                    <div style="border-top: 1px solid #000; margin-top: 5px; padding-top: 5px; text-align: right; font-weight: bold;">
                        {{ number_format(($payslip->sss_contribution ?? 0) + ($payslip->philhealth_contribution ?? 0) + ($payslip->pagibig_contribution ?? 0) + ($payslip->tax_withheld ?? 0), 2) }}
                    </div>
                </div>

                <div class="payslip-section">
                    <div class="section-title">OTHER DEDUCTIONS</div>
                    <ul class="payslip-list">
                        <li><span>OTHERS</span> <span>{{ number_format(($payslip->other_deductions ?? 0), 2) }}</span></li>
                        <li><span>LOANS</span> <span>{{ number_format(($payslip->loans ?? 0), 2) }}</span></li>
                    </ul>
                </div>

                <div style="border: 1px solid #000; padding: 3px; margin-bottom: 5px; text-align: right;">
                    <div style="font-size: 10px; font-weight: bold;">{{ number_format($payslip->net_pay, 2) }}</div>
                    <div style="font-size: 7px;">NET TAKE HOME PAY</div>
                </div>
            </div>
        </div>

        <div class="totals-section">
            <div style="display: flex; justify-content: space-between; font-size: 9px;">
                <div><strong>GROSS PAY</strong></div>
                <div><strong>{{ number_format($payslip->gross_pay, 2) }}</strong></div>
                <div><strong>DEDUCTIONS</strong></div>
                <div><strong>{{ number_format($payslip->total_deductions, 2) }}</strong></div>
                <div><strong>NET PAY</strong></div>
                <div><strong>{{ number_format($payslip->net_pay, 2) }}</strong></div>
            </div>
            <div style="text-align: center; font-size: 7px; margin-top: 3px;">11D</div>
        </div>

        <div class="signature-section">
            <p style="font-size: 7px; margin: 5px 0;">I acknowledge to have received the amount stated above and have no
                further claims for services rendered.</p>

            <div style="margin-top: 8px;">
                <div style="text-align: center;">
                    <div style="font-weight: bold; font-size: 9px;">
                        {{ strtoupper($employee->first_name . ', ' . $employee->last_name) }}</div>
                    <div style="font-size: 7px;">Employee's Signature</div>
                </div>

                <div style="margin-top: 10px; border-top: 1px solid #000; width: 100px; margin-left: auto; margin-right: auto;">
                    <div style="font-size: 7px; text-align: center; margin-top: 3px;">Date Received</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    @endif
    @endif
</div>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
// Auto-print when page loads (optional)
// window.onload = function() {
//     window.print();
// };

function saveAsPDF() {
    const { jsPDF } = window.jspdf;
    const payslips = document.querySelectorAll('.payslip-container');
    
    if (payslips.length === 0) {
        alert('No payslips to save as PDF');
        return;
    }
    
    // Show loading message
    const loadingDiv = document.createElement('div');
    loadingDiv.innerHTML = '<div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.8); color: white; padding: 20px; border-radius: 5px; z-index: 9999;">Generating PDF... Please wait</div>';
    document.body.appendChild(loadingDiv);
    
    // Create PDF with 5x7 inch pages (127mm x 178mm)
    const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: [127, 178] // 5x7 inches
    });
    
    let promises = [];
    
    payslips.forEach((payslip, index) => {
        const promise = html2canvas(payslip, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            width: payslip.offsetWidth,
            height: payslip.offsetHeight
        }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            
            if (index > 0) {
                pdf.addPage([127, 178], 'mm'); // Add new page for each payslip
            }
            
            // Calculate dimensions to fit 5x7 inches
            const imgWidth = 117; // 127mm - 10mm margins
            const imgHeight = 168; // 178mm - 10mm margins
            const x = 5; // 5mm margin
            const y = 5; // 5mm margin
            
            pdf.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight);
        });
        
        promises.push(promise);
    });
    
    Promise.all(promises).then(() => {
        // Remove loading message
        document.body.removeChild(loadingDiv);
        
        // Generate filename with current date
        const now = new Date();
        const dateStr = now.toISOString().split('T')[0];
        const filename = `payslips_${dateStr}.pdf`;
        
        // Save the PDF
        pdf.save(filename);
    }).catch(error => {
        console.error('Error generating PDF:', error);
        document.body.removeChild(loadingDiv);
        alert('Error generating PDF. Please try again.');
    });
}
</script>
@stop