@extends('adminlte::page')

@section('title', 'Employee Payslip')

@section('content_header')
<h1 class="text-2xl font-semibold text-gray-800">Employee Payslip</h1>
@stop

@section('css')
<style>
body {
    font-family: 'Arial', sans-serif;
}

.payslip-container {
    max-width: 600px;
    margin: 20px auto;
    background: #ffffff;
    padding: 20px;
    border: 2px solid #000;
    font-size: 11px;
    line-height: 1.2;
}

.header-section {
    text-align: center;
    margin-bottom: 15px;
    border-bottom: 1px solid #000;
    padding-bottom: 10px;
}

.header-section h2 {
    font-size: 14px;
    font-weight: bold;
    margin: 0;
    text-transform: uppercase;
}

.header-section p {
    margin: 3px 0 0;
    font-size: 10px;
}

.payslip-main {
    display: flex;
    gap: 20px;
}

.left-column {
    flex: 1;
}

.right-column {
    flex: 1;
}

.payslip-section {
    margin-bottom: 15px;
}

.section-title {
    font-weight: bold;
    font-size: 11px;
    margin-bottom: 5px;
    text-transform: uppercase;
    border-bottom: 1px solid #000;
    padding-bottom: 2px;
}

.payslip-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.payslip-list li {
    display: flex;
    justify-content: space-between;
    padding: 1px 0;
    font-size: 10px;
}

.rate-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.rate-info div {
    text-align: center;
    flex: 1;
}

.rate-info .rate-label {
    font-size: 9px;
    font-weight: bold;
}

.rate-info .rate-value {
    font-size: 10px;
    border: 1px solid #000;
    padding: 2px;
    margin-top: 2px;
}

.totals-section {
    border-top: 2px solid #000;
    margin-top: 20px;
    padding-top: 10px;
}

.totals-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-weight: bold;
}

.totals-row.final {
    border: 2px solid #000;
    padding: 5px;
    font-size: 12px;
}

.signature-section {
    margin-top: 20px;
    text-align: center;
}

.signature-box {
    border: 1px solid #000;
    padding: 30px 10px 10px;
    margin: 10px auto;
    width: 200px;
}

.btn-container {
    text-align: center;
    margin-top: 15px;
}

.print-btn {
    background-color: #1a73e8;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
}

.print-btn:hover {
    background-color: #155aab;
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

    .payslip-container,
    .payslip-container * {
        visibility: visible;
    }

    .payslip-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        box-shadow: none;
        border: 2px solid black !important;
        padding: 15px !important;
    }

    .btn-container {
        display: none !important;
    }

    * {
        color: black !important;
        background-color: white !important;
    }

    @page {
        size: A5;
        margin: 5mm;
    }
}
</style>
@stop

@section('content')
<div class="payslip-container">
    <div class="header-section">
        <h2>{{ strtoupper($payslip->employee->company_name ?? 'COMPANY NAME') }}</h2>
        <h2>PAY SLIP</h2>
        <p>Payroll Date: {{ $payslip->payroll->to_date->format('m/d/Y') }}</p>
    </div>

    <div style="text-align: right; margin-bottom: 10px;">
        <div style="border: 1px solid #000; padding: 5px; display: inline-block;">
            <div>{{ number_format($payslip->net_pay, 2) }}</div>
            <div style="font-size: 9px;">TOTAL COMPENSATION</div>
        </div>
    </div>

    <div style="margin-bottom: 10px;">
        <strong>{{ strtoupper($payslip->employee->first_name . ' ' . $payslip->employee->last_name) }}</strong><br>
        <span style="font-size: 9px;">{{ $payslip->employee->employee_id ?? '210033' }},
            {{ $payslip->employee->department ?? 'HOSPITAL INFORMATION MANAGEMENT SYSTEM' }}</span>
    </div>

    <div class="payslip-main">
        <div class="left-column">
            <div class="payslip-section">
                <div class="section-title">WORK DONE</div>
                <ul class="payslip-list">
                    {{-- Use data from the payslip object. Handle cases where the value might not be available. --}}
                    <li><span>BASIC PAY</span> <span>{{ number_format($payslip->basic_hours_pay, 2) }}</span></li>
                    <li><span>REST DAY</span> <span>{{ number_format($payslip->rest_day_pay, 2) }}</span></li>
                    <li><span>LEG HOL</span> <span>{{ number_format($payslip->regular_holiday_pay, 2) }}</span></li>
                    <li><span>SPCL HOL</span> <span>{{ number_format($payslip->special_holiday_pay, 2) }}</span></li>
                    <li><span>LEG HOL + RD</span> <span>{{ number_format($payslip->rest_reg_pay, 2) }}</span></li>
                    <li><span>SPCL HOL + RD</span> <span>{{ number_format($payslip->rest_spec_pay, 2) }}</span></li>
                    <li><span>OT REG</span> <span>{{ number_format($payslip->overtime_pay, 2) }}</span></li>
                    <li><span>OT RD</span> <span>{{ number_format($payslip->rest_ot_pay, 2) }}</span></li>
                    <li><span>OT LEG HOL</span> <span>{{ number_format($payslip->ot_reg_holiday_pay, 2) }}</span></li>
                    <li><span>OT SPCL HOL</span> <span>{{ number_format($payslip->ot_spec_holiday_pay, 2) }}</span></li>
                    <li><span>OT LEG HOL + RD</span>
                        <span>{{ number_format($payslip->ot_rdr_reg_holiday_pay, 2) }}</span>
                    </li>
                    <li><span>OT SPCL HOL + RD</span>
                        <span>{{ number_format($payslip->ot_rdr_spec_holiday_pay, 2) }}</span>
                    </li>
                    <li><span>NIGHT PREMIUM</span> <span>{{ number_format($payslip->night_differential_pay, 2) }}</span>
                    </li>
                    <li><span>ND LEGAL</span> <span>{{ number_format($payslip->night_differential_pay_reg, 2) }}</span>
                    </li>
                    <li><span>ND SPECIAL</span>
                        <span>{{ number_format($payslip->night_differential_pay_spec, 2) }}</span>
                    </li>
                    <li><span>ND+OT_RD</span> <span>{{ number_format($payslip->ot_night_diff_rdr_pay, 2) }}</span></li>
                    <li><span>UNDERTIME</span> <span>({{ number_format($payslip->undertime_deduction, 2) }})</span></li>
                    <li><span>TARDY</span> <span>({{ number_format($payslip->late_deduction, 2) }})</span></li>
                    <li><span>ABSENT</span> <span>({{ number_format($payslip->absent_deduction, 2) }})</span></li>
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

            <div style="border: 1px solid #000; padding: 5px; margin-bottom: 10px; text-align: right;">
                <div>{{ number_format($payslip->gross_pay - $payslip->total_deductions, 2) }}</div>
                <div style="font-size: 9px;">TOTAL DEDUCTIONS</div>
            </div>

            <div class="payslip-section">
                <div class="section-title">OTHER INCOME</div>
                <ul class="payslip-list">
                    <li><span></span> <span>0.00</span></li>
                </ul>
            </div>

            <div class="payslip-section">
                <div class="section-title">BASIC DEDUCTIONS</div>
                <ul class="payslip-list">
                    <li><span>PAG-IBIG PREM.</span> <span>{{ number_format($payslip->pagibig_contribution, 2) }}</span>
                    </li>
                    <li><span>PHIC PREM.</span> <span>{{ number_format($payslip->philhealth_contribution, 2) }}</span>
                    </li>
                    <li><span>SSS PREM.</span> <span>{{ number_format($payslip->sss_contribution, 2) }}</span></li>
                </ul>
                <div
                    style="border-top: 1px solid #000; margin-top: 5px; padding-top: 5px; text-align: right; font-weight: bold;">
                    {{ number_format($payslip->sss_contribution + $payslip->philhealth_contribution + $payslip->pagibig_contribution, 2) }}
                </div>
            </div>

            <div class="payslip-section">
                <div class="section-title">OTHER DEDUCTIONS</div>
                <ul class="payslip-list">
                    <li><span></span> <span>{{ number_format($payslip->other_deductions, 2) }}</span></li>
                </ul>
            </div>

            <div style="border: 1px solid #000; padding: 5px; margin-bottom: 10px; text-align: right;">
                <div>{{ number_format($payslip->net_pay, 2) }}</div>
                <div style="font-size: 9px;">NET TAKE HOME PAY</div>
            </div>
        </div>
    </div>

    <div class="totals-section">
        <div style="display: flex; justify-content: space-between;">
            <div><strong>GROSS PAY</strong></div>
            <div><strong>{{ number_format($payslip->gross_pay, 2) }}</strong></div>
            <div><strong>DEDUCTIONS</strong></div>
            <div><strong>{{ number_format($payslip->total_deductions, 2) }}</strong></div>
            <div><strong>NET PAY</strong></div>
            <div><strong>{{ number_format($payslip->net_pay, 2) }}</strong></div>
        </div>
        <div style="text-align: center; font-size: 9px; margin-top: 5px;">11D</div>
    </div>

    <div class="signature-section">
        <p style="font-size: 9px; margin: 10px 0;">I acknowledge to have received the amount stated above and have no
            further claims for services rendered.</p>

        <div style="margin-top: 20px;">
            <div style="text-align: center;">
                <div style="font-weight: bold;">
                    {{ strtoupper($payslip->employee->first_name . ', ' . $payslip->employee->last_name) }}</div>
                <div style="font-size: 9px;">Employee's Signature</div>
            </div>

            <div
                style="margin-top: 30px; border-top: 1px solid #000; width: 200px; margin-left: auto; margin-right: auto;">
                <div style="font-size: 9px; text-align: center; margin-top: 5px;">Date Received</div>
            </div>
        </div>
    </div>

    <div class="btn-container">
        <button onclick="window.print()" class="print-btn">
            <i class="fas fa-print" style="margin-right: 8px;"></i> Print Payslip
        </button>
    </div>
</div>
@stop

@section('js')
<script>
//Optional JavaScript if you need more complex logic, but for printing, the onclick attribute is sufficient.
</script>
@stop