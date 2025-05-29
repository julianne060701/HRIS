@extends('adminlte::page')

@section('title', 'Schedule Holiday')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

@section('content_header')
    <h1>Schedule Holiday</h1>
@endsection

@section('content')

    {{-- Success message --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Card --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Holiday List</h3>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addHolidayModal">
                Add Holiday
            </button>
        </div>

        <div class="card-body">
            <table id="holiday-table" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Holiday Name</th>
                        <th>Date</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($holidays as $index => $holiday)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $holiday->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($holiday->date)->format('F d, Y') }}</td>
                            <td>{{ $holiday->type }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Holiday Modal --}}
    <div class="modal fade" id="addHolidayModal" tabindex="-1" role="dialog" aria-labelledby="addHolidayModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('holidays.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Holiday</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Holiday Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="type">Type</label>
                            <input type="text" name="type" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description (optional)</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save Holiday</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('#holiday-table').DataTable();
        });
    </script>
@endsection
