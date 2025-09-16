@extends('adminlte::page')

@section('title', 'Process DTR')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

@section('content_header')
    <h1 class="ml-1">Process DTR (Plotted vs. Actual)</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daily Time Records Overview</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('payroll.process-dtr.store') }}" method="POST">
                @csrf

                {{-- Define table headers --}}
                @php
                    $heads = [
                        'Employee ID',
                        'Employee Name',
                        'Date',
                        'Plotted Schedule', // Changed header to be more generic for shifts or leaves
                        'Actual Time In',
                        'Actual Time Out',
                        'Actions' // Placeholder for future actions like edit/delete
                    ];

                    $config = [
                        'order' => [[2, 'desc']], // Order by date descending
                        'columns' => array_fill(0, count($heads), ['orderable' => true]),
                        'paging' => true,       // Keep pagination enabled
                        'info' => false,         // Disable info text (showing 1 to X of Y entries)
                        'searching' => true,     // Keep search enabled if desired
                        'pageLength' => 10,      // Default page size
                        'responsive' => true,    // Make table responsive
                    ];
                @endphp

                <x-adminlte-datatable id="dtrTable" :heads="$heads" :config="$config" hoverable bordered striped class="table-custom">
                    @forelse ($data as $row)
                        <tr>
                            <td>
                                {{ $row->employee_id }}
                                {{-- Hidden inputs to pass data to the store method --}}
                                <input type="hidden" name="dtrs[{{ $loop->index }}][employee_id]" value="{{ $row->employee_id }}">
                                <input type="hidden" name="dtrs[{{ $loop->index }}][date]" value="{{ $row->date }}">
                                <input type="hidden" name="dtrs[{{ $loop->index }}][shift_code]" value="{{ $row->shift_code }}">
                                <input type="hidden" name="dtrs[{{ $loop->index }}][xptd_time_in]" value="{{ $row->plotted_time_in }}">
                                <input type="hidden" name="dtrs[{{ $loop->index }}][xptd_time_out]" value="{{ $row->plotted_time_out }}">
                                <input type="hidden" name="dtrs[{{ $loop->index }}][time_in]" value="{{ $row->actual_time_in }}">
                                <input type="hidden" name="dtrs[{{ $loop->index }}][time_out]" value="{{ $row->actual_time_out }}">
                                {{-- Pass leave_type_id to the store method for DTR processing --}}
                                <input type="hidden" name="dtrs[{{ $loop->index }}][leave_type_id]" value="{{ $row->leave_type_id ?? '' }}">
                            </td>
                            <td>{{ $row->employee_name }}</td>
                            <td>{{ $row->date }}</td>
                            <td>
                                {{-- Conditional display for Plotted Schedule --}}
                                @if ($row->leave_type_name)
                                    <span class="badge badge-info">{{ $row->leave_type_name }}</span>
                                @elseif ($row->plotted_time_in && $row->plotted_time_out)
                                    {{ \Carbon\Carbon::parse($row->plotted_time_in)->format('h:i A') }} - {{ \Carbon\Carbon::parse($row->plotted_time_out)->format('h:i A') }}
                                @else
                                    N/A {{-- Default if no shift or leave is plotted --}}
                                @endif
                            </td>
                            <td>{{ $row->actual_time_in ? \Carbon\Carbon::parse($row->actual_time_in)->format('h:i A') : 'N/A' }}</td>
                            <td>{{ $row->actual_time_out ? \Carbon\Carbon::parse($row->actual_time_out)->format('h:i A') : 'N/A' }}</td>

                            <td>
    <button type="button" class="btn btn-sm btn-warning edit-btn"
        data-id="{{ $row->employee_id }}"
        data-date="{{ $row->date }}"
        data-time-in="{{ $row->actual_time_in }}"
        data-time-out="{{ $row->actual_time_out }}">
        Edit
    </button>
</td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($heads) }}" class="text-center">No DTR records found for processing.</td>
                        </tr>
                    @endforelse
                </x-adminlte-datatable>

                <input type="hidden" name="process_all" value="1">
                <button type="submit" class="btn btn-primary mt-3">Process DTRs</button>
            </form>
        </div>
    </div>
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
  <form id="editForm" method="POST" action="">
        @csrf
        @method('PUT')
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Actual Time</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="employee_id" id="employee_id">
                <input type="hidden" name="date" id="date">

                <div class="form-group">
                    <label for="time_in">Actual Time In</label>
                    <input type="time" class="form-control" name="time_in" id="time_in">
                </div>
                <div class="form-group">
                    <label for="time_out">Actual Time Out</label>
                    <input type="time" class="form-control" name="time_out" id="time_out">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </form>
  </div>
</div>

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).on("click", ".edit-btn", function() {
    let employeeId = $(this).data("id");
    let date = $(this).data("date");
    let timeIn = $(this).data("time-in");
    let timeOut = $(this).data("time-out");

    // Set form action dynamically → resource update
    let url = "/payroll/process-dtr/" + employeeId; 
    $("#editForm").attr("action", url);

    $("#employee_id").val(employeeId);
    $("#date").val(date);
    $("#time_in").val(timeIn ? new Date(timeIn).toISOString().slice(11,16) : '');
    $("#time_out").val(timeOut ? new Date(timeOut).toISOString().slice(11,16) : '');

    $("#editModal").modal("show");
});

</script>
@stop
@section('css')
    <style>
        .table-custom tbody tr td {
            vertical-align: middle;
        }
    </style>
@stop