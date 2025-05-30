@extends('adminlte::page')

@section('title', 'Employee Attendance')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

@section('content_header')
    <h1 class="ml-1">LIST OF SCHEDULE</h1>
@stop

@section('content')
    <div class="mb-3">
        <button id="save_sched" class="btn btn-primary" data-toggle="modal" data-target="#addschedulemodal">
            CREATE A SCHEDULE
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="scheduleTable" class="table table-bordered table-striped table-sm">
                <thead class="thead-dark">
                    <tr>
                        <th>Shift Code</th>
                        <th>Description</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Break In</th>
                        <th>Break Out</th>
                        <th>Working Hours</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->shift }}</td>
                            <td>{{ $schedule->desc }}</td>
                            <td>{{ $schedule->xptd_time_in }}</td>
                            <td>{{ $schedule->xptd_time_out }}</td>
                            <td>{{ $schedule->xptd_brk_in }}</td>
                            <td>{{ $schedule->xptd_brk_out }}</td>
                            <td>{{ $schedule->wrkhrs }}</td>
                            <td>
                                @if($schedule->stat === 'Active')
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Not Active</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Creating Schedule -->
    <div class="modal fade" id="addschedulemodal" tabindex="-1" role="dialog" aria-labelledby="addschedulemodallb" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('schedule.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addschedulemodallb">Add Schedule</h5>
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
                            <label>Shift Code</label>
                            <input type="text" name="shiftcode" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" name="shiftdesc" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Time IN</label>
                            <input type="time" name="shifttime_in" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Time OUT</label>
                            <input type="time" name="shifttime_out" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Break IN</label>
                            <input type="time" name="break_in" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Break OUT</label>
                            <input type="time" name="break_out" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Working Hours</label>
                            <input type="number" name="totalhours" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
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
    $(function () {
        $('#scheduleTable').DataTable({
            responsive: true,
            autoWidth: false
        });
    });
</script>
@endsection
