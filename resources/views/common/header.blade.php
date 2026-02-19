<nav class="navbar navbar-expand navbar-light navbar-bg">
	<a class="sidebar-toggle js-sidebar-toggle">
		<i class="hamburger align-self-center"></i>
	</a>
	<div class="pt-2">
		<h4 class="h4">Document Management System</h4>
	</div>
	<div class="navbar-collapse collapse">
		<ul class="navbar-nav navbar-align">
			<li class="nav-item dropdown">
				<a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
					<i class="align-middle" data-feather="settings"></i>
				</a>
				@php
				$user = Auth::user();
				$profileImage = 'profile_images/default.jpg';
				try {
					if (\Illuminate\Support\Facades\Schema::hasTable('user_profiles')) {
						$profileImage = $user->profile ? $user->profile->image : 'profile_images/default.jpg';
					}
				} catch (\Throwable) {
					$profileImage = 'profile_images/default.jpg';
				}
				@endphp
				<a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
					<img src="{{ asset('storage/'.$profileImage ) }}" class="avatar img-fluid rounded me-1" ><span class="text-dark">{{ Auth::user()->name }}</span>
				</a>

				<div class="dropdown-menu dropdown-menu-end">
					<a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="align-middle me-1" data-feather="user"></i>Profile</a>
					<form method="POST" action="{{ route('logout') }}">
						@csrf
						<button type="submit" class="dropdown-item">
							<i class="align-middle me-1" data-feather="log-out"></i>
							Log Out
						</button>
					</form>
				</div>
			</li>
		</ul>
	</div>
</nav>