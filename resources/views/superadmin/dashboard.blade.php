@extends('layouts.app')

@section('page_title', $page['pageTitle'])

@section('content')
    <div class="container-fluid px-4 py-4" style="font-family: 'Nunito', sans-serif;">

        <!-- Header Section -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-5">
            <div>
                <h1 class="h2 fw-bold mb-2" style="color:#b71c1c;">
                    <i class="fas fa-crown me-2"></i>{{ $page['pageName'] }}
                </h1>
                <p class="text-muted mb-0">Complete system overview and management control panel</p>
            </div>
            <div class="mt-3 mt-md-0">
                <div class="d-flex align-items-center bg-white p-3 rounded-3 shadow-sm">
                    <div class="me-3">
                        <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 45px; height: 45px; background-color: rgba(183, 28, 28, 0.1); color: #b71c1c;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-muted">Super Administrator</div>
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

        <!-- Stats Overview -->
        <div class="row g-4 mb-5">
            <!-- Total Admins Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="text-muted small mb-2">Total Admins</div>
                                <div class="h2 fw-bold mb-0" style="color:#b71c1c;">{{ $totalAdmins ?? 0 }}</div>
                            </div>
                            <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 60px; height: 60px; background-color: rgba(183, 28, 28, 0.1);">
                                <i class="fas fa-user-tie fa-lg" style="color:#b71c1c;"></i>
                            </div>
                        </div>
                        <div class="border-top pt-3">
                            <div class="small text-muted">
                                <i class="fas fa-users me-1"></i>
                                System administrators with full access
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 py-3">
                        <a href="{{ route('superadmin.users') }}" class="btn btn-sm w-100"
                            style="background-color:#b71c1c; color:white;">
                            <i class="fas fa-eye me-1"></i> View All
                        </a>
                    </div>
                </div>
            </div>

            <!-- Total Users Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="text-muted small mb-2">Total Users</div>
                                <div class="h2 fw-bold mb-0" style="color:#2e7d32;">{{ $totalUsers ?? 0 }}</div>
                            </div>
                            <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 60px; height: 60px; background-color: rgba(46, 125, 50, 0.1);">
                                <i class="fas fa-users fa-lg" style="color:#2e7d32;"></i>
                            </div>
                        </div>
                        <div class="border-top pt-3">
                            <div class="small text-muted">
                                <i class="fas fa-user-check me-1"></i>
                                Registered platform users
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 py-3">
                        <a href="{{ route('superadmin.users') }}" class="btn btn-sm w-100 btn-outline-success">
                            <i class="fas fa-user-plus me-1"></i> Manage Users
                        </a>
                    </div>
                </div>
            </div>

            <!-- Created Shapefiles Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="text-muted small mb-2">Created Shapefiles</div>
                                <div class="h2 fw-bold mb-0" style="color:#1565c0;">{{ $totalShapefiles ?? 0 }}</div>
                            </div>
                            <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 60px; height: 60px; background-color: rgba(21, 101, 192, 0.1);">
                                <i class="fas fa-map-marked-alt fa-lg" style="color:#1565c0;"></i>
                            </div>
                        </div>
                        <div class="border-top pt-3">
                            <div class="small text-muted">
                                <i class="fas fa-plus-circle me-1"></i>
                                System-generated datasets
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 py-3">
                        <a href="{{ route('admin.view') }}" class="btn btn-sm w-100 btn-outline-primary">
                            <i class="fas fa-external-link-alt me-1"></i> View on Map
                        </a>
                    </div>
                </div>
            </div>

            <!-- Uploaded Shapefiles Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="text-muted small mb-2">Uploaded Shapefiles</div>
                                <div class="h2 fw-bold mb-0" style="color:#6f42c1;">{{ $totalUploadedShapefiles ?? 0 }}
                                </div>
                            </div>
                            <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 60px; height: 60px; background-color: rgba(111, 66, 193, 0.1);">
                                <i class="fas fa-cloud-upload-alt fa-lg" style="color:#6f42c1;"></i>
                            </div>
                        </div>
                        <div class="border-top pt-3">
                            <div class="small text-muted">
                                <i class="fas fa-file-import me-1"></i>
                                User-uploaded GIS files
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 py-3">
                        <a href="#" class="btn btn-sm w-100 btn-outline-purple">
                            <i class="fas fa-upload me-1"></i> Upload New
                        </a>
                    </div>
                </div>
            </div>
            <!-- PUBLIC DATA -->
        <div class="col-xl-2 col-md-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body text-center">
                    <i class="fas fa-globe fa-2x mb-3" style="color:#198754;"></i>
                    <h5>{{ $totalPublicDatasets }}</h5>
                    <small class="text-muted">Public Data</small>
                </div>
            </div>
        </div>

        <!-- PRIVATE DATA -->
        <div class="col-xl-2 col-md-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body text-center">
                    <i class="fas fa-lock fa-2x mb-3" style="color:#dc3545;"></i>
                    <h5>{{ $totalPrivateDatasets }}</h5>
                    <small class="text-muted">Private Data</small>
                </div>
            </div>
        </div>
        </div>

        <!-- Main Content Row -->
        <div">
            <!-- Recent Activity Panel -->
            <div>
                <div class="card border-0 shadow-sm h-100 activity-container">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-semibold mb-0" style="color:#b71c1c;">
                                    <i class="fas fa-history me-2"></i>Recent Activity
                                </h5>
                                <p class="small text-muted mb-0 mt-1">Latest system activities and events</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush activity-item">
                            @forelse($recentActivities ?? [] as $activity)
                                <div class="list-group-item border-0 py-3 px-4 hover-row">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px; background-color: rgba(183, 28, 28, 0.1);">
                                                    <i class="fas" style="color:#b71c1c;"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">
                                                    {{ $activity->description ?? $activity->location }}</div>
                                                <div class="small text-muted">
                                                    <i class="fas fa-user me-1"></i>
                                                    {{ $activity->user_name }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="small text-muted mb-1">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $activity->created_at->diffForHumans() }}
                                            </div>
                                            <span class="badge rounded-pill px-3"
                                                style="background-color: {{ $activity->action_color }}; color: white;">
                                                {{ $activity->action }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="fas fa-calendar-times fa-3x mb-3" style="color:rgba(183, 28, 28, 0.2);"></i>
                                    <h6 class="text-muted mb-2">No recent activity</h6>
                                    <p class="small text-muted">System activity will appear here</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @if (count($recentActivity ?? []) > 0)
                        <div class="card-footer bg-white border-0 py-3 text-center">
                            <a href="#" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-list me-1"></i> View All Activities
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- <!-- Map Preview Panel -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-semibold mb-0" style="color:#b71c1c;">
                                    <i class="fas fa-map me-2"></i>GIS Map Overview
                                </h5>
                                <p class="small text-muted mb-0 mt-1">Real-time shapefile visualization</p>
                            </div>
                            <div>
                                <span class="badge rounded-pill px-3 py-2"
                                    style="background-color: rgba(183, 28, 28, 0.1); color: #b71c1c;">
                                    <i class="fas fa-layer-group me-1"></i>
                                    {{ count($geojson ?? []) }} datasets
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0 position-relative">
                        <div id="mapPreview" style="width:100%; height:400px; border-radius: 0 0 8px 8px;"></div>
                        <div class="position-absolute bottom-0 end-0 m-3">
                            <div class="btn-group shadow-sm">
                                <button class="btn btn-sm btn-light" onclick="mapPreview.zoomIn()">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button class="btn btn-sm btn-light" onclick="mapPreview.zoomOut()">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button class="btn btn-sm btn-light" onclick="fitMapBounds()">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                        </div>
                        <div class="position-absolute top-0 start-0 m-3">
                            <div class="bg-white rounded-pill px-3 py-2 shadow-sm">
                                <div class="d-flex align-items-center">
                                    <span class="badge me-2"
                                        style="width: 12px; height: 12px; background-color: #b71c1c;"></span>
                                    <span class="small me-3">Disaster</span>
                                    <span class="badge me-2"
                                        style="width: 12px; height: 12px; background-color: #2e7d32;"></span>
                                    <span class="small me-3">Health</span>
                                    <span class="badge me-2"
                                        style="width: 12px; height: 12px; background-color: #1565c0;"></span>
                                    <span class="small">Land Use</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 py-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <a href="{{ route('admin.view') }}" class="btn btn-sm w-100"
                                    style="background-color:#b71c1c; color:white;">
                                    <i class="fas fa-external-link-alt me-1"></i> Open Full Map
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-sm w-100 btn-outline-secondary" onclick="refreshMap()">
                                    <i class="fas fa-sync-alt me-1"></i> Refresh View
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        <!-- Quick Management Tools -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-semibold mb-0" style="color:#b71c1c;">
                            <i class="fas fa-tools me-2"></i>Quick Management Tools
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <a href="#" class="text-decoration-none">
                                    <div class="p-3 rounded-3 text-center hover-lift"
                                        style="background-color: rgba(183, 28, 28, 0.05);">
                                        <i class="fas fa-user-cog fa-2x mb-3" style="color:#b71c1c;"></i>
                                        <div class="fw-semibold" style="color:#b71c1c;">Admin Roles</div>
                                        <div class="small text-muted">Manage permissions</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="#" class="text-decoration-none">
                                    <div class="p-3 rounded-3 text-center hover-lift"
                                        style="background-color: rgba(46, 125, 50, 0.05);">
                                        <i class="fas fa-database fa-2x mb-3" style="color:#2e7d32;"></i>
                                        <div class="fw-semibold" style="color:#2e7d32;">Data Backup</div>
                                        <div class="small text-muted">System backup tools</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="#" class="text-decoration-none">
                                    <div class="p-3 rounded-3 text-center hover-lift"
                                        style="background-color: rgba(21, 101, 192, 0.05);">
                                        <i class="fas fa-chart-bar fa-2x mb-3" style="color:#1565c0;"></i>
                                        <div class="fw-semibold" style="color:#1565c0;">Analytics</div>
                                        <div class="small text-muted">Usage statistics</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="#" class="text-decoration-none">
                                    <div class="p-3 rounded-3 text-center hover-lift"
                                        style="background-color: rgba(111, 66, 193, 0.05);">
                                        <i class="fas fa-cogs fa-2x mb-3" style="color:#6f42c1;"></i>
                                        <div class="fw-semibold" style="color:#6f42c1;">System Config</div>
                                        <div class="small text-muted">Platform settings</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
        <style>
            :root {
                --primary-color: #b71c1c;
                --primary-light: rgba(183, 28, 28, 0.1);
                --success-color: #2e7d32;
                --info-color: #1565c0;
                --purple-color: #6f42c1;
            }

            body {
                background-color: #f8f9fa;
            }

            .card {
                border-radius: 12px;
                overflow: hidden;
                transition: transform 0.2s ease;
            }

            .hover-lift:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
            }

            .hover-row:hover {
                background-color: rgba(183, 28, 28, 0.02) !important;
            }

            .avatar-placeholder {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .btn-outline-purple {
                color: #6f42c1;
                border-color: #6f42c1;
            }

            .btn-outline-purple:hover {
                background-color: #6f42c1;
                border-color: #6f42c1;
                color: white;
            }

            .list-group-item {
                border-left: none;
                border-right: none;
            }

            .list-group-item:first-child {
                border-top: none;
            }

            .list-group-item:last-child {
                border-bottom: none;
            }

            .badge {
                font-weight: 500;
                letter-spacing: 0.3px;
            }

            /* Leaflet map customizations */
            .leaflet-container {
                font-family: 'Nunito', sans-serif;
                border-radius: 0 0 8px 8px;
            }

            .leaflet-popup-content {
                padding: 1rem;
                border-radius: 8px;
            }

            .leaflet-popup-content-wrapper {
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            }

            .activity-container {
                max-height: 550px;
                overflow-y: auto;
                padding-right: 8px;
                scroll-behavior: smooth;
            }

            .activity-item {
                padding: 8px;
                border-bottom: 1px solid #eee;
            }

            .activity-container::-webkit-scrollbar {
                width: 6px;
            }

            .activity-container::-webkit-scrollbar-thumb {
                background: #ccc;
                border-radius: 10px;
            }

            .activity-container .card-header {
                position: sticky;
                top: 0;
                z-index: 10;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // // Global map variable
            // let mapPreview;
            // let allMapLayers;

            // document.addEventListener('DOMContentLoaded', function () {
            //     initializeMap();
            // });

            // function initializeMap() {
            //     let shapefiles = JSON.parse(@json($geojson ?? []));

            //     // Initialize map
            //     mapPreview = L.map('mapPreview', {
            //         center: [14.28, 121.40],
            //         zoom: 10,
            //         minZoom: 8,
            //         maxZoom: 18,
            //         zoomControl: false
            //     });

            //     // Add tile layer
            //     L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            //         maxZoom: 20,
            //         attribution: '© OpenStreetMap contributors',
            //         className: 'map-tiles'
            //     }).addTo(mapPreview);

            //     // Initialize feature group
            //     allMapLayers = L.featureGroup();

            //     // Process shapefiles
            //     shapefiles.forEach(item => {
            //         if (!item.geometry || item.geometry.type !== 'FeatureCollection') {
            //             console.warn('Invalid geometry for ID:', item.id);
            //             return;
            //         }

            //         // Define style based on category
            //         const style = {
            //             color: item.category === 'disaster' ? '#b71c1c' :
            //                    item.category === 'health' ? '#2e7d32' : '#1565c0',
            //             weight: 3,
            //             opacity: 0.7,
            //             fillOpacity: 0.2,
            //             fillColor: item.category === 'disaster' ? '#b71c1c' :
            //                       item.category === 'health' ? '#2e7d32' : '#1565c0'
            //         };

            //         // Create layer
            //         let layer = L.geoJSON(item.geometry, {
            //             style: style,
            //             onEachFeature: function (feature, layer) {
            //                 // Build popup content
            //                 let metaHtml = '';
            //                 if (item.metadata && item.metadata.length > 0) {
            //                     metaHtml = '<div style="max-height: 150px; overflow-y: auto;">';
            //                     item.metadata.slice(0, 5).forEach(m => {
            //                         metaHtml += `<div class="mb-1"><strong>${m.meta_key}:</strong> ${m.meta_value}</div>`;
            //                     });
            //                     if (item.metadata.length > 5) {
            //                         metaHtml += `<div class="text-center mt-2"><small>... and ${item.metadata.length - 5} more</small></div>`;
            //                     }
            //                     metaHtml += '</div>';
            //                 } else {
            //                     metaHtml = '<em class="text-muted">No metadata available</em>';
            //                 }

            //                 layer.bindPopup(`
    //                     <div style="max-width: 300px;">
    //                         <div class="d-flex justify-content-between align-items-start mb-2">
    //                             <div>
    //                                 <span class="badge rounded-pill px-3" 
    //                                       style="background-color: ${style.color}; color: white;">
    //                                     ${item.category}
    //                                 </span>
    //                                 <h6 class="mt-2 mb-1">Shapefile #${item.id}</h6>
    //                             </div>
    //                         </div>
    //                         <div class="border-top pt-2">
    //                             ${metaHtml}
    //                         </div>
    //                     </div>
    //                 `);
            //             }
            //         });

            //         layer.addTo(allMapLayers);
            //     });

            //     // Add layers to map
            //     allMapLayers.addTo(mapPreview);

            //     // Fit bounds if there are layers
            //     if (allMapLayers.getLayers().length > 0) {
            //         mapPreview.fitBounds(allMapLayers.getBounds(), { 
            //             padding: [30, 30],
            //             maxZoom: 12
            //         });
            //     }

            //     // Add zoom control
            //     L.control.zoom({
            //         position: 'topright'
            //     }).addTo(mapPreview);
            // }

            // // Helper function to fit bounds
            // function fitMapBounds() {
            //     if (allMapLayers.getLayers().length > 0) {
            //         mapPreview.fitBounds(allMapLayers.getBounds(), { 
            //             padding: [30, 30],
            //             maxZoom: 12
            //         });
            //     } else {
            //         mapPreview.setView([14.28, 121.40], 10);
            //     }
            // }

            // // Refresh map function
            // function refreshMap() {
            //     // In a real app, this would fetch new data
            //     // For now, just re-center the map
            //     fitMapBounds();

            //     // Show refresh feedback
            //     const refreshBtn = document.querySelector('[onclick="refreshMap()"]');
            //     const originalHtml = refreshBtn.innerHTML;
            //     refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...';
            //     refreshBtn.disabled = true;

            //     setTimeout(() => {
            //         refreshBtn.innerHTML = originalHtml;
            //         refreshBtn.disabled = false;
            //     }, 1000);
            // }

            // Helper functions for activity display (would be in PHP in real app)
            // function getActivityIcon(type) {
            //     const icons = {
            //         'user': 'user',
            //         'system': 'cog',
            //         'security': 'shield-alt',
            //         'data': 'database',
            //         'default': 'bell'
            //     };
            //     return icons[type] || 'bell';
            // }

            // function getActivityBadgeColor(type) {
            //     const colors = {
            //         'user': '#b71c1c',
            //         'system': '#1565c0',
            //         'security': '#2e7d32',
            //         'data': '#6f42c1',
            //         'default': '#6c757d'
            //     };
            //     return colors[type] || '#6c757d';
            // }
        </script>
    @endpush
@endsection
