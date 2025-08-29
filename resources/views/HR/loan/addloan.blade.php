@extends('adminlte::page')

@section('title', 'Loan Information')

@section('plugins.Datatables', true)
@section('plugins.Select2', true)

@section('css')
    <style>
        .select2-container {
            width: 100% !important;
        }
        #loan-calculation-results {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 15px;
            margin-top: 15px;
        }
        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }
    </style>
@stop

@section('content_header')
    <h1>LOAN INFORMATION</h1>
@endsection

@section('content')
    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Error Messages for Validation --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Loans Card --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Loan Records</h3>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addLoanModal">
                <i class="fas fa-plus"></i> Add Loan
            </button>
        </div>
        <div class="card-body">
            <table id="loan-table" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Loan Type</th>
                        <th>Original Amount</th>
                        <th>Current Balance</th>
                        <th>Monthly Payment</th>
                        <th>Number of Terms</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($loans as $index => $loan)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $loan->employee->employee_id ?? 'N/A' }}</td>
                            <td>
                                @if($loan->employee)
                                    {{ $loan->employee->first_name }}
                                    @if($loan->employee->middle_name)
                                        {{ substr($loan->employee->middle_name, 0, 1) }}.
                                    @endif
                                    {{ $loan->employee->last_name }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $loan->loan_type ?? 'N/A' }}</td>
                            <td>₱{{ number_format($loan->original_amount, 2) }}</td>
                            <td>₱{{ number_format($loan->balance, 2) }}</td>
                            <td>₱{{ number_format($loan->amortization_amount, 2) }}</td>
                            <td>{{ $loan->numer_terms ?? 0 }}</td>
                            <td>
                                <span class="badge badge-{{ $loan->status === 'active' ? 'success' : ($loan->status === 'completed' ? 'info' : 'danger') }}">
                                    {{ ucfirst($loan->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Loan Modal --}}
    <div class="modal fade" id="addLoanModal" tabindex="-1" role="dialog" aria-labelledby="addLoanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form action="{{ route('loan.store') }}" method="POST" id="loanForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addLoanModalLabel">Add New Loan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employee_id">Employee <span class="text-danger">*</span></label>
                                    <select name="employee_id" id="employee_id" class="form-control select2-employee @error('employee_id') is-invalid @enderror" required>
                                        <option value="">Select Employee</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->employee_id }}" {{ old('employee_id') == $employee->employee_id ? 'selected' : '' }}>
                                                {{ $employee->first_name }}
                                                @if($employee->middle_name)
                                                    {{ substr($employee->middle_name, 0, 1) }}.
                                                @endif
                                                {{ $employee->last_name }}
                                                ({{ $employee->employee_id }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="loan_type">Loan Type <span class="text-danger">*</span></label>
                                    <select name="loan_type" id="loan_type" class="form-control @error('loan_type') is-invalid @enderror" required>
                                        <option value="">Select Loan Type</option>
                                        <option value="SSS Salary Loan" {{ old('loan_type') == 'SSS Salary Loan' ? 'selected' : '' }}>SSS Salary Loan</option>
                                        <option value="PAG-IBIG Salary Loan" {{ old('loan_type') == 'PAG-IBIG Salary Loan' ? 'selected' : '' }}>PAG-IBIG Salary Loan</option>
                                        <option value="SSS Calamity Loan" {{ old('loan_type') == 'SSS Calamity Loan' ? 'selected' : '' }}>SSS Calamity Loan</option>
                                        <option value="Other" {{ old('loan_type') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('loan_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="original_amount">Loan Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₱</span>
                                        </div>
                                        <input type="number" name="original_amount" id="original_amount" 
                                               class="form-control @error('original_amount') is-invalid @enderror" 
                                               value="{{ old('original_amount') }}" 
                                               step="0.01" min="1" required>
                                    </div>
                                    @error('original_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amortization_amount">Monthly Amortization <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₱</span>
                                        </div>
                                        <input type="number" name="amortization_amount" id="amortization_amount" 
                                               class="form-control @error('amortization_amount') is-invalid @enderror" 
                                               value="{{ old('amortization_amount') }}" 
                                               step="0.01" min="1" required>
                                    </div>
                                    @error('amortization_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" 
                                           class="form-control @error('start_date') is-invalid @enderror" 
                                           value="{{ old('start_date') }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Number of Terms (Auto-calculated)</label>
                                    <div class="form-control-plaintext bg-light p-2 rounded">
                                        <span id="calculated-terms">Enter loan amount and monthly amortization</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Loan Calculation Results --}}
                        <div id="loan-calculation-results" style="display: none;">
                            <h6><i class="fas fa-calculator"></i> Loan Summary</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Number of Payments:</small>
                                    <div class="h5 text-primary" id="number-of-terms">0</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">End Date:</small>
                                    <div class="h5 text-info" id="end-date">-</div>
                                </div>
                            </div>
                        </div>

                        {{-- Hidden fields for calculated values --}}
                        <input type="hidden" name="numer_terms" id="numer_terms_hidden">
                        <input type="hidden" name="balance" id="balance_hidden">
                        <input type="hidden" name="end_date" id="end_date_hidden">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-info" id="calculate-loan">Calculate Loan</button>
                        <button type="submit" class="btn btn-success" id="save-loan" disabled>Save Loan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('adminlte_js')
    <script>
        $(document).ready(function () {
            // Initialize DataTable
            $('#loan-table').DataTable({
                responsive: true,
                pageLength: 25,
                order: [[0, 'desc']]
            });

            // Initialize Select2
            $('.select2-employee').select2({
                placeholder: "Search for an Employee",
                allowClear: true
            });

            // Re-initialize Select2 when modal is shown
            $('#addLoanModal').on('shown.bs.modal', function () {
                $('.select2-employee').select2({
                    placeholder: "Search for an Employee",
                    allowClear: true,
                    dropdownParent: $('#addLoanModal')
                });
            });

            // Destroy Select2 when modal is hidden
            $('#addLoanModal').on('hidden.bs.modal', function () {
                $('.select2-employee').select2('destroy');
                // Reset form
                $('#loanForm')[0].reset();
                $('#loan-calculation-results').hide();
                $('#calculated-terms').text('Enter loan amount and monthly amortization');
                $('#save-loan').prop('disabled', true);
            });

            // Calculate loan when button is clicked
            $('#calculate-loan').click(function() {
                calculateLoan();
            });

            // Auto-calculate when relevant fields change
            $('#original_amount, #amortization_amount, #start_date').on('input change', function() {
                if (validateInputs()) {
                    calculateLoan();
                }
            });

            function validateInputs() {
                const amount = parseFloat($('#original_amount').val()) || 0;
                const monthlyPayment = parseFloat($('#amortization_amount').val()) || 0;
                const startDate = $('#start_date').val();

                return amount > 0 && monthlyPayment > 0 && startDate;
            }

            function calculateLoan() {
                if (!validateInputs()) {
                    alert('Please fill in all required fields first.');
                    return;
                }

                const amount = parseFloat($('#original_amount').val());
                const monthlyPayment = parseFloat($('#amortization_amount').val());
                const startDate = new Date($('#start_date').val());

                // Simple calculation: loan amount ÷ monthly payment = number of terms
                const numberOfTerms = Math.ceil(amount / monthlyPayment);

                // Calculate end date
                const endDate = new Date(startDate);
                endDate.setMonth(endDate.getMonth() + numberOfTerms);

                // Display results
                $('#calculated-terms').text(numberOfTerms + ' months');
                $('#number-of-terms').text(numberOfTerms);
                $('#end-date').text(endDate.toLocaleDateString());

                // Set hidden fields
                $('#numer_terms_hidden').val(numberOfTerms);
                $('#balance_hidden').val(amount.toFixed(2));
                $('#end_date_hidden').val(endDate.toISOString().split('T')[0]);

                // Show results and enable save button
                $('#loan-calculation-results').show();
                $('#save-loan').prop('disabled', false);
            }

            // Form validation before submit
            $('#loanForm').on('submit', function(e) {
                if (!validateInputs()) {
                    e.preventDefault();
                    alert('Please fill in all required fields and calculate the loan terms first.');
                    return false;
                }

                // Check if loan has been calculated
                if (!$('#numer_terms_hidden').val()) {
                    e.preventDefault();
                    alert('Please calculate the loan terms first by clicking "Calculate Loan" button.');
                    return false;
                }
            });
        });
    </script>
@stop