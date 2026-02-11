@extends('layouts.app')

@section('page_title', $page['pageTitle'])

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-5">
            <div>
                <h1 class="h3 fw-bold mb-2" style="color:#b71c1c;">{{ $page['pageName'] }}</h1>
                <p class="text-muted mb-0">Interactive map visualization with category filtering</p>
            </div>
            <div class="mt-3 mt-md-0">
                <div class="badge"
                    style="background-color: rgba(183, 28, 28, 0.1); color: #b71c1c; padding: 0.75rem 1.25rem;">
                    <i class="fas fa-layer-group me-2"></i>
                    Total Shapefiles: {{ count($geojson) }}
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Controls & Analysis -->
            <div class="col-lg-4">
                <!-- Filter Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pb-0 pt-3">
                        <h5 class="fw-semibold mb-3" style="color:#b71c1c;">
                            <i class="fas fa-filter me-2" style="color:#b71c1c;"></i>Filter by Category
                        </h5>
                    </div>
                    <div class="card-body pt-2">
                        <form method="GET" action="{{ route('admin.view') }}" id="categoryForm">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-tag" style="color:#b71c1c;"></i>
                                </span>
                                <select name="category" class="form-select border-start-0 ps-2" id="categorySelect"
                                    style="border-color: #dee2e6;">
                                    <option value="">All Categories</option>
                                    <option value="disaster" {{ $category == 'disaster' ? 'selected' : '' }}>Disaster Risk
                                    </option>
                                    <option value="health" {{ $category == 'health' ? 'selected' : '' }}>Public Health
                                    </option>
                                    <option value="land_use" {{ $category == 'land_use' ? 'selected' : '' }}>Land Use
                                    </option>
                                </select>
                                <button type="submit" class="btn d-none d-md-block"
                                    style="background-color: #b71c1c; color: white; border-color: #b71c1c;">
                                    <i class="fas fa-sync-alt me-1"></i> Apply
                                </button>
                            </div>
                            <button type="submit" class="btn w-100 mt-3 d-md-none"
                                style="background-color: #b71c1c; color: white; border-color: #b71c1c;">
                                <i class="fas fa-sync-alt me-1"></i> Apply Filter
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Analysis Panel -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h5 class="fw-semibold mb-0" style="color:#b71c1c;">
                            <i class="fas fa-chart-pie me-2" style="color:#b71c1c;"></i>Analysis Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-4">
                                <div class="text-center p-3 rounded-3" style="background-color: rgba(183, 28, 28, 0.1);">
                                    <div class="h4 fw-bold mb-1" style="color: #b71c1c;">
                                        {{ $geojson->where('category', 'disaster')->count() }}
                                    </div>
                                    <div class="small" style="color:#b71c1c;">Disaster Risk</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-3 rounded-3" style="background-color: rgba(46, 125, 50, 0.1);">
                                    <div class="h4 fw-bold mb-1" style="color: #2e7d32;">
                                        {{ $geojson->where('category', 'health')->count() }}
                                    </div>
                                    <div class="small text-muted">Public Health</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-3 rounded-3" style="background-color: rgba(21, 101, 192, 0.1);">
                                    <div class="h4 fw-bold mb-1" style="color: #1565c0;">
                                        {{ $geojson->where('category', 'land_use')->count() }}
                                    </div>
                                    <div class="small text-muted">Land Use</div>
                                </div>
                            </div>
                        </div>

                        <div class="border-top pt-3">
                            <h6 class="fw-semibold mb-3" style="color:#b71c1c;">Category Distribution</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small">Disaster Risk</span>
                                    <span
                                        class="small fw-semibold">{{ number_format(($geojson->where('category', 'disaster')->count() / max(count($geojson), 1)) * 100, 1) }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ ($geojson->where('category', 'disaster')->count() / max(count($geojson), 1)) * 100 }}%; background-color: #b71c1c;">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small">Public Health</span>
                                    <span
                                        class="small fw-semibold">{{ number_format(($geojson->where('category', 'health')->count() / max(count($geojson), 1)) * 100, 1) }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ ($geojson->where('category', 'health')->count() / max(count($geojson), 1)) * 100 }}%; background-color: #2e7d32;">
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small">Land Use</span>
                                    <span
                                        class="small fw-semibold">{{ number_format(($geojson->where('category', 'land_use')->count() / max(count($geojson), 1)) * 100, 1) }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ ($geojson->where('category', 'land_use')->count() / max(count($geojson), 1)) * 100 }}%; background-color: #1565c0;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-top pt-3 mt-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle me-2" style="color:#b71c1c;"></i>
                                <small class="text-muted">Click on any shape on the map to view details</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Map -->
            <div class="col-lg-8">
                <!-- Map Container -->
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-semibold mb-0" style="color:#b71c1c;">
                            <i class="fas fa-map-marked-alt me-2" style="color:#b71c1c;"></i>Interactive Map
                        </h5>
                        <div class="d-flex align-items-center">
                            <span class="me-3 small text-muted">
                                <span class="badge me-1"
                                    style="width: 12px; height: 12px; background-color: #b71c1c;"></span> Disaster
                                <span class="badge mx-1"
                                    style="width: 12px; height: 12px; background-color: #2e7d32;"></span> Health
                                <span class="badge ms-1"
                                    style="width: 12px; height: 12px; background-color: #1565c0;"></span> Land Use
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="map" style="height: 650px; border-radius: 0 0 8px 8px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Metadata Modal -->
    <div class="modal fade" id="metadataModal" tabindex="-1" aria-labelledby="metadataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header text-white" style="background-color: #b71c1c;">
                    <h5 class="modal-title" id="metadataModalLabel"> <i class="fas fa-database me-2"></i>Shapefile
                        Metadata </h5> <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="sticky-top bg-light p-4 border-bottom">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2"> <span class="badge fs-6" id="modalCategory"
                                        style="background-color: rgba(183, 28, 28, 0.1); color: #b71c1c;"></span> </div>
                                <h6 class="fw-semibold mb-1">Metadata Items</h6>
                                <p class="text-muted mb-0" id="metadataCount">0 items</p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="text-muted small"> <i class="fas fa-calendar-alt me-1"
                                        style="color:#b71c1c;"></i> <span id="modalTimestamp">Loaded just now</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-4" id="metadataModalBody"> <!-- Metadata content will be inserted here --> </div>
                </div>
                <div class="modal-footer border-top-0 bg-light"> <button type="button" class="btn btn-outline-secondary"
                        data-bs-dismiss="modal"> <i class="fas fa-times me-1"></i> Close </button> </div>
            </div>
        </div>
    </div>

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Leaflet Fullscreen Plugin CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.fullscreen@1.6.0/Control.FullScreen.css" />

    <!-- Leaflet Fullscreen Plugin JS (AFTER Leaflet) -->
    <script src="https://unpkg.com/leaflet.fullscreen@1.6.0/Control.FullScreen.js"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
        <style>
            :root {
                --primary-color: #b71c1c;
                --primary-light: rgba(183, 28, 28, 0.1);
                --primary-dark: #8c1c1c;
                --disaster-color: #b71c1c;
                --health-color: #2e7d32;
                --landuse-color: #1565c0;
                --success-color: #2e7d32;
                --info-color: #1565c0;
            }

            body {
                background-color: #f8f9fa;
            }

            .card {
                border-radius: 12px;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
                border: 1px solid rgba(0, 0, 0, 0.05);
            }

            .card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(183, 28, 28, 0.1) !important;
            }

            .form-select,
            .form-control {
                border-radius: 8px;
                border: 1px solid #dee2e6;
                padding: 0.75rem 1rem;
                font-size: 0.95rem;
            }

            .btn-primary {
                background-color: #b71c1c;
                border-color: #b71c1c;
                border-radius: 8px;
                padding: 0.5rem 1.5rem;
                font-weight: 500;
                transition: all 0.2s ease;
            }

            .btn-primary:hover {
                background-color: #8c1c1c;
                border-color: #8c1c1c;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(183, 28, 28, 0.2);
            }

            .progress {
                border-radius: 10px;
                overflow: hidden;
                background-color: #e9ecef;
            }

            .modal-content {
                border-radius: 12px;
                overflow: hidden;
                border: none;
            }

            .metadata-item {
                background: #f8f9fa;
                border-radius: 8px;
                padding: 1rem;
                margin-bottom: 0.75rem;
                border-left: 4px solid #b71c1c;
                transition: all 0.2s ease;
            }

            .metadata-item:hover {
                background: #e9ecef;
                transform: translateX(4px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            }

            .leaflet-popup-content {
                border-radius: 8px;
                padding: 1rem;
            }

            .leaflet-popup-content-wrapper {
                border-radius: 12px;
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
                border-top: 3px solid #b71c1c;
            }

            #map {
                box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.1);
            }

            .badge {
                border-radius: 20px;
                padding: 0.5em 1em;
                font-weight: 500;
                font-size: 0.85rem;
            }

            .sticky-top {
                backdrop-filter: blur(10px);
                background-color: rgba(248, 249, 250, 0.95);
            }

            /* Custom animations */
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .card {
                animation: fadeIn 0.3s ease-out;
            }

            /* Hover effects for interactive elements */
            .btn-outline-primary:hover {
                background-color: #b71c1c;
                border-color: #b71c1c;
            }

            /* Category-specific colors */
            .disaster-badge {
                background-color: rgba(183, 28, 28, 0.1);
                color: #b71c1c;
            }

            .health-badge {
                background-color: rgba(46, 125, 50, 0.1);
                color: #2e7d32;
            }

            .landuse-badge {
                background-color: rgba(21, 101, 192, 0.1);
                color: #1565c0;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // 🔑 GLOBAL DATA (IMPORTANT FIX)
            window.shapefiles = @json($geojson);

            document.addEventListener('DOMContentLoaded', () => {

                /* =====================================================
                 * BASE MAP LAYERS
                 * ===================================================== */

                const map = L.map('map', {
                    center: [14.28, 121.4],
                    zoom: 10,
                    zoomControl: true,
                    scrollWheelZoom: true,
                    dragging: true,
                    tap: true,
                    zoomSnap: 0.5
                });

                const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 20,
                    attribution: '&copy; OpenStreetMap contributors'
                });

                const hybrid = L.tileLayer(
                    'https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                        attribution: '&copy; Google Hybrid'
                    }
                );

                const satellite = L.tileLayer(
                    'https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                    }
                );

                // Carto Light
                const cartoLight = L.tileLayer(
                    'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                        maxZoom: 20,
                        attribution: '&copy; CartoDB'
                    }
                );

                // Carto Dark
                const cartoDark = L.tileLayer(
                    'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                        maxZoom: 20,
                        attribution: '&copy; CartoDB'
                    }
                );

                // ✅ DEFAULT BASE LAYER
                osm.addTo(map);

                /* =====================================================
                 * MAP INIT
                 * ===================================================== */

                L.control.scale({
                    imperial: false,
                    position: 'bottomleft'
                }).addTo(map);

                /* =====================================================
                 * OVERLAY GROUPS (PER CATEGORY)
                 * ===================================================== */

                const disasterLayer = L.featureGroup();
                const healthLayer = L.featureGroup();
                const landUseLayer = L.featureGroup();

                /* =====================================================
                 * CATEGORY STYLES
                 * ===================================================== */

                const categoryStyles = {
                    disaster: {
                        color: '#b71c1c',
                        fillColor: '#b71c1c',
                        weight: 3,
                        opacity: 0.8,
                        fillOpacity: 0.2
                    },
                    health: {
                        color: '#2e7d32',
                        fillColor: '#2e7d32',
                        weight: 3,
                        opacity: 0.8,
                        fillOpacity: 0.2
                    },
                    land_use: {
                        color: '#1565c0',
                        fillColor: '#1565c0',
                        weight: 3,
                        opacity: 0.8,
                        fillOpacity: 0.2
                    }
                };

                /* =====================================================
                 * LOAD SHAPEFILES
                 * ===================================================== */

                shapefiles.forEach(item => {

                    if (!item.geometry || item.geometry.type !== 'FeatureCollection') return;

                    const style = categoryStyles[item.category] || categoryStyles.land_use;

                    const geoLayer = L.geoJSON(item.geometry, {
                        style,
                        onEachFeature: (feature, layer) => {

                            const MAX_META = 5;
                            let metaHtml = '';
                            let extraCount = 0;

                            if (item.metadata?.length) {
                                item.metadata.slice(0, MAX_META).forEach(m => {
                                    metaHtml += `
                            <div class="mb-2">
                                <span class="fw-semibold">${m.meta_key}:</span>
                                <span class="ms-2">${m.meta_value || '<em class="text-muted">Not specified</em>'}</span>
                            </div>
                        `;
                                });
                                extraCount = item.metadata.length - MAX_META;
                            }

                            const popupContent = `
                        <div style="max-width: 320px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge mb-2 ${getCategoryBadgeClass(item.category)}"> ${getCategoryName(item.category)} </span>
                                </div>
                            </div>
                            <div class="mb-3" style="max-height: 180px; overflow-y: auto; padding-right: 8px;">
                                ${metaHtml || '<div class="text-center text-muted py-3"><i class="fas fa-info-circle me-1"></i>No metadata available</div>'}
                            </div>
                            ${extraCount > 0 ? `<div class="text-center pt-2 border-top">
                                        <button type="button" class="btn btn-sm view-meta" data-id="${item.id}" style="background-color: #b71c1c; color: white; border-radius: 20px; padding: 0.25rem 1rem; border: none;">
                                            <i class="fas fa-ellipsis-h me-1"></i> View all metadata (${item.metadata.length})
                                        </button>
                                    </div>` : ''}
                            <div class="mt-3 pt-2 border-top small text-muted text-center">
                                <i class="fas fa-mouse-pointer me-1"></i> Click for details
                            </div>
                        </div>`

                            layer.bindPopup(popupContent, {
                                maxWidth: 350,
                                closeOnClick: true
                            });

                            layer.on('mouseover', () => {
                                layer.setStyle({
                                    weight: 4,
                                    fillOpacity: 0.3
                                });
                            });

                            layer.on('mouseout', () => {
                                layer.setStyle(style);
                            });
                        }
                    });

                    // 🔑 ADD TO CORRECT OVERLAY
                    if (item.category === 'disaster') {
                        geoLayer.addTo(disasterLayer);
                    } else if (item.category === 'health') {
                        geoLayer.addTo(healthLayer);
                    } else {
                        geoLayer.addTo(landUseLayer);
                    }
                });

                /* =====================================================
                 * ADD DEFAULT VISIBLE LAYERS
                 * ===================================================== */

                disasterLayer.addTo(map);
                healthLayer.addTo(map);
                landUseLayer.addTo(map);

                /* =====================================================
                 * FIT MAP TO DATA
                 * ===================================================== */

                const allBounds = L.featureGroup([
                    disasterLayer,
                    healthLayer,
                    landUseLayer
                ]);

                if (allBounds.getLayers().length) {
                    map.fitBounds(allBounds.getBounds(), {
                        padding: [50, 50],
                        maxZoom: 15
                    });
                }

                /* =====================================================
                 * LAYER CONTROL (GIS STYLE)
                 * ===================================================== */

                L.control.layers({
                    "OpenStreetMap": osm,
                    "Satellite": satellite,
                    "Hybrid": hybrid,
                    "Carto Light": cartoLight,
                    "Carto Dark": cartoDark
                }, {
                    "Disaster Risk": disasterLayer,
                    "Public Health": healthLayer,
                    "Land Use": landUseLayer
                }, {
                    collapsed: false
                }).addTo(map);

                /* =====================================================
                 * FULLSCREEN
                 * ===================================================== */

                L.control.fullscreen({
                    position: 'topleft',
                    title: 'Fullscreen',
                    titleCancel: 'Exit fullscreen'
                }).addTo(map);
            });

            /* =====================================================
             * HELPERS (UNCHANGED)
             * ===================================================== */

            function getCategoryName(category) {
                return {
                    disaster: 'Disaster Risk',
                    health: 'Public Health',
                    land_use: 'Land Use'
                } [category] || 'Unknown';
            }

            function getCategoryBadgeClass(category) {
                return {
                    disaster: 'disaster-badge',
                    health: 'health-badge',
                    land_use: 'landuse-badge'
                } [category] || '';
            }

            function getCategoryIconColor(category) {
                return {
                    disaster: 'text-danger',
                    health: 'text-success',
                    land_use: 'text-primary'
                } [category] || 'text-secondary';
            }

            // ✅ ENHANCED MODAL HANDLER
            document.addEventListener('click', e => {

                const btn = e.target.closest('.view-meta');
                if (!btn) return;

                const id = btn.dataset.id;
                const item = shapefiles.find(s => s.id == id);
                if (!item) return;

                // Update modal header
                document.getElementById('modalCategory').textContent = getCategoryName(item.category);
                document.getElementById('modalCategory').className =
                    `badge fs-6 ${getCategoryBadgeClass(item.category)}`;
                document.getElementById('metadataCount').textContent = `${item.metadata.length} metadata items`;

                // Update timestamp
                const now = new Date();
                document.getElementById('modalTimestamp').textContent = now.toLocaleString();

                // Build metadata content
                let html = '';

                if (item.metadata.length === 0) {
                    html = `
            <div class="text-center py-5">
                <i class="fas fa-database fa-3x mb-3" style="color:#b71c1c;"></i>
                <h6 class="text-muted">No metadata available</h6>
                <p class="small text-muted mt-2">This shapefile doesn't have any metadata attached.</p>
            </div>
        `;
                } else {
                    html = '<div class="row g-3">';

                    item.metadata.forEach((m, index) => {
                        html += `
                <div class="col-md-6">
                    <div class="metadata-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="fw-bold">${m.meta_key}</span>
                            <span class="badge bg-light text-dark small">#${index + 1}</span>
                        </div>
                        <div class="text-muted" style="word-break: break-word; line-height: 1.6;">
                            ${m.meta_value || '<span class="text-muted fst-italic">Not specified</span>'}
                        </div>
                    </div>
                </div>
            `;
                    });

                    html += '</div>';

                    // Add summary (footer)
                    html += `
            <div class="mt-4 p-3 rounded-3" style="background-color: rgba(183, 28, 28, 0.05);">
                <div class="row">
                    <div class="col-md-6">
                        <div class="small">
                            <i class="fas fa-layer-group me-1" style="color:#b71c1c;"></i>
                            <strong style="color:#b71c1c;">Total Items:</strong> ${item.metadata.length}
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="small">
                            <i class="fas fa-tag me-1" style="color:#b71c1c;"></i>
                            <strong style="color:#b71c1c;">Category:</strong> ${getCategoryName(item.category)}
                        </div>
                    </div>
                </div>
            </div>
        `;
                }

                document.getElementById('metadataModalBody').innerHTML = html;

                // Show modal with animation
                const metadataModal = new bootstrap.Modal(document.getElementById('metadataModal'), {
                    backdrop: 'static'
                });
                metadataModal.show();
            });

            // Auto-submit form on mobile for better UX
            document.getElementById('categorySelect').addEventListener('change', function() {
                if (window.innerWidth < 768) {
                    document.getElementById('categoryForm').submit();
                }
            });

            // Add smooth scrolling for better UX
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        </script>
    @endpush
@endsection
