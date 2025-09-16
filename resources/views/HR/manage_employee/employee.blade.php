@extends('adminlte::page')

@section('title', 'Add Employee')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)
@section('plugins.Sweetalert2', true)

<link rel="icon" type="image/x-icon" href="{{ asset('LOGO.ico') }}">

@section('content_header')
    <h1 class="ml-1">Employee List</h1>
@stop

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
        .status-Probationary { background-color: #ffc107; color: black; }
        .status-Regular { background-color: #28a745; color: white; }
        .status-Resigned { background-color: #dc3545; color: white; }
    </style>
@endpush

@section('content')

<div class="container-fluid">

    <!-- Upload new employee -->
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('HR.manage_employee.create_employee') }}" class="btn btn-primary px-5">Add Employee</a>
    </div>

    <!-- Employee Data Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    @php
                        $heads = [
                            'Employee ID',
                            'Full Name',  
                            'Status',                       
                            'Department', 
                            'Salary',
                            'Date Joined',
                            'Actions',
                        ];

                        $config = [
                            'order' => [[5, 'desc']], // Sort by Date Joined (column index 5)
                            'columns' => [
                                null, // Employee ID
                                null, // Full Name
                                null, // Status
                                null, // Department
                                null, // Salary
                                null, // Date Joined
                                ['orderable' => false], // Actions (disable sorting)
                            ],
                        ];
                    @endphp

                    <x-adminlte-datatable id="employeeTable" :heads="$heads" :config="$config" hoverable class="table-custom">
                    @foreach ($data as $employee)
                        <tr>
                            <td>{{ $employee[0] }}</td>  <!-- employee_id -->
                            <td>{{ $employee[1] }}</td>  <!-- Full Name -->
                            <td>{{ $employee[2] }}</td>  <!-- Status -->
                            <td>{{ $employee[3] }}</td>  <!-- Department -->
                            <td>{{ $employee[4] }}</td>  <!-- Salary -->
                            <td>{{ $employee[5] }}</td>  <!-- Date Joined -->
                            <td>{!! $employee[6] !!}</td>  <!-- Actions (Edit and Delete) -->
                        </tr>
                    @endforeach
                    </x-adminlte-datatable>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Employee Modal -->
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="viewEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewEmployeeModalLabel">
                    <i class="fas fa-user"></i> Employee Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong><i class="fas fa-id-card"></i> Employee ID:</strong></label>
                            <p id="view_employee_id" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong><i class="fas fa-user"></i> Full Name:</strong></label>
                            <p id="view_full_name" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong><i class="fas fa-calendar"></i> Birthday:</strong></label>
                            <p id="view_birthday" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong><i class="fas fa-phone"></i> Contact Number:</strong></label>
                            <p id="view_contact_number" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><strong><i class="fas fa-map-marker-alt"></i> Address:</strong></label>
                            <p id="view_address" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong><i class="fas fa-building"></i> Department:</strong></label>
                            <p id="view_department" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong><i class="fas fa-money-bill-wave"></i> Salary:</strong></label>
                            <p id="view_salary" class="form-control-plaintext"></p>
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
                            <label><strong><i class="fas fa-calendar-plus"></i> Date Joined:</strong></label>
                            <p id="view_created_at" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong><i class="fas fa-id-badge"></i> SSS:</strong></label>
                            <p id="view_sss" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong><i class="fas fa-heart"></i> PhilHealth:</strong></label>
                            <p id="view_philhealth" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong><i class="fas fa-file-invoice"></i> TIN:</strong></label>
                            <p id="view_tin" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong><i class="fas fa-home"></i> Pag-IBIG:</strong></label>
                            <p id="view_pagibig" class="form-control-plaintext"></p>
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
        // Initialize DataTable only if not already initialized
        if (!$.fn.DataTable.isDataTable('#employeeTable')) {
            $('#employeeTable').DataTable({
                responsive: true,
                autoWidth: false,
                ordering: true,
                pageLength: 10,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search employees...",
                }
            });
        }

        // Variables for tracking current employee ID
        let currentEmployeeId = null;

        // Function to format date
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        // Function to format currency
        function formatCurrency(amount) {
            if (!amount) return 'N/A';
            return new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP'
            }).format(amount);
        }

        // Function to get status badge HTML
        function getStatusBadge(status) {
            let className = '';
            switch(status) {
                case 'Probationary':
                    className = 'status-Probationary';
                    break;
                case 'Regular':
                    className = 'status-Regular';
                    break;
                case 'Resigned':
                    className = 'status-Resigned';
                    break;
                default:
                    className = 'status-Probationary';
            }
            return `<span class="status-badge ${className}">${status}</span>`;
        }

        // Test function to debug the issue
        function testEmployeeRoute(employeeId) {
            console.log('Testing route with employee ID:', employeeId);
            
            $.ajax({
                url: `{{ url('test-employee') }}/${employeeId}`,
                type: 'GET',
                success: function(response) {
                    console.log('Test route success:', response);
                },
                error: function(xhr, status, error) {
                    console.error('Test route error:', xhr, status, error);
                }
            });
        }

        // Handle view button click
        $(document).on('click', '.view-ticket', function(e) {
            e.preventDefault();
            currentEmployeeId = $(this).data('id');
            
            // Test the route first
            testEmployeeRoute(currentEmployeeId);
            
            // Show loading
            Swal.fire({
                title: 'Loading...',
                text: 'Please wait while we fetch the employee details.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Debug: Log the current employee ID and URL
            console.log('Current Employee ID:', currentEmployeeId);
            const requestUrl = `{{ url('employee') }}/${currentEmployeeId}`;
            console.log('Request URL:', requestUrl);
            
            // Fetch employee data via AJAX
            $.ajax({
                url: requestUrl,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                success: function(response) {
                    // Close loading
                    Swal.close();
                    
                    console.log('Employee data received:', response); // Debug log
                    
                    // Populate view fields
                    $('#view_employee_id').text(response.employee_id || 'N/A');
                    $('#view_full_name').text(`${response.first_name || ''} ${response.middle_name || ''} ${response.last_name || ''}`.trim() || 'N/A');
                    $('#view_birthday').text(formatDate(response.birthday));
                    $('#view_contact_number').text(response.contact_number || 'N/A');
                    $('#view_address').text(response.address || 'N/A');
                    $('#view_department').text(response.department || 'N/A');
                    $('#view_salary').text(formatCurrency(response.salary));
                    $('#view_status').html(getStatusBadge(response.status || 'N/A'));
                    $('#view_created_at').text(formatDate(response.created_at));
                    $('#view_sss').text(response.sss || 'N/A');
                    $('#view_philhealth').text(response.philhealth || 'N/A');
                    $('#view_tin').text(response.tin || 'N/A');
                    $('#view_pagibig').text(response.pagibig || 'N/A');
                    
                    // Show modal
                    $('#viewEmployeeModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr);
                    console.error('Status:', status);
                    console.error('Error:', error);
                    console.error('Response:', xhr.responseText);
                    
                    let errorMessage = 'Failed to load employee data. Please try again.';
                    if (xhr.status === 404) {
                        errorMessage = 'Employee not found.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. Please try again later.';
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage,
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        // Handle delete button click
        $(document).on('click', '.Delete', function(e) {
            e.preventDefault();
            currentEmployeeId = $(this).data('delete');
            
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
                        text: 'Please wait while we delete the employee.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    $.ajax({
                        url: `/HR/delete_employee/${currentEmployeeId}`,
                        type: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Employee has been deleted successfully.',
                                confirmButtonText: 'OK',
                                timer: 3000
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            let errorMessage = 'Failed to delete employee. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
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
                        text: 'Employee is safe :)',
                        icon: 'info',
                        timer: 2000
                    });
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

