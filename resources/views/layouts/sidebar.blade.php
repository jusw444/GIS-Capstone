<div class="sidebar">

    <!-- SIDEBAR HEADER -->
    <div class="sidebar-header">
        @switch(Auth::user()->role)
            @case('super_admin')
                Super Admin
                @break

            @case('admin')
                Admin Panel
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
                        <span>Create Admin</span>
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

        <!-- PUSH LOGOUT TO BOTTOM -->
        <li class="nav-spacer"></li>

        {{-- LOGOUT --}}
        <li class="nav-item logout">
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit"
                        class="btn w-100 text-start text-danger border-0 bg-transparent">
                    <i class="bi bi-power"></i>
                    <span>Logout</span>
                </button>
            </form>
        </li>

    </ul>
</div>
