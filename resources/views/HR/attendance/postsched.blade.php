@extends('adminlte::page')

@section('title', 'Payroll')

@section('content')
    <div class="card">
        <div class="card-body">
            <button id="cutoff_btn" class="btn btn-primary mb-3">Get Cutoff Dates</button>

            <div class="mb-3">
                <label for="minDate">From:</label>
                <input type="date" id="minDate" class="form-control d-inline w-auto mx-2" />
                <label for="maxDate">To:</label>
                <input type="date" id="maxDate" class="form-control d-inline w-auto mx-2" />
            </div>
           


            <table id="schedulepost" class="table table-bordered table-hover mt-3">
                <thead>
                    <tr id="schedule-header">
                        <th>Name</th>
                    </tr>
                    <tr>
                    <td>210033</td>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <button id="pstschd_btn" class="btn btn-primary mt-3">POST SCHEDULE</button>
            
        </div>
    </div>

@stop


 @section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('cutoff_btn');
    const minDate = document.getElementById('minDate');
    const maxDate = document.getElementById('maxDate');

    btn.addEventListener('click', function () {
        fetch("{{ route('cutoff.dates') }}")
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert("No cutoff data found.");
                } else {
                    minDate.value = data.start_date;
                    maxDate.value = data.end_date;

                    const start = new Date(data.start_date);
                    const end = new Date(data.end_date);
                    const headerRow = document.getElementById('schedule-header');
                    headerRow.innerHTML = `<th>Name</th>`;

                    const dateArray = [];
                    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                        const current = new Date(d);
                        const formatted = current.toISOString().split('T')[0];
                        const weekday = current.toLocaleDateString('en-US', { weekday: 'short' });

                        dateArray.push(formatted);
                        const th = document.createElement('th');
                        th.textContent = `${formatted} (${weekday})`;
                        headerRow.appendChild(th);
                    }

                    fetch(`/schedule/data?from=${data.start_date}&to=${data.end_date}`)
                        .then(res => res.json())
                        .then(scheduleData => {
                            const tbody = document.querySelector('#schedulepost tbody');
                            tbody.innerHTML = '';

                            scheduleData.forEach(row => {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `<td>${row.name}</td>`;

                                dateArray.forEach(date => {
                                    const shift = row.shifts?.[date] || '';
                                    tr.innerHTML += `<td>${shift}</td>`; // show shift code
                                });

                                tbody.appendChild(tr);
                            });
                        });
                }
            })
            .catch(error => {
                console.error("Fetch Error:", error);
            });
    });
});


</script>
@stop
