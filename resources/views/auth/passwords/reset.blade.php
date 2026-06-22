@extends('auth.main')

@section('title', 'Create Password')

@section('content')

<section class="auth-page-wrapper py-5 position-relative d-flex align-items-center justify-content-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="card mb-0">
                    <div class="row g-0 align-items-center">
                        <div class="col-xxl-5">
                            <div class="card auth-card bg-secondary h-100 border-0 shadow-none d-none d-sm-block mb-0">
                                <div class="card-body py-5 d-flex justify-content-between flex-column">
                                    <div class="text-center">
                                        <h3 class="text-white">Fuel Management System.</h3>
                                        {{-- <p class="text-white opacity-75 fs-base">It brings together your
                                            tasks,
                                            projects, timelines, files and more</p> --}}
                                    </div>

                                    <div
                                        class="auth-effect-main my-5 position-relative rounded-circle d-flex align-items-center justify-content-center mx-auto">
                                        <div
                                            class="effect-circle-1 position-relative mx-auto rounded-circle d-flex align-items-center justify-content-center">
                                            <div
                                                class="effect-circle-2 position-relative mx-auto rounded-circle d-flex align-items-center justify-content-center">
                                                <div
                                                    class="effect-circle-3 mx-auto rounded-circle position-relative text-white fs-4xl d-flex align-items-center justify-content-center">
                                                    Welcome to <span class="text-primary ms-1">FMS</span>
                                                </div>
                                            </div>
                                        </div>
                                        <ul class="auth-user-list list-unstyled">
                                            <li>
                                                <div class="avatar-sm d-inline-block">
                                                    <div
                                                        class="avatar-title bg-white shadow-lg overflow-hidden rounded-circle">
                                                        <img src="{{ asset('assets/images/users/avatar-1.jpg') }}"
                                                            alt="" class="img-fluid">
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="avatar-sm d-inline-block">
                                                    <div
                                                        class="avatar-title bg-white shadow-lg overflow-hidden rounded-circle">
                                                        <img src="{{ asset('assets/images/users/avatar-2.jpg') }}"
                                                            alt="" class="img-fluid">
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="avatar-sm d-inline-block">
                                                    <div
                                                        class="avatar-title bg-white shadow-lg overflow-hidden rounded-circle">
                                                        <img src="{{ asset('assets/images/users/avatar-3.jpg') }}"
                                                            alt="" class="img-fluid">
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="avatar-sm d-inline-block">
                                                    <div
                                                        class="avatar-title bg-white shadow-lg overflow-hidden rounded-circle">
                                                        <img src="{{ asset('assets/images/users/avatar-4.jpg') }}"
                                                            alt="" class="img-fluid">
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="avatar-sm d-inline-block">
                                                    <div
                                                        class="avatar-title bg-white shadow-lg overflow-hidden rounded-circle">
                                                        <img src="{{ asset('assets/images/users/avatar-5.jpg') }}"
                                                            alt="" class="img-fluid">
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="text-center">
                                        <p class="text-white opacity-75 mb-0 mt-3">
                                            &copy; <script>
                                                document.write(new Date().getFullYear())
                                            </script>. Coded with <i class="mdi mdi-heart text-danger"></i> by
                                            JOREB
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end col-->

                        <div class="col-xxl-6 mx-auto">
                            <div class="card mb-0 border-0 shadow-none mb-0">
                                <div class="card-body p-sm-5 m-lg-4">
                                    <div class="text-center">
                                        <h5 class="fs-3xl">Create new password</h5>
                                        <p class="text-muted mb-5">Your new password must be different from
                                            previous used password.</p>
                                    </div>

                                    @if (session()->has('error'))
                                    <div class="alert alert-danger mb-4 text-center" role="alert">
                                          <strong> {{ session('error') }} </strong>
                                    </div>
                                  @endif
                                  
                                    <div class="p-2">
                                        <form method="POST" action="{{ route('password.update') }}">
                                            @csrf
                                            <input type="hidden" name="token" value="{{ $token }}">
                                            <div class="mb-3">
                                                <label class="form-label" for="password-input">Email</label>
                                                <div class="position-relative auth-pass-inputgroup">
                                                    <input type="email" class="form-control pe-5 password-input" name="email" value="{{ $email ?? old('email') }}" placeholder="Enter password">
                                                    @error('email')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>

                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="password-input">Password</label>
                                                <div class="position-relative auth-pass-inputgroup">
                                                    <input type="password" id="password" class="form-control pe-5 password-input @error('password') is-invalid @enderror" name="password" placeholder="Enter password" required autocomplete="new-password">

                                                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                                        type="button" id="password-addon"><i
                                                            class="ri-eye-fill align-middle"></i></button>
                                                            @error('password')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                            @enderror
                                                </div>

                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="password-input">Confirm Password</label>
                                                <div class="position-relative auth-pass-inputgroup">
                                                    <input type="password" class="form-control pe-5 password-input" name="password_confirmation" placeholder="Confirm password">

                                                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                                        type="button" id="password-addon"><i
                                                            class="ri-eye-fill align-middle"></i></button>
                                                </div>
                                            </div>


                                            <div class="mt-4">
                                                <button class="btn btn-primary w-100" type="submit">Reset
                                                    Password</button>
                                            </div>

                                        </form>
                                    </div>
                                    <div class="mt-4 text-center">
                                        <p class="mb-0">Wait, I remember my password... <a href="{{ route('login') }}"
                                                class="fw-semibold text-primary text-decoration-underline">
                                                Click here </a> </p>
                                    </div>
                                </div><!-- end card body -->
                            </div><!-- end card -->
                        </div>

                        <!--end col-->
                    </div>
                    <!--end row-->
                </div>
            </div>
            <!--end col-->
        </div>
        <!--end row-->

    </div>
    <!--end container-->
</section>

@endsection