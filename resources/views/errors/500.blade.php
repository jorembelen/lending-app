@extends('errors.main')

@section('title', 'Error 500')

@section('content')
    
<div class="row justify-content-center h-100 align-items-center">
    <div class="col-md-12">
        <div class="form-input-content text-center error-page">
            <h1 class="error-text font-weight-bold">500</h1>
            <h4 class="text-nowrap"><i class="fa fa-times-circle text-danger"></i> Internal Server Error</h4>
            <p>You do not have permission to view this resource. Please contact the system administrator.</p> 
            <div>
                <a class="btn btn-primary" href="/">Back to Home</a>
            </div>	
        </div>
    </div>
</div>


@endsection