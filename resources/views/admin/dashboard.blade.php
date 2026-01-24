@extends('layouts.app')

@section('page_title', $page['pageTitle'])

@section('content')
<div class="container">

    <!-- Main Content -->
    <div class="flex-grow-1 p-0" style="background:#f5f5f5;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-4" style="color:#b71c1c;">{{ $page['pageName'] }}</h3>
            <span>Welcome, <strong>{{ auth()->user()->name }}</strong></span>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

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
                        <tr @if ($shapefile->trashed()) class="table-secondary" @endif>
                            <td>{{ $shapefile->id }}</td>
                            <td>{{ ucfirst($shapefile->category) }}</td>
                            <td>{{ $shapefile->user->name ?? 'N/A' }}</td>
                            <td>{{ $shapefile->metadata->count() }}</td>
                            <td>
                                @if ($shapefile->trashed())
                                    <!-- Restore Button triggers modal -->
                                    <button type="button" class="btn btn-sm btn-success" 
                                        onclick="openRestoreModal({{ $shapefile->id }})">
                                        Restore
                                    </button>
                                @else
                                    <a href="{{ route('shapefiles.edit', $shapefile->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <!-- Delete Button triggers modal -->
                                    <button type="button" class="btn btn-sm btn-danger" onclick="openDeleteModal({{ $shapefile->id }})">
                                        Delete
                                    </button>
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
        </div>
    </div>

    @include('layouts.modal')

</div>

<style>
    .sidebar .nav-link.active {
        font-weight: bold;
        text-decoration: underline;
    }
    .card { border-radius: 10px; background: white; }
    .delete-modal {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    .delete-box {
        background: #fff;
        padding: 25px;
        border-radius: 12px;
        width: 380px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
</style>

@push('scripts')
<script>
    function openDeleteModal(id) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        form.action = `/shapefiles/${id}`;
        modal.style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    function openRestoreModal(id) {
        const modal = document.getElementById('restoreModal');
        const form = document.getElementById('restoreForm');
        form.action = `/shapefiles/${id}/restore`;
        modal.style.display = 'flex';
    }

    function closeRestoreModal() {
        document.getElementById('restoreModal').style.display = 'none';
    }
</script>
@endpush
@endsection
