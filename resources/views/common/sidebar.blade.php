<nav id="sidebar" class="sidebar js-sidebar fixed">
	<div class="sidebar-content js-simplebar">
		<a class="sidebar-brand d-flex justify-content-center" href="{{ route('dashboard') }}">
			<!-- <span class="align-middle ">Universal Enterprise</span> -->
			<img src="{{ asset('img/icons/logo.png') }}" class="w-75" alt="No Image">
		</a>
		<ul class="sidebar-nav">
			<li class="sidebar-header">Main</li>
			<li class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('dashboard') }}">
					<i class="align-middle" data-feather="sliders"></i> <span class="align-middle">Dashboard</span>
				</a>
			</li>

			<li class="sidebar-header">Documents</li>

			@can('view_document_audit')
			<li class="sidebar-item {{ request()->routeIs('document-audit') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('document-audit') }}">
					<i class="align-middle" data-feather="eye"></i> <span class="align-middle">Document Audit</span>
				</a>
			</li>
			@endcan

			@can('view_document_type')
			<li class="sidebar-item {{ request()->routeIs('document-type') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('document-type') }}">
					<i class="align-middle" data-feather="file-text"></i> <span class="align-middle">Document Types</span>
				</a>
			</li>
			@endcan

			<li class="sidebar-item {{ request()->routeIs('document') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('document') }}">
					<i class="align-middle" data-feather="file"></i> <span class="align-middle">Documents</span>
				</a>
			</li>

			@can('view_recycle_bin')
			<li class="sidebar-item {{ request()->routeIs('recycle-bin') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('recycle-bin') }}">
					<i class="align-middle" data-feather="trash-2"></i> <span class="align-middle">Recycle Bin</span>
				</a>
			</li>
			@endcan

			<li class="sidebar-item {{ request()->routeIs('uploads') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('uploads') }}">
					<i class="align-middle" data-feather="upload"></i> <span class="align-middle">Uploads</span>
				</a>
			</li>

			<li class="sidebar-header">Administration</li>

			@can('view_group')
			<li class="sidebar-item {{ request()->routeIs('groups') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('groups') }}">
					<i class="align-middle" data-feather="users"></i> <span class="align-middle">Groups</span>
				</a>
			</li>
			@endcan
			@can('view_user')
			<li class="sidebar-item {{ request()->routeIs('users') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('users') }}">
					<i class="align-middle" data-feather="user"></i> <span class="align-middle">Users</span>
				</a>
			</li>
			@endcan

			<li class="sidebar-header">Settings</li>
			<li class="sidebar-item {{ request()->routeIs('setting') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('setting') }}">
					<i class="align-middle" data-feather="settings"></i> <span class="align-middle">Settings</span>
				</a>
			</li>
		</ul>
	</div>
</nav>