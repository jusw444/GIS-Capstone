

<!-- SIDEBAR -->
<div class="sidebar">

    <!-- SIDEBAR HEADER -->
    <div class="sidebar-header">
        <div class="logo">
            <i class="bi bi-globe2"></i>
        </div>

        <div class="system-info">
            <div class="system-title">GIS System</div>
            <div class="system-role">
                @switch(Auth::user()->role)
                    @case('super_admin')
                        Super Admin Panel
                        @break
                    @case('admin')
                        {{ ucfirst(str_replace('_',' ', $adminCategory)) }} Admin
                        @break
                    @default
                        User Panel
                @endswitch
            </div>
        </div>
    </div>


    <!-- NAVIGATION -->
    <ul class="sidebar-nav">

        <li class="nav-section">Navigation</li>

        @switch(Auth::user()->role)

        {{-- ================= ADMIN ================= --}}
        @case('admin')

            <li class="nav-item {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <div class="nav-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item {{ Route::is('admin.view') ? 'active' : '' }}">
                <a href="{{ route('admin.view') }}">
                    <div class="nav-icon">
                        <i class="bi bi-map"></i>
                    </div>
                    <span>GIS Map Viewer</span>
                </a>
            </li>

            <li class="nav-item {{ Route::is('shapefiles.create') ? 'active' : '' }}">
                <a href="{{ route('shapefiles.create') }}">
                    <div class="nav-icon">
                        <i class="bi bi-plus-square"></i>
                    </div>
                    <span>Create Spatial Data</span>
                </a>
            </li>

            <li class="nav-item {{ Route::is('admin.shapefile.upload') ? 'active' : '' }}">
                <a href="{{ route('admin.shapefile.upload') }}">
                    <div class="nav-icon">
                        <i class="bi bi-upload"></i>
                    </div>
                    <span>Upload Shapefile</span>
                </a>
            </li>

            {{-- <li class="nav-section">Management</li>

            <li class="nav-item">
                <a href="#">
                    <div class="nav-icon">
                        <i class="bi bi-gear"></i>
                    </div>
                    <span>GIS Settings</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="#">
                    <div class="nav-icon">
                        <i class="bi bi-bar-chart"></i>
                    </div>
                    <span>Reports & Analytics</span>
                </a>
            </li> --}}

        @break



        {{-- ================= SUPER ADMIN ================= --}}
        @case('super_admin')

            <li class="nav-item {{ Route::is('superadmin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('superadmin.dashboard') }}">
                    <div class="nav-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item {{ Route::is('admin.view') ? 'active' : '' }}">
                <a href="{{ route('admin.view') }}">
                    <div class="nav-icon">
                        <i class="bi bi-map"></i>
                    </div>
                    <span>GIS Map Viewer</span>
                </a>
            </li>

            <li class="nav-section">Administration</li>

            <li class="nav-item {{ Route::is('superadmin.admins.create') ? 'active' : '' }}">
                <a href="{{ route('superadmin.admins.create') }}">
                    <div class="nav-icon">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <span>Create Admin</span>
                </a>
            </li>

            <li class="nav-item {{ Route::is('superadmin.classifications') ? 'active' : '' }}">
                <a href="{{ route('superadmin.classifications') }}">
                    <div class="nav-icon">
                        <i class="bi bi-tags"></i>
                    </div>
                    <span>Classifications</span>
                </a>
            </li>

            <li class="nav-item {{ Route::is('superadmin.users') ? 'active' : '' }}">
                <a href="{{ route('superadmin.users') }}">
                    <div class="nav-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <span>Users & Admins</span>
                </a>
            </li>

        @break



        {{-- ================= USER ================= --}}
        @case('user')

            <li class="nav-item">
                <a href="#">
                    <div class="nav-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="#">
                    <div class="nav-icon">
                        <i class="bi bi-map"></i>
                    </div>
                    <span>View Maps</span>
                </a>
            </li>

        @break

        @endswitch


        <!-- PUSH TO BOTTOM -->
        <li class="nav-spacer"></li>



        <!-- USER ACCOUNT -->
        <li class="user-menu">

            <div class="user-trigger">

                <div class="user-avatar">
                    {{ strtoupper(substr(Auth::user()->name,0,1)) }}
                </div>

                <div class="user-info">
                    <div class="user-name">
                        {{ Auth::user()->name }}
                    </div>

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
<button id="sidebar-toggle" class="sidebar-toggle">
    <i class="bi bi-x-lg"></i>
</button>

<script>
    const toggleBtn = document.getElementById('sidebar-toggle');
const sidebar = document.querySelector('.sidebar');

toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('sidebar-hidden');

    // 👉 THIS IS THE KEY FIX
    document.body.classList.toggle('sidebar-collapsed');

    // Change icon
    const icon = toggleBtn.querySelector('i');
    if (sidebar.classList.contains('sidebar-hidden')) {
        icon.classList.remove('bi-x-lg');
        icon.classList.add('bi-list');
    } else {
        icon.classList.remove('bi-list');
        icon.classList.add('bi-x-lg');
    }
});
</script>