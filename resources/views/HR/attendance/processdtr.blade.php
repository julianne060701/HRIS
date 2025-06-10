@extends('adminlte::page')

@section('title', 'Process DTR')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

@section('content_header')
    <h1 class="ml-1">Process DTR (Plotted vs. Actual)</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    @php
                        $heads = [
                            'Employee ID',
                            'Employee Name',
                            'Date',
                            'Plotted Time In',
                            'Plotted Time Out',
                            'Actual Time In',
                            'Actual Time Out',
                        ];

                        $config = [
                            'order' => [[2, 'desc']],
                            'columns' => array_fill(0, count($heads), ['orderable' => true]),
                        ];
                    @endphp

                    <x-adminlte-datatable id="dtrTable" :heads="$heads" :config="$config" hoverable class="table-custom">
                        @forelse ($data as $row)
                            <tr>
                                <td>{{ $row->employee_id }}</td>
                                <td>{{ $row->employee_name }}</td>
                                <td>{{ $row->date }}</td>
                                <td>{{ $row->plotted_time_in ? \Carbon\Carbon::parse($row->plotted_time_in)->format('h:i A') : 'N/A' }}</td>
                                <td>{{ $row->plotted_time_out ? \Carbon\Carbon::parse($row->plotted_time_out)->format('h:i A') : 'N/A' }}</td>
                                <td>{{ $row->actual_time_in ? \Carbon\Carbon::parse($row->actual_time_in)->format('h:i A') : 'N/A' }}</td>
                                <td>{{ $row->actual_time_out ? \Carbon\Carbon::parse($row->actual_time_out)->format('h:i A') : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No DTR records found.</td>
                            </tr>
                        @endforelse
                    </x-adminlte-datatable>

                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stop
