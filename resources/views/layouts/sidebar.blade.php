<div class="sidebar">

    <!-- SIDEBAR HEADER -->
    <div class="sidebar-header">
        @switch(Auth::user()->role)
            @case('super_admin')
                Super Admin
                @break
            @case('admin')
                Admin Panel ({{ Auth::user()->category }})
                @break
            @default
                User Panel
        @endswitch
    </div>

    <!-- NAV -->
    <ul class="sidebar-nav">

        @switch(Auth::user()->role)

            {{-- ================= ADMIN ================= --}}
            @case('admin')

                <li class="nav-item {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="nav-item {{ Route::is('admin.view') ? 'active' : '' }}">
                    <a href="{{ route('admin.view') }}">
                        <i class="bi bi-map"></i>
                        <span>GIS Map Viewer</span>
                    </a>
                </li>

                <li class="nav-item {{ Route::is('shapefiles.create') ? 'active' : '' }}">
                    <a href="{{ route('shapefiles.create') }}">
                        <i class="bi bi-file-earmark-plus"></i>
                        <span>Create Shapefile</span>
                    </a>
                </li>

                <li class="nav-item {{ Route::is('admin.shapefile.upload') ? 'active' : '' }}">
                    <a href="{{ route('admin.shapefile.upload') }}">
                        <i class="bi bi-upload"></i>
                        <span>Upload Shapefile</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#">
                        <i class="bi bi-gear"></i>
                        <span>GIS Settings</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Reports</span>
                    </a>
                </li>

                @break


            {{-- ================= SUPER ADMIN ================= --}}
            @case('super_admin')

                <li class="nav-item {{ Route::is('superadmin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('superadmin.dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="nav-item {{ Route::is('superadmin.admins.create') ? 'active' : '' }}">
                    <a href="{{ route('superadmin.admins.create') }}">
                        <i class="bi bi-person-plus"></i>
                        <span>Create Account</span>
                    </a>
                </li>

                <li class="nav-item {{ Route::is('superadmin.users') ? 'active' : '' }}">
                    <a href="{{ route('superadmin.users') }}">
                        <i class="bi bi-people"></i>
                        <span>Users & Admins</span>
                    </a>
                </li>

                @break


            {{-- ================= USER ================= --}}
            @case('user')

                <li class="nav-item">
                    <a href="#">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#">
                        <i class="bi bi-map"></i>
                        <span>View Maps</span>
                    </a>
                </li>

                @break

        @endswitch

        <!-- PUSH TO BOTTOM -->
        <li class="nav-spacer"></li>

        {{-- USER ACCOUNT MENU --}}
<li class="nav-item user-menu">
    <div class="user-trigger">
        <div class="user-avatar">
            <i class="bi bi-person-circle"></i>
        </div>
        <div class="user-info">
            <div class="user-name">{{ Auth::user()->name }}</div>
            <div class="user-role">
                {{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}
            </div>
        </div>
        <i class="bi bi-chevron-up user-caret"></i>
    </div>

    <div class="user-dropdown">
        <a href="{{ route('password.request') }}">
            <i class="bi bi-shield-lock"></i>
            <span>Change Password</span>
        </a>

        <div class="dropdown-divider"></div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-action">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</li>

    </ul>
</div>
