@extends('adminlte::page')

@section('title', 'Employee Leave Management')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

@section('content_header')
    <h1 class="ml-1">Manage Leave</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="leaveTable" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Leave Type</th>
                        <th>Reason</th>
                        <th>Total Days</th>
                        <th>Status</th>
                        <th>Approved By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- Modal for custom alerts (instead of native alert()) --}}
    <div class="modal fade" id="customAlertModal" tabindex="-1" role="dialog" aria-labelledby="customAlertModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customAlertModalLabel">Notification</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="customAlertModalBody">
                    <!-- Message will be inserted here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(function() {
    // Function to show custom alert modal
    function showCustomAlert(message) {
        $('#customAlertModalBody').text(message);
        $('#customAlertModal').modal('show');
    }

    // Initialize DataTable
    var table = $('#leaveTable').DataTable({
        processing: true,
        serverSide: false, // Set to false since we're fetching all data at once
        ajax: {
            url: "{{ route('leavemgt.data') }}", // Use a named route for the data endpoint
            type: 'GET'
        },
        columns: [
            { data: 'employee_id' },
            {
                data: 'date_start',
                render: function(data) {
                    return data ? data.split('T')[0] : '';
                }
            },
            {
                data: 'date_end',
                render: function(data) {
                    return data ? data.split('T')[0] : '';
                }
            },
            { data: 'leave_type_display' }, 
            { data: 'reason' },
            { data: 'total_days' }, 
            {
                data: 'status',
                render: function(data) {
                    if (data === 'approved') {
                        return '<span class="badge bg-success">Approved</span>';
                    } else if (data === 'pending') {
                        return '<span class="badge bg-secondary">Pending</span>';
                    } else if (data === 'disapproved') {
                        return '<span class="badge bg-danger">Disapproved</span>';
                    }
                    return '<span class="badge bg-info">' + data + '</span>'; 
                }
            },
            { data: 'approved_by', defaultContent: 'N/A' }, 
            {
                data: null,
                render: function(data, type, row) {
                    let actions = '';
                    if (row.status === 'pending') { 
                        actions += `<button class="btn btn-success btn-sm btn-approve" data-id="${row.id}">Approve</button>
                                        <button class="btn btn-danger btn-sm btn-disapprove" data-id="${row.id}">Disapprove</button>`;
                    }
                    actions += `<button class="btn btn-primary btn-sm btn-edit ml-1" data-id="${row.id}">Edit</button>`;
                    return actions;
                },
                orderable: false,
                searchable: false
            }
        ],
    });

    // Approve Leave
    $('#leaveTable').on('click', '.btn-approve', function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to approve this leave request?')) {
            $.post('/HR/leave/approve/' + id, {
                _token: '{{ csrf_token() }}'
            }, function(response) {
                showCustomAlert(response.message); // Use custom alert
                table.ajax.reload();
            }).fail(function(xhr) {
                let errorMessage = 'Approval failed: ';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += xhr.responseJSON.message;
                } else {
                    errorMessage += xhr.statusText;
                }
                showCustomAlert(errorMessage); // Use custom alert
            });
        }
    });

    // Disapprove Leave
    $('#leaveTable').on('click', '.btn-disapprove', function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to disapprove this leave request?')) {
            $.post('/HR/leave/disapprove/' + id, {
                _token: '{{ csrf_token() }}'
            }, function(response) {
                showCustomAlert(response.message); // Use custom alert
                table.ajax.reload();
            }).fail(function(xhr) {
                let errorMessage = 'Disapproval failed: ';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += xhr.responseJSON.message;
                } else {
                    errorMessage += xhr.statusText;
                }
                showCustomAlert(errorMessage); // Use custom alert
            });
        }
    });

    // Edit Leave
    $('#leaveTable').on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        const row = table.row($(this).parents('tr')).data();

        // Fetch leave types to populate the prompt with valid options
        $.get('{{ route('leave.types') }}', function(response) {
            let leaveTypes = response.data;
            let leaveTypeNames = leaveTypes.map(lt => lt.name);

            // Use JSON.stringify to properly escape default values for prompt
            const newStartDate = prompt('Enter new Start Date (YYYY-MM-DD):', JSON.stringify(row.date_start.split('T')[0]));
            if (newStartDate === null) return;

            const newEndDate = prompt('Enter new End Date (YYYY-MM-DD):', JSON.stringify(row.date_end.split('T')[0]));
            if (newEndDate === null) return;

            // Prompt with available leave types
            const newLeaveType = prompt('Enter new Leave Type (e.g., ' + leaveTypeNames.join(', ') + '):', JSON.stringify(row.leave_type_display));
            if (newLeaveType === null) return;

            const newReason = prompt('Enter new Reason:', JSON.stringify(row.reason));
            if (newReason === null) return;
            
            $.post('/HR/leave/update/' + id, {
                _token: '{{ csrf_token() }}',
                date_start: newStartDate,
                date_end: newEndDate,
                leave_type: newLeaveType, // Send the name, backend will find the ID and calculate total_days
                reason: newReason,
            }, function(response) {
                showCustomAlert(response.message); // Use custom alert
                table.ajax.reload();
            }).fail(function(xhr) {
                console.log(xhr);
                let errorMessage = 'Update failed: ';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage += JSON.stringify(xhr.responseJSON.errors);
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += xhr.responseJSON.message;
                } else {
                    errorMessage += xhr.statusText;
                }
                showCustomAlert(errorMessage); // Use custom alert
            });
        }).fail(function() {
            showCustomAlert('Failed to load leave types. Cannot edit.'); // Use custom alert
        });
    });
});
</script>
@stop
