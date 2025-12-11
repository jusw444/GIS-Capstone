<!-- Sidebar -->
    <div style="width:250px; background:#212529; color:white; padding:20px;">
        <h3>Admin Panel</h3>
        <ul style="list-style:none; padding:0; margin-top:30px;">
            <li style="margin-bottom:15px;">
                <a href="{{ route('admin.dashboard') }}" style="color:white; text-decoration:none;">Dashboard</a>
            </li>

            <li style="margin-bottom:15px;">
                <a href="#" style="color:white; text-decoration:none;">Manage Users</a>
            </li>

            <li style="margin-bottom:15px;">
                <a href="#" style="color:white; text-decoration:none;">Manage Features</a>
            </li>

            <li style="margin-bottom:15px;">
                <a href="#" style="color:white; text-decoration:none;">Reports</a>
            </li>

            <li style="margin-top:30px;">
                <a href="{{ route('logout') }}" style="color:white; text-decoration:none;">Logout</a>
            </li>
        </ul>
    </div>
