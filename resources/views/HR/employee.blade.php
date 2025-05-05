@extends('adminlte::page')

@section('title', 'Dashboard')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)
<link rel="icon" type="image/x-icon" href="{{ asset('LOGO.ico') }}">
@section('content_header')
    <h1 class="ml-1">Add Employee</h1>
@stop

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-end mb-3">
    <a href="{{ route('hr.create_employee') }}" class="btn btn-primary px-5">Upload PR</a>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    @php
                        $heads = [
                            'Ticket #',
                            'Department',  
                            'Responsible Department',                      
                            'Concern Type',  
                            'Urgency',                                      
                            'Image',
                            'Status',
                            'Date Request',
                            'Total Duration',
                            ['label' => 'Actions', 'no-export' => true, 'width' => 5],
                        ];

                        $config = [
                            'order' => [[7, 'desc']], // Sort by Date Request (column index 6)
                            'columns' => [
                                null, // Ticket #
                                null, // Department
                                null, // Responsible Department
                                null, // Concern Type
                                null, // Urgency
                                ['orderable' => false], // Image (disable sorting)
                                null, // Status
                                null, // Date Request (Ensure this is sortable)
                                null,
                                ['orderable' => false], // Actions (disable sorting)
                            ],
                        ];
                    @endphp

                    <x-adminlte-datatable id="table1" :heads="$heads" :config="$config" hoverable class="table-custom">
                        @foreach ($data as $row)
                            <tr>
                                @foreach ($row as $cell)
                                    <td>{!! $cell !!}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </x-adminlte-datatable>
                </div>
            </div>
        </div>
    </div>
</div>


@stop

@section('js')
    <script>

    </script>
@endsection
