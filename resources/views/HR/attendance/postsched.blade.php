@extends('adminlte::page')

@section('title', 'Payroll')

@section('content')
<div class="card">
    <div class="card-body">
        <!-- Payroll Selection -->
        <div class="mb-3">
            <label for="payrollSelect">Select Payroll:</label>
            <select id="payrollSelect" class="form-control d-inline w-auto mx-2">
                <option value="">-- Select Payroll --</option>
                @foreach ($payrollData as $payroll)
                <option value="{{ $payroll['id'] }}" data-from="{{ $payroll['from_date'] }}"
                    data-to="{{ $payroll['to_date'] }}">
                    {{ $payroll['payroll_code'] }} - {{ $payroll['title'] }}
                </option>
                @endforeach
            </select>
        </div>

        <!-- Cutoff and Manual Date Selection -->
        <div class="mb-3">
            <button id="cutoff_btn" class="btn btn-primary mb-3" disabled>Get Current Cutoff Dates</button>

            <div class="d-inline-block ml-3">
                <label for="minDate">From:</label>
                <input type="date" id="minDate" class="form-control d-inline w-auto mx-2" readonly />
                <label for="maxDate">To:</label>
                <input type="date" id="maxDate" class="form-control d-inline w-auto mx-2" readonly />
                <button id="fetch_schedule_btn" class="btn btn-info mb-3" disabled>Fetch Schedule</button>
            </div>
        </div>

        <div class="mb-3">
            <label for="departmentSelect">Select Department:</label>
            <select id="departmentSelect" class="form-control w-auto d-inline mx-2" disabled>
                <option value="">-- All Departments --</option>
                @foreach(\App\Models\Employee::select('department')->distinct()->get() as $dept)
                <option value="{{ $dept->department }}">{{ $dept->department }}</option>
                @endforeach
            </select>
        </div>

        <form id="scheduleForm" method="POST" action="{{ route('schedule.post') }}">
            @csrf
            <input type="hidden" name="payroll_id" id="form_payroll_id">
            <input type="hidden" name="start_date" id="form_start_date">
            <input type="hidden" name="end_date" id="form_end_date">

            <div style="overflow-x: auto; width: 100%;">
                <table id="schedulepost" class="table table-bordered table-hover mt-3">
                    <thead>
                        <tr id="schedule-header">
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <button id="pstschd_btn" class="btn btn-success mt-3" disabled>POST SCHEDULE</button>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const payrollSelect = document.getElementById('payrollSelect');
    const cutoffBtn = document.getElementById('cutoff_btn');
    const fetchScheduleBtn = document.getElementById('fetch_schedule_btn');
    const minDate = document.getElementById('minDate');
    const maxDate = document.getElementById('maxDate');
    const departmentSelect = document.getElementById('departmentSelect');
    const startInput = document.getElementById('form_start_date');
    const endInput = document.getElementById('form_end_date');
    const payrollInput = document.getElementById('form_payroll_id');
    const postBtn = document.getElementById('pstschd_btn');
    let currentDateArray = [];

    // Handle payroll selection change
    payrollSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const payrollId = this.value;
        const fromDate = selectedOption.dataset.from;
        const toDate = selectedOption.dataset.to;

        if (payrollId && fromDate && toDate) {
            // Auto-populate dates
            minDate.value = fromDate;
            maxDate.value = toDate;
            startInput.value = fromDate;
            endInput.value = toDate;
            payrollInput.value = payrollId;

            // Enable controls
            cutoffBtn.disabled = false;
            fetchScheduleBtn.disabled = false;
            departmentSelect.disabled = false;

            // Auto-load schedule for selected payroll
            populateScheduleHeader(fromDate, toDate);
            loadSchedule(fromDate, toDate, departmentSelect.value);

            postBtn.disabled = false;
        } else {
            // Reset
            minDate.value = '';
            maxDate.value = '';
            startInput.value = '';
            endInput.value = '';
            payrollInput.value = '';

            cutoffBtn.disabled = true;
            fetchScheduleBtn.disabled = true;
            departmentSelect.disabled = true;
            postBtn.disabled = true;

            const tbody = document.querySelector('#schedulepost tbody');
            tbody.innerHTML =
                '<tr><td colspan="100%" class="text-center">Please select a payroll to view schedules.</td></tr>';

            const headerRow = document.getElementById('schedule-header');
            headerRow.innerHTML = '<th>Name</th>';
        }
    });

    // Function to populate header dates
    function populateScheduleHeader(startDate, endDate) {
        const headerRow = document.getElementById('schedule-header');
        headerRow.innerHTML = `<th>Name</th>`;
        currentDateArray = [];

        const start = new Date(startDate);
        const end = new Date(endDate);

        for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            const current = new Date(d);
            const formatted = current.toISOString().split('T')[0];
            const weekday = current.toLocaleDateString('en-US', {
                weekday: 'short'
            });

            currentDateArray.push(formatted);
            const th = document.createElement('th');
            th.textContent = `${formatted} (${weekday})`;
            headerRow.appendChild(th);
        }
    }

    // "Get Current Cutoff Dates"
    cutoffBtn.addEventListener('click', function() {
        if (!payrollSelect.value) {
            alert('Please select a payroll first.');
            return;
        }

        const dates = getCutoffDatesFromNow();

        minDate.value = dates.start_date;
        maxDate.value = dates.end_date;
        startInput.value = dates.start_date;
        endInput.value = dates.end_date;

        populateScheduleHeader(dates.start_date, dates.end_date);
        loadSchedule(dates.start_date, dates.end_date, departmentSelect.value);
    });

    // Example cutoff calculator (uncomment/adapt if needed)
    function getCutoffDatesFromNow() {
        const today = new Date();
        const day = today.getDate();
        const month = today.getMonth();
        const year = today.getFullYear();

        let start, end;
        if (day <= 15) {
            start = new Date(year, month, 11);
            end = new Date(year, month, 25);
        } else {
            start = new Date(year, month, 26);
            end = new Date(year, month + 1, 10);
        }

        return {
            start_date: start.toISOString().split('T')[0],
            end_date: end.toISOString().split('T')[0],
        };
    }

    // "Fetch Schedule"
    fetchScheduleBtn.addEventListener('click', function() {
        if (!payrollSelect.value) {
            alert('Please select a payroll first.');
            return;
        }

        const startDate = minDate.value;
        const endDate = maxDate.value;

        if (!startDate || !endDate) {
            alert('Please ensure both dates are selected.');
            return;
        }

        populateScheduleHeader(startDate, endDate);
        loadSchedule(startDate, endDate, departmentSelect.value);
    });

    // Department filter
    departmentSelect.addEventListener('change', function() {
        if (payrollSelect.value && minDate.value && maxDate.value) {
            loadSchedule(minDate.value, maxDate.value, this.value);
        }
    });

    // Load schedule
    function loadSchedule(from, to, department) {
        let url = `/schedule/data?from=${from}&to=${to}`;
        if (department) {
            url += `&department=${encodeURIComponent(department)}`;
        }

        fetch(url)
            .then(res => res.json())
            .then(async scheduleData => {
                const shiftResponse = await fetch("{{ route('schedule.get') }}");
                const availableShifts = await shiftResponse.json();

                const tbody = document.querySelector('#schedulepost tbody');
                tbody.innerHTML = '';

                if (scheduleData.length === 0) {
                    const tr = document.createElement('tr');
                    tr.innerHTML =
                        `<td colspan="${currentDateArray.length + 1}" class="text-center">No employees found for this department or date range.</td>`;
                    tbody.appendChild(tr);
                    return;
                }

                scheduleData.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${row.name}</td>`;

                    currentDateArray.forEach(date => {
                        const actualShiftOrLeave = row.schedules?. [date] || '';
                        const select = document.createElement('select');
                        select.name = `schedule[${row.employee_id}][${date}]`;
                        select.className = 'form-control form-control-sm';

                        const defaultOption = document.createElement('option');
                        defaultOption.value = '';
                        defaultOption.textContent = '--';
                        select.appendChild(defaultOption);

                        availableShifts.forEach(s => {
                            const option = document.createElement('option');
                            option.value = s;
                            option.textContent = s;
                            if (actualShiftOrLeave === s) {
                                option.selected = true;
                            }
                            select.appendChild(option);
                        });

                        const td = document.createElement('td');
                        td.appendChild(select);
                        tr.appendChild(td);
                    });

                    tbody.appendChild(tr);
                });
            })
            .catch(error => {
                console.error("Schedule Fetch Error:", error);
                alert('An error occurred while fetching the schedule. Please try again.');
            });
    }

    // Post schedule
    document.getElementById('pstschd_btn').addEventListener('click', function(e) {
        e.preventDefault();

        if (!payrollSelect.value) {
            alert('Please select a payroll before posting schedules.');
            return;
        }

        const form = document.getElementById('scheduleForm');

        fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message || 'Schedule posted successfully!');
                if (minDate.value && maxDate.value) {
                    loadSchedule(minDate.value, maxDate.value, departmentSelect.value);
                }
            })
            .catch(err => {
                console.error('Schedule Post Error:', err);
                alert('An error occurred while posting the schedule. Please try again.');
            });
    });

    // Initial message
    const tbody = document.querySelector('#schedulepost tbody');
    tbody.innerHTML =
        '<tr><td colspan="100%" class="text-center">Please select a payroll to view schedules.</td></tr>';
});
</script>

<style>
#schedulepost thead th:first-child,
#schedulepost tbody td:first-child {
    position: sticky;
    left: 0;
    background-color: #fff;
    z-index: 1;
}

#schedulepost {
    white-space: nowrap;
}
</style>
@stop