<div class="app-menu navbar-menu">
	<!-- LOGO -->
	<div class="navbar-brand-box">
		<a href="/" class="logo logo-dark">
			<h3 class="text-light mt-4">{{ config('app.name') }}</h3>
		</a>
		<a href="/" class="logo logo-light">
			<h3 class="text-light mt-4">{{ config('app.name') }}</h3>
		</a>
		<button type="button" class="btn btn-sm p-0 fs-3xl header-item float-end btn-vertical-sm-hover"
			id="vertical-hover">
			<i class="ri-record-circle-line"></i>
		</button>
	</div>

	<div id="scrollbar">
		<div class="container-fluid">

			<div id="two-column-menu">
			</div>
			<ul class="navbar-nav" id="navbar-nav">
			<li class="menu-title"><span data-key="t-menu">Menu </span></li>
			<li class="nav-item">
				<a class="nav-link menu-link {{ request()->routeIs('home') ? 'active' : null }}" href="{{ route('home') }}">
					<i class="ph-gauge"></i> <span data-key="t-dashboards">Dashboard</span>
				</a>
			</li>

			@if (auth()->user()->super_admin)
			<li class="nav-item">
				<a href="#sidebarTickets" class="nav-link menu-link collapsed {{ request()->routeIs('users', 'logs') ? 'active' : null }}"
					 data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarTickets">
					<i class="ph-ticket"></i> <span data-key="t-support-tickets">System Management</span>
				</a>
			    <div class=" collapse menu-dropdown {{ request()->routeIs
					('users', 'logs', 'employees', 'roles', 'permissions', 'import.facilities', 'tenants', 'facility-type', 'materials.index', 'jobs')
					? 'show ' : null }}"
					  id="sidebarTickets">
					<ul class="nav nav-sm flex-column">
						<li class="nav-item">
							<a href="{{ route('users') }}" class="nav-link {{ request()->routeIs('users') ? 'active' : null }}" data-key="t-list-users">Users</a>
						</li>
						<li class="nav-item">
							<a href="{{ route('roles') }}"
								class="nav-link {{ request()->routeIs('roles') ? 'active' : null }}"
								data-key="t-list-roles">Roles</a>
						</li>
						<li class="nav-item">
							<a href="{{ route('permissions') }}"
								class="nav-link {{ request()->routeIs('permissions') ? 'active' : null }}"
								data-key="t-list-permissions">Permissions</a>
						</li>
						<li class="nav-item">
							<a href="{{ route('logs') }}"
								class="nav-link {{ request()->routeIs('logs') ? 'active' : null }}"
								data-key="t-list-logs">Logs</a>
						</li>
						<li class="nav-item">
							<a href="{{ route('user.sessions') }}"
								class="nav-link {{ request()->routeIs('user.sessions') ? 'active' : null }}"
								data-key="t-list-logs">User Sessions</a>
						</li>
						<li class="nav-item">
							<a href="{{ route('jobs') }}"
								class="nav-link {{ request()->routeIs('jobs') ? 'active' : null }}"
								data-key="t-list-jobs">Jobs Monitoring</a>
						</li>
					</ul>
				</div>
			</li>
			@endif
			{{-- <li class="nav-item">
				<a href="{{ route('employees') }}"
					class="nav-link menu-link {{ request()->routeIs('employees') ? 'active' : null }}">
					<i class="ri-building-2-line"></i> <span data-key="t-calendar">Employees</span>
				</a>
			</li> --}}
			<li class="nav-item">
				<a class="nav-link menu-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
					 <i class="mdi mdi-logout"></i> <span data-key="t-calendar">Logout</span>
					 <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
						@csrf
					</form>
				</a>
			</li>

		</ul>

		</div>
		<!-- Sidebar -->
	</div>

	<div class="sidebar-background"></div>
</div>