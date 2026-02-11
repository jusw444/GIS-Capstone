@extends('layouts.app')

@section('page_title', $page['pageTitle'])

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-5">
            <div>
                <h1 class="h3 fw-bold mb-2" style="color:#b71c1c;">{{ $page['pageName'] }}</h1>
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
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="row g-4 mb-5">
            <div class="col-xl-4 col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #b71c1c;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="text-muted small mb-2">Total Users</div>
                                <div class="h2 fw-bold mb-0" style="color:#b71c1c;">{{ $totalUsers ?? '0' }}</div>
                                <div class="small text-muted mt-2">
                                    <i class="fas fa-users me-1"></i> Registered users
                                </div>
                            </div>
                            <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 60px; height: 60px; background-color: rgba(183, 28, 28, 0.1);">
                                <i class="fas fa-users fa-lg" style="color:#b71c1c;"></i>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-top">
                            <div class="small">
                                <span class="text-success fw-semibold">
                                    <i class="fas fa-chart-line me-1"></i> Active
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #2e7d32;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="text-muted small mb-2">Total Shapefiles</div>
                                <div class="h2 fw-bold mb-0" style="color:#2e7d32;">{{ $totalShapefiles ?? '0' }}</div>
                                <div class="small text-muted mt-2">
                                    <i class="fas fa-layer-group me-1"></i> GIS datasets
                                </div>
                            </div>
                            <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 60px; height: 60px; background-color: rgba(46, 125, 50, 0.1);">
                                <i class="fas fa-map-marked-alt fa-lg" style="color:#2e7d32;"></i>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-top">
                            <div class="small">
                                <span class="fw-semibold" style="color:#b71c1c;">
                                    <i class="fas fa-tag me-1"></i>
                                    {{ $categoryCounts['disaster'] ?? 0 }} Disaster
                                </span>
                                <span class="mx-2">•</span>
                                <span class="fw-semibold" style="color:#2e7d32;">
                                    {{ $categoryCounts['health'] ?? 0 }} Health
                                </span>
                                <span class="mx-2">•</span>
                                <span class="fw-semibold" style="color:#1565c0;">
                                    {{ $categoryCounts['land_use'] ?? 0 }} Land Use
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #1565c0;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="text-muted small mb-2">System Activities</div>
                                <div class="h2 fw-bold mb-0" style="color:#1565c0;">Coming soon</div>
                                <div class="small text-muted mt-2">
                                    <i class="fas fa-chart-bar me-1"></i> Real-time monitoring
                                </div>
                            </div>
                            <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 60px; height: 60px; background-color: rgba(21, 101, 192, 0.1);">
                                <i class="fas fa-chart-pie fa-lg" style="color:#1565c0;"></i>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-top">
                            <div class="small">
                                <span class="text-muted">
                                    <i class="fas fa-clock me-1"></i> Analytics dashboard in development
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Row -->
        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-4" style="color:#b71c1c;">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h5>
                        <div class="row g-3 justify-content-center">
                            <div class="col-md-3">
                                <a href="{{ route('admin.view') }}" class="text-decoration-none">
                                    <div class="p-3 rounded-3 text-center hover-lift"
                                        style="background-color: rgba(183, 28, 28, 0.05); border: 2px solid rgba(183, 28, 28, 0.1);">
                                        <i class="fas fa-globe fa-2x mb-3" style="color:#b71c1c;"></i>
                                        <div class="fw-semibold" style="color:#b71c1c;">View Map</div>
                                        <div class="small text-muted">Interactive GIS visualization</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.shapefile.upload') }}" class="text-decoration-none">
                                    <div class="p-3 rounded-3 text-center hover-lift"
                                        style="background-color: rgba(46, 125, 50, 0.05); border: 2px solid rgba(46, 125, 50, 0.1);">
                                        <i class="fas fa-upload fa-2x mb-3" style="color:#2e7d32;"></i>
                                        <div class="fw-semibold" style="color:#2e7d32;">Upload Data</div>
                                        <div class="small text-muted">Add new shapefiles</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="#" class="text-decoration-none">
                                    <div class="p-3 rounded-3 text-center hover-lift"
                                        style="background-color: rgba(108, 117, 125, 0.05); border: 2px solid rgba(108, 117, 125, 0.1);">
                                        <i class="fas fa-chart-line fa-2x mb-3" style="color:#6c757d;"></i>
                                        <div class="fw-semibold" style="color:#6c757d;">Reports</div>
                                        <div class="small text-muted">Generate analytics</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shapefiles Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-semibold mb-0" style="color:#b71c1c;">
                            <i class="fas fa-table me-2"></i>Shapefiles Management
                        </h5>
                        <p class="small text-muted mb-0 mt-1">Manage all GIS datasets in the system</p>
                    </div>
                    <div>
                        <span class="badge rounded-pill px-3 py-2"
                            style="background-color: rgba(183, 28, 28, 0.1); color: #b71c1c;">
                            <i class="fas fa-database me-1"></i>
                            {{ $totalShapefiles }} total records
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: rgba(183, 28, 28, 0.05);">
                            <tr>
                                <th style="color:#b71c1c; font-weight: 600;">Category</th>
                                <th style="color:#b71c1c; font-weight: 600;">Created By</th>
                                <th style="color:#b71c1c; font-weight: 600;">Metadata</th>
                                <th style="color:#b71c1c; font-weight: 600;">Status</th>
                                <th style="color:#b71c1c; font-weight: 600;" class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shapefiles as $shapefile)
                                <tr class="{{ $shapefile->trashed() ? 'table-secondary' : 'hover-row' }}">
                                    <td>
                                        <span class="badge category-badge {{ $shapefile->category }}-badge">
                                            {{ ucfirst($shapefile->category) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center me-2"
                                                style="width: 28px; height: 28px; background-color: rgba(183, 28, 28, 0.1); color: #b71c1c; font-size: 0.8rem;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            {{ $shapefile->user->name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-info-circle me-2" style="color:#6c757d;"></i>
                                            <span class="fw-semibold">{{ $shapefile->metadata->count() }}</span>
                                            <span class="text-muted ms-1 small">items</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($shapefile->trashed())
                                            <span
                                                class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                                <i class="fas fa-archive me-1"></i> Archived
                                            </span>
                                        @else
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                                <i class="fas fa-check-circle me-1"></i> Active
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group" role="group">
                                            @if ($shapefile->trashed())
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-success rounded-start-2"
                                                    onclick="openRestoreModal({{ $shapefile->id }})">
                                                    <i class="fas fa-undo me-1"></i> Restore
                                                </button>
                                            @else
                                                <a href="{{ route('shapefiles.edit', $shapefile->id) }}"
                                                    class="btn btn-sm btn-outline-primary rounded-start-2">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-end-2"
                                                    onclick="openDeleteModal({{ $shapefile->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="fas fa-inbox fa-3x mb-3" style="color:rgba(183, 28, 28, 0.2);"></i>
                                            <h6 class="text-muted mb-2">No shapefiles found</h6>
                                            <p class="small text-muted">Start by uploading your first GIS dataset</p>
                                            <a href="#" class="btn btn-sm"
                                                style="background-color:#b71c1c; color:white;">
                                                <i class="fas fa-plus me-1"></i> Upload Shapefile
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($shapefiles->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                        <div class="small text-muted mb-3 mb-md-0">
                            Showing {{ $shapefiles->firstItem() ?? 0 }} to {{ $shapefiles->lastItem() ?? 0 }} of
                            {{ $shapefiles->total() }} entries
                        </div>

                        <!-- Custom Pagination -->
                        <nav aria-label="Shapefiles pagination">
                            <ul class="pagination pagination-sm mb-0">
                                {{-- Previous Page Link --}}
                                @if ($shapefiles->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link">
                                            <i class="fas fa-chevron-left fa-xs"></i>
                                        </span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $shapefiles->previousPageUrl() }}"
                                            aria-label="Previous">
                                            <i class="fas fa-chevron-left fa-xs"></i>
                                        </a>
                                    </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($shapefiles->getUrlRange(1, $shapefiles->lastPage()) as $page => $url)
                                    @if ($page == $shapefiles->currentPage())
                                        <li class="page-item active" aria-current="page">
                                            <span class="page-link"
                                                style="background-color: #b71c1c; border-color: #b71c1c;">
                                                {{ $page }}
                                            </span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $url }}" style="color: #b71c1c;">
                                                {{ $page }}
                                            </a>
                                        </li>
                                    @endif
                                @endforeach

                                {{-- Next Page Link --}}
                                @if ($shapefiles->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $shapefiles->nextPageUrl() }}" aria-label="Next">
                                            <i class="fas fa-chevron-right fa-xs"></i>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link">
                                            <i class="fas fa-chevron-right fa-xs"></i>
                                        </span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @include('layouts.modal')

    <style>
        .avatar-placeholder {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .hover-lift {
            transition: all 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .hover-row:hover {
            background-color: rgba(183, 28, 28, 0.02);
        }

        .category-badge {
            padding: 0.35rem 0.75rem;
            font-weight: 500;
            border-radius: 20px;
        }

        .disaster-badge {
            background-color: rgba(183, 28, 28, 0.1);
            color: #b71c1c;
            border: 1px solid rgba(183, 28, 28, 0.2);
        }

        .health-badge {
            background-color: rgba(46, 125, 50, 0.1);
            color: #2e7d32;
            border: 1px solid rgba(46, 125, 50, 0.2);
        }

        .land_use-badge {
            background-color: rgba(21, 101, 192, 0.1);
            color: #1565c0;
            border: 1px solid rgba(21, 101, 192, 0.2);
        }

        .btn-outline-primary {
            color: #b71c1c;
            border-color: #b71c1c;
        }

        .btn-outline-primary:hover {
            background-color: #b71c1c;
            border-color: #b71c1c;
            color: white;
        }

        .btn-outline-danger:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        .btn-outline-success:hover {
            background-color: #198754;
            border-color: #198754;
            color: white;
        }

        .table th {
            border-bottom: 2px solid rgba(183, 28, 28, 0.1);
            padding: 1rem 0.75rem;
        }

        .table td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card {
            border-radius: 12px;
            overflow: hidden;
        }

        .badge {
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        /* Custom modal styles */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background-color: #b71c1c;
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 1.25rem 1.5rem;
        }

        .modal-title {
            font-weight: 600;
        }

        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* For Pagination */
        /* Pagination Styles */
        .pagination {
            margin-bottom: 0;
        }

        .page-link {
            color: #b71c1c;
            border: 1px solid rgba(183, 28, 28, 0.2);
            padding: 0.5rem 0.75rem;
            margin: 0 2px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .page-link:hover {
            background-color: rgba(183, 28, 28, 0.1);
            border-color: #b71c1c;
            color: #b71c1c;
            transform: translateY(-1px);
        }

        .page-item.active .page-link {
            background-color: #b71c1c;
            border-color: #b71c1c;
            color: white;
        }

        .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #f8f9fa;
            border-color: rgba(183, 28, 28, 0.1);
        }

        .page-link:focus {
            box-shadow: 0 0 0 0.25rem rgba(183, 28, 28, 0.25);
        }
    </style>

    @push('scripts')
        <script>
            function openDeleteModal(id) {
                const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
                const form = document.getElementById('deleteForm');
                form.action = `/shapefiles/${id}`;
                modal.show();
            }

            function openRestoreModal(id) {
                const modal = new bootstrap.Modal(document.getElementById('restoreModal'));
                const form = document.getElementById('restoreForm');
                form.action = `/shapefiles/${id}/restore`; // POST route
                modal.show();
            }

            // Add animation to table rows
            document.addEventListener('DOMContentLoaded', function() {
                const rows = document.querySelectorAll('tbody tr');
                rows.forEach((row, index) => {
                    row.style.animationDelay = `${index * 0.05}s`;
                    row.classList.add('fade-in');
                });
            });
        </script>
    @endpush
@endsection
