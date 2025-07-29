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
                    ];

                    $config = [
                        'order' => [[2, 'desc']], // Order by date descending
                        'columns' => array_fill(0, count($heads), ['orderable' => true]),
                        'paging' => false,       // Disable pagination
                        'info' => false,         // Disable info text (showing 1 to X of Y entries)
                        'searching' => true,     // Keep search enabled if desired
                        'pageLength' => -1,      // Show all entries. -1 means no limit.
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($heads) }}" class="text-center">No DTR records found for processing.</td>
                        </tr>
                    @endforelse
                </x-adminlte-datatable>

                <button type="submit" class="btn btn-primary mt-3">Process DTRs</button>
            </form>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stop
@section('css')
    <style>
        .table-custom tbody tr td {
            vertical-align: middle;
        }
    </style>
@stop