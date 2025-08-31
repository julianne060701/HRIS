@extends('adminlte::page')

@section('title', 'Process Payroll')

@section('content_header')
    <h1>Process Payroll</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Payroll Periods</h3>
                </div>
                <div class="card-body">
                    <p class="text-center text-muted mb-3">
                        Select a payroll period from the list below to proceed with processing.
                    </p>

                    @if ($payrollPeriods->isEmpty())
                        <div class="alert alert-info text-center" role="alert">
                            <i class="fas fa-info-circle"></i>
                            No payroll periods found.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Payroll Code</th>
                                        <th>Title</th>
                                        <th>From Date</th>
                                        <th>To Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($payrollPeriods as $period)
                                        <tr>
                                            <td>
                                                <strong>{{ $period->payroll_code }}</strong>
                                            </td>
                                            <td>{{ $period->title }}</td>
                                            <td>{{ \Carbon\Carbon::parse($period->from_date)->format('M d, Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($period->to_date)->format('M d, Y') }}</td>
                                            <td>
                                                @if($period->status == 'Processed')
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Processed
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($period->status == 'Processed')
                                                    <button class="btn btn-secondary btn-sm" disabled title="Already processed">
                                                        <i class="fas fa-check"></i> Processed
                                                    </button>
                                                @else
                                                    <a href="{{ url('payroll/process/' . $period->id) }}" 
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fas fa-cogs"></i> Process
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        console.log('Process Payroll page loaded!');
    </script>
@stop