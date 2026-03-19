@extends('layouts.app')

@section('page_title', $page['pageTitle'])

@section('content')
<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-5">
        <div>
            <h1 class="h3 fw-bold mb-2" style="color:#b71c1c;">
                {{ $page['pageName'] }} ({{ ucfirst(str_replace('_', ' ', $adminCategory)) }})
            </h1>
            <p class="text-muted mb-0">GIS Management Dashboard & Analytics</p>
        </div>
        <div class="mt-3 mt-md-0">
            <div class="d-flex align-items-center bg-white p-3 rounded-3 shadow-sm">
                <div class="me-3">
                    <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 40px; height: 40px; background-color: rgba(183, 28, 28, 0.1); color: #b71c1c;">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div>
                    <div class="small text-muted">Welcome back,</div>
                    <div class="fw-semibold">{{ auth()->user()->name }}</div>
                    <div class="small text-muted">
                        <i class="fas fa-calendar-alt me-1"></i>
                        {{ now()->format('F j, Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert"
            style="background-color: rgba(25, 135, 84, 0.1); border-left: 4px solid #198754;">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-lg me-3" style="color: #198754;"></i>
                <div class="flex-grow-1">
                    <strong class="text-success">Success!</strong>
                    <div class="text-dark mt-1">{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <!-- GIS Stats Cards -->
    <div class="row g-4 mb-5">

        <!-- Total Users -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #b71c1c;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-2">Total Users</div>
                            <div class="h2 fw-bold mb-0" style="color:#b71c1c;">{{ $totalUsers ?? 0 }}</div>
                            <div class="small text-muted mt-2">
                                <i class="fas fa-users me-1"></i> Registered users
                            </div>
                        </div>
                        <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 60px; height: 60px; background-color: rgba(183, 28, 28, 0.1);">
                            <i class="fas fa-users fa-lg" style="color:#b71c1c;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Shapefiles -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #2e7d32;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-2">Total Shapefiles</div>
                            <div class="h2 fw-bold mb-0" style="color:#2e7d32;">{{ $totalShapefiles ?? 0 }}</div>
                            <div class="small text-muted mt-2">
                                <i class="fas fa-layer-group me-1"></i> GIS datasets
                            </div>
                        </div>
                        <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 60px; height: 60px; background-color: rgba(46, 125, 50, 0.1);">
                            <i class="fas fa-map-marked-alt fa-lg" style="color:#2e7d32;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Categories -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #1565c0;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-2">Total Categories</div>
                            <div class="h2 fw-bold mb-0" style="color:#1565c0;">{{ $categoryCounts ?? 0 }}</div>
                            <div class="small text-muted mt-2">
                                <i class="fas fa-tags me-1"></i> GIS Categories
                            </div>
                        </div>
                        <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 60px; height: 60px; background-color: rgba(21, 101, 192, 0.1);">
                            <i class="fas fa-tags fa-lg" style="color:#1565c0;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Classifications -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #ff9800;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-2">Total Classifications</div>
                            <div class="h2 fw-bold mb-0" style="color:#ff9800;">{{ $totalClassifications ?? 0 }}</div>
                            <div class="small text-muted mt-2">
                                <i class="fas fa-layer-group me-1"></i> GIS Types
                            </div>
                        </div>
                        <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 60px; height: 60px; background-color: rgba(255, 152, 0, 0.1);">
                            <i class="fas fa-layer-group fa-lg" style="color:#ff9800;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Recent Activities -->
    <div class="row g-4 mb-5">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-semibold mb-0" style="color:#2e7d32;">
                        <i class="fas fa-history me-2"></i>Recent Shapefile Activities
                    </h5>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush">
                        @forelse($recentActivities as $activity)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-semibold">{{ $activity->category_name }}</span> Admin
                                    {{ $activity->action }} 
                                    <span class="badge px-2 py-1"
                                        style="background-color: {{ $activity->category_color }}; color:white;">
                                        {{ ucfirst($activity->classification_name) }}
                                    </span> Classification
                                </div>
                                <div class="small text-muted">{{ $activity->created_at->diffForHumans() }}</div>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted">
                                No recent activities
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Shapefiles Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="fw-semibold mb-0" style="color:#b71c1c;">
                <i class="fas fa-table me-2"></i>Shapefiles Management
            </h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: rgba(183, 28, 28, 0.05);">
                        <tr>
                            <th>Category</th>
                            <th>Classification</th>
                            <th>Metadata</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
        @forelse($features as $feature)
            <tr class="{{ $feature->trashed() ? 'table-secondary' : 'hover-row' }}">
                <!-- Category Badge -->
                <td>
                    <span class="badge category-badge text-dark {{ Str::slug($feature->category_name) }}-badge fs-6 px-3 py-2">
                        {{ ucfirst(str_replace('_', ' ', $feature->category_name)) }}
                    </span>
                </td>

                <!-- Classification with Color -->
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="color-preview" style="background-color: {{ $feature->classification_color }};"></span>
                        <span class="fw-medium">{{ $feature->classification_name }}</span>
                    </div>
                </td>

                <!-- Feature Items Count -->
                <td>
                    <span class="fw-semibold">{{ $feature->properties->count() ?? 0 }}</span>
                    <span class="text-muted small">items</span>
                </td>

                <!-- Status Badge -->
                <td>
                    @if ($feature->trashed())
                        <span class="badge bg-warning bg-opacity-10 text-warning">Archived</span>
                    @else
                        <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                    @endif
                </td>

                <!-- Updated At -->
                <td class="small text-muted">{{ $feature->updated_at->diffForHumans() }}</td>

                <!-- Action Buttons -->
                <td class="text-end pe-4">
                    @if (!$feature->trashed())
                        <a href="{{ route('shapefiles.edit', $feature->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteModal"
                                data-id="{{ $feature->id }}">
                            Delete
                        </button>
                    @else
                        <button type="button" class="btn btn-sm btn-outline-success"
                                data-bs-toggle="modal"
                                data-bs-target="#restoreModal"
                                data-id="{{ $feature->id }}">
                            Restore
                        </button>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-5">
                    <h6 class="text-muted">No features found</h6>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Pagination -->
<div class="pagination-wrapper d-flex justify-content-between align-items-center mt-4">

    <!-- Records Info -->
    <div class="pagination-info">
        Showing 
        <strong>{{ $features->firstItem() }}</strong>
        to 
        <strong>{{ $features->lastItem() }}</strong>
        of 
        <strong>{{ $features->total() }}</strong>
        records
    </div>

    <!-- Pagination Links -->
    <div class="pagination-links">
        {{ $features->links() }}
    </div>
</div>
</div>

@include('layouts.modal')

@push('styles')
<style>
.color-preview {
    width: 18px;
    height: 18px;
    border-radius: 4px;
    border: 1px solid rgba(0, 0, 0, 0.15);
    display: inline-block;
}
.category-badge { font-size: 0.85rem; font-weight: 500; letter-spacing: 0.5px; }
.avatar-placeholder { font-size: 1.1rem; }
.hover-row:hover { background-color: rgba(183,28,28,0.05); transition: all 0.2s; }

/* PAGINATION WRAPPER */
.pagination-wrapper{
    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:10px;
    padding:12px 18px;
}


/* RECORDS INFO */
.pagination-info{
    font-size:13px;
    color:#6b7280;
}

.pagination-info strong{
    color:#111827;
}


/* PAGINATION LINKS */
.pagination-links nav{
    margin:0;
}

.pagination-links .pagination{
    margin:0;
}


/* PAGE BUTTONS */
.pagination .page-link{
    color:#dc2626;
    border:1px solid #e5e7eb;
    border-radius:6px;
    margin:0 3px;
    padding:6px 12px;
    font-size:13px;

    transition:all .2s ease;
}


/* HOVER */
.pagination .page-link:hover{
    background:#fee2e2;
    color:#b91c1c;
    border-color:#fecaca;
}


/* ACTIVE PAGE */
.pagination .active .page-link{
    background:#dc2626;
    border-color:#dc2626;
    color:#ffffff;
}


/* DISABLED */
.pagination .disabled .page-link{
    color:#9ca3af;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deleteModal');
    const restoreModal = document.getElementById('restoreModal');

    deleteModal?.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const form = document.getElementById('deleteForm');
        form.action = "{{ url('admin/features') }}/" + id;
    });

    restoreModal?.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const form = document.getElementById('restoreForm');
        form.action = "{{ url('admin/features') }}/" + id + "/restore";
    });
});
</script>
@endpush
@endsection