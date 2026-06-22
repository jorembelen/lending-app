
@extends('auth.main')

@section('title', '2FA')

@section('content')


<div class="form-content">


    <div class="card-header">
        <h5>Disable Two Factor Authentication</h5>
    </div>
    <div class="card-body">

        <p>If you are looking to disable Two Factor Authentication. Please confirm your password and Click Disable 2FA Button.</p>


        <form class="form-horizontal" method="POST" action="{{ route('disable2fa') }}">
            {{ csrf_field() }}
            <div class="form-group{{ $errors->has('current-password') ? ' has-error' : '' }}">
                <input id="current-password" type="password" class="form-control col-md-12 text-center" name="current-password" required placeholder="Enter current password">
                @if(session()->has('error'))
                <div  class="invalid-feedback animated fadeInUp" style="display: block;">{{ session('error') }}</div>
                @endif
            </div>
            <button class="btn btn-primary" type="submit">Disable 2FA</button>
        </form>
    </div>
</div>


@endsection



