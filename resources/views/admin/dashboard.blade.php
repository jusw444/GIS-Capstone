@extends('layouts.app')

@section('content')
<div class="d-flex" style="min-height: 100vh; font-family: 'Nunito', sans-serif;">

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column" style="width:250px; background:#b71c1c; color:white; padding:30px;">
        <h2 class="mb-4" style="font-weight:bold;">Admin Panel</h2>
        <ul class="nav flex-column">
            <li class="nav-item mb-3">
                <a href="{{ route('admin.dashboard') }}" class="nav-link text-white" style="text-decoration:none;">Dashboard</a>
            </li>
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white" style="text-decoration:none;">Manage Users</a>
            </li>
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white" style="text-decoration:none;">Manage Shapefiles</a>
            </li>
            <li class="nav-item mb-3">
                <a href="{{ route ('admin.view') }}" class="nav-link text-white" style="text-decoration:none;">View Map</a>
            </li>
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white" style="text-decoration:none;">Reports</a>
            </li>
            <li class="mt-auto">
                <a href="{{ route('logout') }}" class="nav-link text-white" style="text-decoration:none;">Logout</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content flex-grow-1 p-4" style="background:#f5f5f5;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0" style="color:#b71c1c;">Dashboard</h1>
            <span>Welcome, <strong>{{ auth()->user()->name }}</strong></span>
        </div>

        <!-- Top Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm" style="border-left: 5px solid #b71c1c; padding:20px;">
                    <h5>Total Users</h5>
                    <p>{{ $totalUsers ?? '0' }}</p>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm" style="border-left: 5px solid #b71c1c; padding:20px;">
                    <h5>Total Shapefiles</h5>
                    <p>{{ $totalShapefiles ?? '0' }}</p>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm" style="border-left: 5px solid #b71c1c; padding:20px;">
                    <h5>System Activities</h5>
                    <p>Coming soon...</p>
                </div>
            </div>
        </div>

        <!-- Shapefiles Table -->
        <div class="card shadow-sm p-3">
            <h4 class="mb-3" style="color:#b71c1c;">Shapefiles</h4>
            <a href="{{ route('shapefiles.create') }}" class="btn btn-danger mb-3">+ Create Shapefile</a>

            <table class="table table-striped table-hover">
                <thead style="background:#b71c1c; color:white;">
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Created By</th>
                        <th>Metadata Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shapefiles as $shapefile)
                    <tr @if($shapefile->trashed()) class="table-secondary" @endif>
                        <td>{{ $shapefile->id }}</td>
                        <td>{{ ucfirst($shapefile->category) }}</td>
                        <td>{{ $shapefile->user->name ?? 'N/A' }}</td>
                        <td>{{ $shapefile->metadata->count() }}</td>
                        <td>
                            @if($shapefile->trashed())
                                <!-- Restore Button -->
                                <form action="{{ route('shapefiles.restore', $shapefile->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Restore this shapefile?')">Restore</button>
                                </form>
                            @else
                                <a href="{{ route('shapefiles.edit', $shapefile->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('shapefiles.destroy', $shapefile->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this shapefile?')">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No shapefiles yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $shapefiles->links() }}
        </div>
    </div>
</div>

<style>
    .sidebar .nav-link.active {
        font-weight: bold;
        text-decoration: underline;
    }
    .card {
        border-radius: 10px;
        background:white;
    }
</style>
@endsection
