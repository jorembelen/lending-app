
@extends('auth.main')

@section('title', 'One Time Password')

@section('content')


<div class="form-content">
    <div class="card-header">
        <h3>One Time Password</h3>
    </div>
    <div class="card-body">
        @if($errors->any())
        <div class="alert text-white bg-danger" role="alert">
            <div class="mm-alert-icon">
                <i class="ri-information-line"></i>
            </div>
            <div class="mm-alert-text">{{ $errors->first() }}</div>
        </div>
        @endif
        <div class="text-center">

            <p>Please enter the  <strong>OTP</strong> generated on your <br> Authenticator App.  Ensure you submit <br> the current one because it refreshes every 30 seconds.</p>
        </div>
        <form class="form-horizontal" method="POST" action="{{ route('2faVerify') }}">
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <input  type="number" class="form-control text-center" name="one_time_password" required autofocus>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
    </div>
</div>


@endsection



