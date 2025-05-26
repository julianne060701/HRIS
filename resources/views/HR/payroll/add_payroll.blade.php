@extends('adminlte::page')

@section('title', 'Payroll')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

@section('content_header')
    <h1 class="ml-1">Payroll List</h1>
@stop

@section('content')
    <!-- Add Payroll Button -->
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary px-5" data-toggle="modal" data-target="#addPayrollModal">Add Payroll</button>
    </div>

    <!-- Payroll Table -->
    <div class="card">
        <div class="card-body">
            <table id="payrollTable" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Payroll Code</th>
                        <th>Title</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payrollData as $payroll)
                        <tr>
                            <td>{{ $payroll['id'] }}</td>
                            <td>{{ $payroll['payroll_code'] }}</td>
                            <td>{{ $payroll['title'] }}</td>
                            <td>{{ $payroll['from_date'] }}</td>
                            <td>{{ $payroll['to_date'] }}</td>
                            <td>{!! $payroll['actions'] !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Payroll Modal -->
    <div class="modal fade" id="addPayrollModal" tabindex="-1" role="dialog" aria-labelledby="addPayrollModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('add-payroll.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPayrollModalLabel">Add Payroll</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="payroll_code">Payroll Code</label>
                            <input type="text" name="payroll_code" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="from_date">From Date</label>
                            <input type="date" name="from_date" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="to_date">To Date</label>
                            <input type="date" name="to_date" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="Active">Active</option>
                                <option value="NotActive">Not Active</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function () {
        $('#payrollTable').DataTable({
            responsive: true,
            autoWidth: false,
            ordering: true,
            pageLength: 10,
        });
    });
</script>
@endsection
