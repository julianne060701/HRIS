@extends('adminlte::page')

@section('title', 'Payroll')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)
@section('plugins.Sweetalert2', true)

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .card {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: none;
        }
        .table th {
            background-color: #343a40;
            color: white;
        }
        .btn-action {
            margin: 0 2px;
            padding: 4px 8px;
            font-size: 12px;
        }
        .modal-header {
            background-color: #007bff;
            color: white;
        }
        .modal-header .close {
            color: white;
            opacity: 0.8;
        }
        .form-control-plaintext {
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 10px;
            padding-bottom: 5px;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-active { background-color: #28a745; color: white; }
        .status-inactive { background-color: #6c757d; color: white; }
        .status-processed { background-color: #17a2b8; color: white; }
        .content-header h1 {
            margin: 0;
        }
    </style>
@endpush

@section('content_header')
    <h1 class="ml-1"><i class="fas fa-money-check-alt"></i> Payroll List</h1>
@stop

@section('content')
    <!-- Add Payroll Button -->
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary px-5" data-toggle="modal" data-target="#addPayrollModal">
            <i class="fas fa-plus"></i> Add Payroll
        </button>
    </div>

    <!-- Payroll Table -->
    <div class="card">
        <div class="card-body">
            <table id="payrollTable" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Payroll Code</th>
                        <th>Title</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payrollData as $payroll)
                        <tr>
                            <td>{{ $payroll['id'] }}</td>
                            <td>{{ $payroll['payroll_code'] }}</td>
                            <td>{{ $payroll['title'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($payroll['from_date'])->format('m/d/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($payroll['to_date'])->format('m/d/Y') }}</td>
                            <td>
                                @php
                                    $statusClass = match($payroll['status']) {
                                        'Active' => 'status-active',
                                        'NotActive' => 'status-inactive', 
                                        'Processed' => 'status-processed',
                                        default => 'status-inactive'
                                    };
                                @endphp
                                <span class="status-badge {{ $statusClass }}">{{ $payroll['status'] }}</span>
                            </td>
                            <td>
                                <button class="btn btn-info btn-sm btn-action view-payroll" data-id="{{ $payroll['id'] }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-warning btn-sm btn-action edit-payroll" data-id="{{ $payroll['id'] }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm btn-action delete-payroll" data-id="{{ $payroll['id'] }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Payroll Modal -->
    <div class="modal fade" id="addPayrollModal" tabindex="-1" role="dialog" aria-labelledby="addPayrollModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('add-payroll.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPayrollModalLabel">
                            <i class="fas fa-plus-circle"></i> Add Payroll
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="payroll_code">Payroll Code <span class="text-danger">*</span></label>
                            <input type="text" name="payroll_code" class="form-control" placeholder="Enter payroll code" required>
                        </div>

                        <div class="form-group">
                            <label for="title">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="Enter payroll title" required>
                        </div>

                        <div class="form-group">
                            <label for="from_date">From Date <span class="text-danger">*</span></label>
                            <input type="date" name="from_date" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="to_date">To Date <span class="text-danger">*</span></label>
                            <input type="date" name="to_date" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="">Select Status</option>
                                <option value="Active">Active</option>
                                <option value="NotActive">Not Active</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Save
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Payroll Modal -->
    <div class="modal fade" id="editPayrollModal" tabindex="-1" role="dialog" aria-labelledby="editPayrollModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="editPayrollForm" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPayrollModalLabel">
                            <i class="fas fa-edit"></i> Edit Payroll
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_payroll_code">Payroll Code <span class="text-danger">*</span></label>
                            <input type="text" name="payroll_code" id="edit_payroll_code" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_title">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="edit_title" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_from_date">From Date <span class="text-danger">*</span></label>
                            <input type="date" name="from_date" id="edit_from_date" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_to_date">To Date <span class="text-danger">*</span></label>
                            <input type="date" name="to_date" id="edit_to_date" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="edit_status" class="form-control" required>
                                <option value="Active">Active</option>
                                <option value="NotActive">Not Active</option>
                                <option value="Processed">Processed</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Update
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- View Payroll Modal -->
    <div class="modal fade" id="viewPayrollModal" tabindex="-1" role="dialog" aria-labelledby="viewPayrollModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewPayrollModalLabel">
                        <i class="fas fa-eye"></i> Payroll Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong><i class="fas fa-barcode"></i> Payroll Code:</strong></label>
                                <p id="view_payroll_code" class="form-control-plaintext"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong><i class="fas fa-heading"></i> Title:</strong></label>
                                <p id="view_title" class="form-control-plaintext"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong><i class="fas fa-calendar-alt"></i> From Date:</strong></label>
                                <p id="view_from_date" class="form-control-plaintext"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong><i class="fas fa-calendar-check"></i> To Date:</strong></label>
                                <p id="view_to_date" class="form-control-plaintext"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong><i class="fas fa-info-circle"></i> Status:</strong></label>
                                <p id="view_status" class="form-control-plaintext"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong><i class="fas fa-clock"></i> Created At:</strong></label>
                                <p id="view_created_at" class="form-control-plaintext"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#payrollTable').DataTable({
            responsive: true,
            autoWidth: false,
            ordering: true,
            pageLength: 10,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search payrolls...",
            }
        });

        // Variables for tracking current payroll ID
        let currentPayrollId = null;

        // Function to format date without time
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        }

        // Function to get status badge HTML
        function getStatusBadge(status) {
            let className = '';
            switch(status) {
                case 'Active':
                    className = 'status-active';
                    break;
                case 'NotActive':
                    className = 'status-inactive';
                    break;
                case 'Processed':
                    className = 'status-processed';
                    break;
                default:
                    className = 'status-inactive';
            }
            return `<span class="status-badge ${className}">${status}</span>`;
        }

        // Handle edit button click
        $(document).on('click', '.edit-payroll', function(e) {
            e.preventDefault();
            currentPayrollId = $(this).data('id');
            
            // Show loading
            Swal.fire({
                title: 'Loading...',
                text: 'Please wait while we fetch the payroll data.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Fetch payroll data via AJAX
            $.ajax({
                url: `{{ url('HR/payroll/edit') }}/${currentPayrollId}`,
                type: 'GET',
                success: function(response) {
                    // Close loading
                    Swal.close();
                    
                    // Debug logging
                    console.log('Payroll data received:', response);
                    console.log('From date:', response.from_date);
                    console.log('To date:', response.to_date);
                    
                    // Populate form fields
                    $('#edit_payroll_code').val(response.payroll_code || '');
                    $('#edit_title').val(response.title || '');
                    $('#edit_from_date').val(response.from_date || '');
                    $('#edit_to_date').val(response.to_date || '');
                    $('#edit_status').val(response.status || '');
                    
                    // Show modal
                    $('#editPayrollModal').modal('show');
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr);
                    console.error('Response:', xhr.responseText);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Failed to load payroll data. Please check the console for details.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        // Handle edit form submission
        $('#editPayrollForm').on('submit', function(e) {
            e.preventDefault();
            
            // Validate dates on frontend
            let fromDate = new Date($('#edit_from_date').val());
            let toDate = new Date($('#edit_to_date').val());
            
            if (fromDate >= toDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error!',
                    text: 'From date must be earlier than To date.',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Show loading
            Swal.fire({
                title: 'Updating...',
                text: 'Please wait while we update the payroll.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Debug: Log the form data being sent
            let formData = $(this).serialize();
            console.log('Form data being sent:', formData);
            console.log('Current Payroll ID:', currentPayrollId);
            
            $.ajax({
                url: `{{ url('HR/payroll/update') }}/${currentPayrollId}`,
                type: 'POST',
                data: formData,
                success: function(response) {
                    $('#editPayrollModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Great!',
                        text: 'Payroll updated successfully.',
                        confirmButtonText: 'OK',
                        timer: 3000
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    console.error('Update Error:', xhr);
                    console.error('Response:', xhr.responseText);
                    
                    if (xhr.status === 422) {
                        // Validation errors
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = [];
                        
                        $.each(errors, function(field, messages) {
                            $.each(messages, function(index, message) {
                                errorMessages.push(message);
                            });
                        });
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error!',
                            html: '<ul class="text-left"><li>' + errorMessages.join('</li><li>') + '</li></ul>',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        let errorMessage = 'Failed to update payroll. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMessage,
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        });

        // Handle view button click
        $(document).on('click', '.view-payroll', function(e) {
            e.preventDefault();
            currentPayrollId = $(this).data('id');
            
            // Show loading
            Swal.fire({
                title: 'Loading...',
                text: 'Please wait while we fetch the payroll details.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Fetch payroll data via AJAX
            $.ajax({
                url: `{{ url('HR/payroll/edit') }}/${currentPayrollId}`,
                type: 'GET',
                success: function(response) {
                    // Close loading
                    Swal.close();
                    
                    // Populate view fields with proper date formatting
                    $('#view_payroll_code').text(response.payroll_code || 'N/A');
                    $('#view_title').text(response.title || 'N/A');
                    $('#view_from_date').text(formatDate(response.from_date));
                    $('#view_to_date').text(formatDate(response.to_date));
                    $('#view_status').html(getStatusBadge(response.status || 'N/A'));
                    $('#view_created_at').text(formatDate(response.created_at));
                    
                    // Show modal
                    $('#viewPayrollModal').modal('show');
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Failed to load payroll data. Please try again.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        // Handle delete button click
        $(document).on('click', '.delete-payroll', function(e) {
            e.preventDefault();
            currentPayrollId = $(this).data('id');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait while we delete the payroll.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Debug: Log the delete request
                    console.log('Delete Payroll ID:', currentPayrollId);
                    console.log('CSRF Token:', $('meta[name="csrf-token"]').attr('content'));
                    
                    $.ajax({
                        url: `{{ url('HR/payroll/delete') }}/${currentPayrollId}`,
                        type: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Your payroll has been deleted.',
                                confirmButtonText: 'OK',
                                timer: 3000
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            console.error('Delete Error:', xhr);
                            console.error('Response:', xhr.responseText);
                            
                            let errorMessage = 'Failed to delete payroll. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorMessage,
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire({
                        title: 'Cancelled',
                        text: 'Your payroll is safe :)',
                        icon: 'info',
                        timer: 2000
                    });
                }
            });
        });

        // Handle add form submission with SweetAlert
        $('form[action="{{ route("add-payroll.store") }}"]').on('submit', function(e) {
            e.preventDefault();
            
            // Validate dates on frontend
            let fromDate = new Date($('input[name="from_date"]').val());
            let toDate = new Date($('input[name="to_date"]').val());
            
            if (fromDate >= toDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error!',
                    text: 'From date must be earlier than To date.',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Show loading
            Swal.fire({
                title: 'Creating...',
                text: 'Please wait while we create the payroll.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#addPayrollModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Excellent!',
                        text: 'Payroll added successfully.',
                        confirmButtonText: 'OK',
                        timer: 3000
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        // Validation errors
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = [];
                        
                        $.each(errors, function(field, messages) {
                            $.each(messages, function(index, message) {
                                errorMessages.push(message);
                            });
                        });
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error!',
                            html: '<ul class="text-left"><li>' + errorMessages.join('</li><li>') + '</li></ul>',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to add payroll. Please try again.',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        });

        // Show success message if redirected with success
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session("success") }}',
                confirmButtonText: 'OK',
                timer: 4000
            });
        @endif

        // Show error message if redirected with error
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session("error") }}',
                confirmButtonText: 'OK'
            });
        @endif

        // Show validation errors if any
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validation Errors!',
                html: '<ul class="text-left">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
                confirmButtonText: 'OK'
            });
        @endif
    });
</script>
@endsection