@extends('layouts.app')

@section('page_title', $page['pageTitle'])

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-5">
            <div>
                <h1 class="h3 fw-bold mb-2" style="color:#b71c1c;">{{ $page['pageName'] }}
                    ({{ ucfirst(str_replace('_', ' ', $adminCategory)) }})</h1>
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

        <!-- Stats Cards -->
        <div class="row g-4 mb-5">

            <!-- Total Users -->
            <div class="col-xl-4 col-md-6">
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
            <div class="col-xl-4 col-md-6">
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

                        <!-- CATEGORY ONLY -->
                        <div class="mt-4 pt-3 border-top">
                            <div class="small">
                                <span class="fw-semibold" style="color:#b71c1c;">
                                    <i class="fas fa-tag me-1"></i>
                                    {{ $totalShapefiles ?? 0 }}
                                    {{ ucfirst(str_replace('_', ' ', $adminCategory)) }}
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- System Activities -->
            <div class="col-xl-4 col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #1565c0;">
                    <div class="card-body p-4">
                        <div class="h2 fw-bold mb-0" style="color:#1565c0;">Coming soon</div>
                        <div class="small text-muted mt-2">
                            <i class="fas fa-chart-bar me-1"></i> Real-time monitoring
                        </div>
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
                                <th>Created By</th>
                                <th>Metadata</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shapefiles as $shapefile)
                                <tr class="{{ $shapefile->trashed() ? 'table-secondary' : 'hover-row' }}">
                                    @php
                                        $categoryName = optional($shapefile->category)->name ?? 'No Category';
                                    @endphp
                                    <td>
                                        <span class="badge category-badge text-dark {{ Str::slug($categoryName) }}-badge">
                                            {{ ucfirst(str_replace('_', ' ', $categoryName)) }}
                                        </span>
                                    </td>

                                    <td>{{ $shapefile->user->name ?? 'N/A' }}</td>

                                    <td>
                                        <span class="fw-semibold">
                                            {{ $shapefile->metadata->count() }}
                                        </span>
                                        <span class="text-muted small">items</span>
                                    </td>

                                    <td>
                                        @if ($shapefile->trashed())
                                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                                Archived
                                            </span>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success">
                                                Active
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-end pe-4">

                                        @if (!$shapefile->trashed())
                                            <!-- Edit -->
                                            <a href="{{ route('shapefiles.edit', $shapefile->id) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </a>

                                            <!-- Delete -->
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                data-id="{{ $shapefile->id }}">
                                                Delete
                                            </button>
                                        @else
                                            <!-- Restore -->
                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                data-bs-toggle="modal" data-bs-target="#restoreModal"
                                                data-id="{{ $shapefile->id }}">
                                                Restore
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <h6 class="text-muted">No shapefiles found</h6>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($shapefiles->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $shapefiles->links() }}
                </div>
            @endif
        </div>
    </div>

    @include('layouts.modal')

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const deleteModal = document.getElementById('deleteModal');
                const restoreModal = document.getElementById('restoreModal');

                deleteModal?.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-id');
                    const form = document.getElementById('deleteForm');

                    // Use correct route
                    form.action = "{{ url('admin/shapefiles') }}/" + id;
                });

                restoreModal?.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-id');
                    const form = document.getElementById('restoreForm');

                    form.action = "{{ url('admin/shapefiles') }}/" + id + "/restore";
                });
            });
        </script>
    @endpush
@endsection
