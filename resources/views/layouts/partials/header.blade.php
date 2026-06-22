
<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                   <div class="navbar-brand-box horizontal-logo">
                    <a href="/" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="/assets/logo.png" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="/assets/logo.png" alt="" height="22">
                        </span>
                    </a>

                    <a href="/" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="/assets/logo.png" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="/assets/logo.png" alt="" height="60">
                        </span>
                    </a>
                </div>
                
                <div class="d-none d-md-block d-xs-block mt-4 logo-lg">
                    <h3 class="text-light">{{ config('app.name') }}</h3>
                </div>
                <div class=".d-none d-xs-block d-sm-block d-md-none mt-4">
                    <h3 class="text-light">ETA</h3>
                </div>
                <button type="button"
                    class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger shadow-none"
                    id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

            </div>

            <div class="d-flex align-items-center">


                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn shadow-none" id="page-header-user-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user"
                            avatar="{{ auth()->user()->name }}" alt="avatar">
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ auth()->user()->name }}</span>
                                {{-- <span class="d-none d-xl-block ms-1 fs-sm user-name-sub-text">{{ auth()->user()->role_name }}</span> --}}
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <h6 class="dropdown-header">Welcome {{ auth()->user()->name }}!</h6>
                        <a class="dropdown-item" href="{{ route('profile') }}">
                            <i class="mdi mdi-account-circle text-muted fs-lg align-middle me-1"></i> 
                            <span class="align-middle">Profile</span>
                        </a>
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="mdi mdi-logout text-muted fs-lg align-middle me-1"></i> <span
                                class="align-middle" data-key="t-logout">Logout</span>
                                <form id="logout-form-header" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>