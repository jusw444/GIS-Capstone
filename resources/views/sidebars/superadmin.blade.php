<!-- Sidebar -->
    <div style="width:250px; background:#343a40; color:white; padding:20px;">
        <h3>Super Admin</h3>
        <ul style="list-style:none; padding:0; margin-top:30px;">
            <li style="margin-bottom:15px;">
                <a href="{{ route('superadmin.dashboard') }}" style="color:white; text-decoration:none;">Dashboard</a>
            </li>
            <li style="margin-bottom:15px;">
                <a href="{{ route('superadmin.admins.create') }}" style="color:white; text-decoration:none;">Create Admin</a>
            </li>
            <li style="margin-bottom:15px;">
                <a href="{{ route('superadmin.users') }}" style="color:white; text-decoration:none;">All Users/Admins</a>
            </li>
            <li style="margin-top:30px;">
                <a href="{{ route('logout') }}" style="color:white; text-decoration:none;">Logout</a>
            </li>
        </ul>
    </div>
