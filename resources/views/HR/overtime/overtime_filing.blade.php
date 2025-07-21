@extends('adminlte::page')

@section('title', 'Dashboard')

@section('head')
    <link rel="icon" type="image/x-icon" href="{{ asset('LOGO.ico') }}">
@endsection

@section('content_header')
    <h1 class="ml-1">File Overtime</h1>
@stop

@section('content')
<div class="container">
    <div class="card shadow">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="employeeTabs" role="tablist">
            </ul>
        </div>

        <div class="card-body">
            <form action="{{ route('overtime.store') }}" method="POST">

                @csrf

                {{-- Display general errors at the top of the form --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Display success message --}}
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="tab-content" id="ot_data">
                    <div class="tab-pane fade show active" id="personal" role="tabpanel">
                        <div class="form-group">
                            <label for="employee_id">Employee ID</label>
                            {{-- Use old() to repopulate input field after validation error --}}
                            <input type="text" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" placeholder="Enter Employee ID" value="{{ old('employee_id') }}" required>
                            @error('employee_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror

                            <label for="ot_date" class="mt-3">Date</label>
                            {{-- Add is-invalid class and error message for ot_date --}}
                            <input type="date" name="ot_date" class="form-control @error('ot_date') is-invalid @enderror" value="{{ old('ot_date') }}" required>
                            @error('ot_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror

                            <label for="ot_in" class="mt-3">Overtime Time In</label>
                            <input type="time" name="ot_in" class="form-control @error('ot_in') is-invalid @enderror" value="{{ old('ot_in') }}" required>
                            @error('ot_in')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror

                            <label for="ot_out" class="mt-3">Overtime Time Out</label>
                            <input type="time" name="ot_out" class="form-control @error('ot_out') is-invalid @enderror" value="{{ old('ot_out') }}" required>
                            @error('ot_out')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-success">Save Overtime</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
{{-- You can add any JavaScript here if needed --}}
@endsection