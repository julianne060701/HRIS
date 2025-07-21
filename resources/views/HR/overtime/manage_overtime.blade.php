@extends('adminlte::page')

@section('title', 'Employee Attendance')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

@section('content_header')
    <h1 class="ml-1">MANAGE OVERTIME</h1> {{-- Changed to MANAGE OVERTIME as per the controller context --}}
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            {{-- Removed the date range filters as requested --}}
            <table id="otTable" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>OT Date</th>
                        <th>OT Time In</th>
                        <th>OT Time Out</th>
                        <th>Total Hours</th>
                        <th>Status</th> {{-- Added Status column --}}
                        <th>Approved Hours</th> {{-- Added Approved Hours column --}}
                        <th>Actions</th> {{-- Added Actions column for buttons --}}
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
<script>
$(function() {
    // Initialize DataTable
    var table = $('#otTable').DataTable({
        processing: true,
        serverSide: false, // Set to false since we're fetching all data at once
        ajax: {
            url: "{{ route('overtime.data') }}", // Use a named route for the data endpoint
            type: 'GET'
        },
        columns: [
            { data: 'employee_id' },
            { data: 'ot_date' },
            { data: 'ot_in' },
            { data: 'ot_out' },
            { data: 'total_ot_hours' },
            {
                data: 'is_approved',
                render: function(data) {
                    return data ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-secondary">Pending</span>';
                }
            },
            { data: 'approved_hours' },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-success btn-sm btn-approve" data-id="${row.id}">Approve</button>
                        <button class="btn btn-danger btn-sm btn-disapprove" data-id="${row.id}">Disapprove</button>
                        <button class="btn btn-primary btn-sm btn-edit" data-id="${row.id}">Edit</button>
                    `;
                },
                orderable: false,
                searchable: false
            }
        ],
        // Add DataTables options here if needed, e.g., ordering, searching
    });

    // Approve
    $('#otTable').on('click', '.btn-approve', function() {
        const id = $(this).data('id');
        $.post('/overtime/approve/' + id, {_token: '{{ csrf_token() }}'}, function(response) {
            alert(response.message);
            table.ajax.reload();
        }).fail(function(xhr) {
            alert('Approval failed: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : xhr.statusText));
        });
    });

    // Disapprove
    $('#otTable').on('click', '.btn-disapprove', function() {
        const id = $(this).data('id');
        $.post('/overtime/disapprove/' + id, {_token: '{{ csrf_token() }}'}, function(response) {
            alert(response.message);
            table.ajax.reload();
        }).fail(function(xhr) {
            alert('Disapproval failed: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : xhr.statusText));
        });
    });

    // Edit
    $('#otTable').on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        const row = table.row($(this).parents('tr')).data();

        const newDate = prompt('Enter new OT Date (YYYY-MM-DD):', row.ot_date);
        if (newDate === null) return; // User cancelled

        const newIn = prompt('Enter new OT Time In:', row.ot_in);
        if (newIn === null) return; // User cancelled

        const newOut = prompt('Enter new OT Time Out:', row.ot_out);
        if (newOut === null) return; // User cancelled

        const newTotal = prompt('Enter new Total Hours:', row.total_ot_hours);
        if (newTotal === null) return; // User cancelled

        // Optionally, ask for approved hours if different from total
        const newApprovedHours = prompt('Enter new Approved Hours (leave blank to use Total Hours):', row.approved_hours);

        $.post('/overtime/update/' + id, {
            _token: '{{ csrf_token() }}',
            ot_date: newDate,
            ot_in: newIn,
            ot_out: newOut,
            total_ot_hours: newTotal,
            approved_hours: newApprovedHours !== '' ? newApprovedHours : null // Send null if empty string
        }, function(response) {
            alert(response.message);
            table.ajax.reload();
        }).fail(function(xhr) {
            let errorMessage = 'Update failed: ';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMessage += JSON.stringify(xhr.responseJSON.errors);
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage += xhr.responseJSON.message;
            } else {
                errorMessage += xhr.statusText;
            }
            alert(errorMessage);
        });
    });
});
</script>
@stop