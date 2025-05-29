
@extends('adminlte::page')

@section('title', 'Employee Attendance')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

@section('css')
<style>
    /* .schedulelist 
    {

    } */
</style>
@section('content_header')
   <h1 class="ml-1">LIST OF SCHEDULE</h1>
@stop

@section('content')
<button id="save_sched" class="btn btn-primary mt-3" data-toggle="modal" data-target="#addschedulemodal">CREATE A SCHEDULE</button>
    <div class="card"></div>
   <tbody>
@foreach($schedules as $schedule)
    <tr>
        <td>
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Code</th>
                        <th>Shift</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Break In</th>
                        <th>Break Out</th>
                        <th>Working Hours</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $schedule->shift }}</td>
                        <td>{{ $schedule->desc }}</td>
                        <td>{{ $schedule->xptd_time_in }}</td>
                        <td>{{ $schedule->xptd_time_out }}</td>
                        <td>{{ $schedule->xptd_brk_in }}</td>
                        <td>{{ $schedule->xptd_brk_out }}</td>
                        <td>{{ $schedule->wrkhrs }}</td>
                        <td>{{ $schedule->stat }}</td>
                    </tr>
                </tbody>
            </table>
        </td>
@endforeach
</tbody>


                </tbody>
            </table>
        </div>
    </div>
    <!-- MODAL FOR CREATING SCHEDULE -->
    <div class="modal fade" id="addschedulemodal" tabindex="-1" role="dialog" aria-labelledby="addschedulemodallb" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('schedule.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addschedulemodallb">Add Payroll</h5>
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
                            <label for="shftcd">Shift Code</label>
                            <input type="text" name="shiftcode" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="shftdesc">Description</label>
                            <input type="text" name="shiftdesc" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="sfttm_in">Time IN</label>
                            <input type="time" name="shifttime_in" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="sfttm_out">Time OUT</label>
                            <input type="time" name="shifttime_out" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="brk_in">Break IN</label>
                            <input type="time" name="break_in" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="brk_out">Break OUT</label>
                            <input type="time" name="break_out" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="tl_hrs">Working Hours</label>
                            <input type="number" name="totalhours" class="form-control" required>
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