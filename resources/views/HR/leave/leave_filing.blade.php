@extends('adminlte::page')

@section('title', 'File Leave')

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="card">
        <div class="card-body">
            <table id="leaveTable" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Vacation Leave</th>
                        <th>Sick Leave</th>
                        <th>Birthday Leave</th>
                        <th>Maternity Leave</th>
                        <th>Paternity Leave</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

<div class="container">
    <div class="card shadow">
        <div class="card-body">
            <form id="leaveApplicationForm" method="POST" action="{{ route('leave.store') }}">
                @csrf
                <div id="alertContainer"></div>

                <label for="employee_id">Employee ID</label>
                <input type="text" id="employee_id" name="employee_id" class="form-control" required>

                <label for="date_start" class="mt-2">Start Date</label>
                <input type="date" id="date_start" name="date_start" class="form-control" required>

                <label for="date_end" class="mt-2">End Date</label>
                <input type="date" id="date_end" name="date_end" class="form-control" required>

                <label for="leave_type" class="mt-2">Leave Type</label>
                <select id="leave_type" name="leave_type" class="form-control" required>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->name }}">{{ $type->name }}</option>
                    @endforeach
                </select>

                <label for="reason" class="mt-2">Reason</label>
                <textarea id="reason" name="reason" class="form-control"></textarea>

                <button type="submit" class="btn btn-success mt-3">File Leave</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    console.log('LeaveFilingController script loaded and running!');

    document.addEventListener('DOMContentLoaded', function () {
        console.log('DOMContentLoaded fired!');

        const form = document.getElementById('leaveApplicationForm');
        const alertContainer = document.getElementById('alertContainer');
        const employeeIdInput = document.getElementById('employee_id');
        const leaveTable = document.getElementById('leaveTable');
        const leaveTableBody = document.querySelector('#leaveTable tbody');

        console.log('employeeIdInput element:', employeeIdInput);
        console.log('leaveTable element:', leaveTable);
        console.log('leaveTableBody element:', leaveTableBody);

        if (leaveTable && leaveTable.querySelector('thead')) {
            leaveTable.querySelector('thead').style.display = '';
        }

        function clearLeaveTable() {
            leaveTableBody.innerHTML = '';
            if (leaveTable && leaveTable.querySelector('thead')) {
                leaveTable.querySelector('thead').style.display = 'none';
            }
        }

        function populateLeaveTable(credits) {
            clearLeaveTable();

            if (leaveTable && leaveTable.querySelector('thead')) {
                leaveTable.querySelector('thead').style.display = '';
            }

            const row = document.createElement('tr');
            const leaveTypesOrder = ['Vacation Leave', 'Sick Leave', 'Birthday Leave', 'Maternity Leave', 'Paternity Leave'];

            if (Object.keys(credits).length === 0) {
                const noDataRow = document.createElement('tr');
                const noDataTd = document.createElement('td');
                noDataTd.setAttribute('colspan', leaveTypesOrder.length);
                noDataTd.textContent = 'No leave credits found for this employee.';
                noDataTd.style.textAlign = 'center';
                noDataRow.appendChild(noDataTd);
                leaveTableBody.appendChild(noDataRow);
            } else {
                leaveTypesOrder.forEach(type => {
                    const td = document.createElement('td');
                    if (credits[type]) {
                        td.textContent = credits[type].rem_leave + ' / ' + credits[type].all_leave;
                    } else {
                        td.textContent = '0 / 0';
                    }
                    row.appendChild(td);
                });
                leaveTableBody.appendChild(row);
            }
        }

        clearLeaveTable();
        if (leaveTable && leaveTable.querySelector('thead')) {
            leaveTable.querySelector('thead').style.display = 'none';
        }

        employeeIdInput.addEventListener('input', function() {
            console.log('Input event detected on employee_id field!');
            const employeeId = this.value.trim();
            console.log('Current Employee ID value:', employeeId);

            alertContainer.innerHTML = '';

            if (employeeId) {
                console.log('Employee ID is not empty, attempting fetch for:', employeeId);
                fetch(`/api/leave-credits/${employeeId}`)
                    .then(response => {
                        console.log('Fetch request completed, response status:', response.status);
                        if (!response.ok) {
                            clearLeaveTable();
                            response.json().then(error => {
                                alertContainer.innerHTML = `<div class="alert alert-warning">${error.message || 'Error fetching leave credits.'}</div>`;
                            }).catch(() => {
                                alertContainer.innerHTML = `<div class="alert alert-warning">Error fetching leave credits.</div>`;
                            });
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('API Response Data:', data);
                        if (data.success) {
                            populateLeaveTable(data.leave_credits);
                            alertContainer.innerHTML = '';
                        } else {
                            populateLeaveTable({});
                            alertContainer.innerHTML = `<div class="alert alert-info">${data.message || 'No leave credits available.'}</div>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching leave credits:', error);
                        clearLeaveTable();
                        alertContainer.innerHTML = `<div class="alert alert-danger">An error occurred while fetching leave credits. Please try again.</div>`;
                    });
            } else {
                console.log('Employee ID is empty, clearing table and alerts.');
                clearLeaveTable();
                alertContainer.innerHTML = '';
            }
        });

       // Your existing form submission logic
       form.addEventListener('submit', function (event) {
            event.preventDefault();
            alertContainer.innerHTML = '';

            const formData = new FormData(form);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json', // <-- ADD THIS LINE
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                // --- NEW DEBUGGING START ---
                console.log('Form submission response:', response);
                const contentType = response.headers.get("content-type");
                console.log('Content-Type header:', contentType);
                // --- NEW DEBUGGING END ---

                if (contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json().then(json => {
                        // --- NEW DEBUGGING START ---
                        console.log('Parsed JSON response:', json);
                        // --- NEW DEBUGGING END ---
                        return {
                            status: response.status,
                            ok: response.ok,
                            json
                        };
                    });
                } else {
                    return response.text().then(text => {
                        // --- NEW DEBUGGING START ---
                        console.log('Non-JSON text response:', text);
                        // --- NEW DEBUGGING END ---
                        return {
                            status: response.status,
                            ok: response.ok,
                            text
                        };
                    });
                }
            })
            .then(({ status, ok, json, text }) => {
                // --- NEW DEBUGGING START ---
                console.log('Inside final .then block:');
                console.log('status:', status);
                console.log('ok:', ok);
                console.log('json:', json); // Check this value directly before the error line
                console.log('text:', text);
                // --- NEW DEBUGGING END ---

                if (!ok) {
                    if (json) {
                        // This branch is for JSON errors (e.g., validation, custom API errors)
                        console.error('Server responded with an error (JSON):', json);
                        throw json; // Re-throw the JSON error object
                    } else if (text) {
                        // This branch is for non-JSON errors (e.g., Laravel 500 error page)
                        console.error('Server responded with non-JSON error text:', text);
                        alertContainer.innerHTML = `<div class="alert alert-danger">Server error: ${text.substring(0, 200)}... (Check console for full response)</div>`;
                        throw new Error(`Server responded with non-JSON data (status: ${status}). Raw response: ${text.substring(0, 200)}...`);
                    } else {
                        // Generic network or status error
                        console.error('Server responded with status:', status);
                        alertContainer.innerHTML = `<div class="alert alert-danger">Server responded with status: ${status}.</div>`;
                        throw new Error(`Server responded with status: ${status}`);
                    }
                }
                
                // This is the line that was causing the error:
                if (json && json.success) { // Ensure json is defined and has 'success'
                    console.log('Success block entered. json.message:', json.message);
                    alertContainer.innerHTML = `<div class="alert alert-success">${json.message || 'Leave successfully filed! Reloading page...'}</div>`;
                    
                    setTimeout(() => {
                        window.location.reload(); 
                    }, 1500); 
                } else {
                    // This else block handles cases where 'ok' is true, but 'json.success' is false
                    // This means the server successfully returned JSON, but indicated an operation failure
                    console.warn('Operation completed but not successful (json.success is false):', json);
                    alertContainer.innerHTML = `<div class="alert alert-warning">${json.message || 'Operation completed with an issue.'}</div>`;
                }
            })
            .catch(error => {
                console.error('Caught error in .catch block:', error);
                // Remove existing validation feedback before adding new ones
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

                if (error && error.errors) { // This is for ValidationException errors from Laravel
                    let errorMessages = '<ul>';
                    for (const key in error.errors) {
                        error.errors[key].forEach(msg => {
                            errorMessages += `<li>${msg}</li>`;
                        });
                        const inputElement = document.getElementById(key);
                        if (inputElement) {
                            inputElement.classList.add('is-invalid');
                            let existingFeedback = inputElement.nextElementSibling;
                            if (!existingFeedback || !existingFeedback.classList.contains('invalid-feedback')) {
                                const feedbackDiv = document.createElement('div');
                                feedbackDiv.classList.add('invalid-feedback');
                                feedbackDiv.innerText = error.errors[key][0];
                                inputElement.parentNode.insertBefore(feedbackDiv, inputElement.nextSibling);
                            }
                        }
                    }
                    errorMessages += '</ul>';
                    alertContainer.innerHTML = `<div class="alert alert-danger">${errorMessages}</div>`;
                } else if (error && error.message) { // This handles other custom error messages from Laravel
                    alertContainer.innerHTML = `<div class="alert alert-danger">An error occurred: ${error.message}</div>`;
                } else { // Generic unexpected error
                    alertContainer.innerHTML = `<div class="alert alert-danger">An unexpected error occurred. Please try again.</div>`;
                }
            });
        });
    });
</script>
@endsection