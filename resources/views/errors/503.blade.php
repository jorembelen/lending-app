@extends('errors.main')

@section('title', 'Under Maintenance')

@section('content')
    
<div class="row justify-content-center h-100 align-items-center">
    <div class="col-md-12">
        <div class="form-input-content text-center error-page">
            <h1 class="error-text font-weight-bold">503</h1>
            <h4 class="text-nowrap"><i class="fa fa-times-circle text-danger"></i> Service Unavailable</h4>
            <p>Sorry, we are under maintenance! Please contact the system administrator.</p>
        </div>
    </div>
</div>


@endsection