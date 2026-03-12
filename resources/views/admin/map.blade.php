@extends('layouts.app')

@section('page_title', $page['pageTitle'])

@section('content')
    <div class="container-fluid vh-100 d-flex flex-column p-3">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h1 class="h3 fw-bold mb-1 text-danger">{{ $page['pageName'] }}</h1>
                <p class="text-muted mb-0">Interactive map visualization for your assigned category</p>
            </div>
            {{-- <div>
                <span class="badge" style="background-color: rgba(183,28,28,.1); color:#b71c1c;">
                    <i class="fas fa-layer-group me-1"></i> Total Shapefiles: {{ count($geojson) }}
                </span>
            </div> --}}
        </div>

        <!-- Main Content -->
        <div class="row flex-grow-1 g-3 overflow-hidden">

            <!-- Left Panel -->
<div class="col-lg-4 d-flex">
    <div class="card w-100 shadow-sm border-0 d-flex flex-column" style="height:100%;">

        <!-- Header -->
        <div class="card-header bg-white border-0 pb-0">
            <h5 class="fw-semibold mb-0 text-danger">
                <i class="fas fa-chart-pie me-2"></i>Analysis Summary
            </h5>
        </div>

        <!-- Body -->
        <div class="card-body d-flex flex-column pt-3 p-0">

            <!-- Scrollable Category Container -->
            <div class="category-scroll flex-grow-1 px-3 py-2">
                <div class="row g-2">
                    @foreach ($categoryLegend as $cat)
                        <div class="col-6">
                            <div class="category-card p-3 rounded-3 text-center"
                                style="background: {{ $cat['color'] }}15; border-left:4px solid {{ $cat['color'] }};">

                                <div class="fw-bold fs-4" style="color: {{ $cat['color'] }}">
                                    {{ $cat['count'] }}
                                </div>

                                <div class="small text-muted text-capitalize">
                                    {{ str_replace('_', ' ', $cat['name']) }}
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- FILTER SECTION (Fixed Bottom) -->
            <div class="border-top pt-3 px-3 pb-3">
                <div class="mb-3">
                    <label class="small text-muted mb-1">Filter by Category</label>
                    <select id="categoryFilter" class="form-select">
                        <option value="all">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->name }}">{{ ucfirst($cat->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="small text-muted mb-1">Filter by Classification</label>
                    <select id="classificationFilter" class="form-select">
                        <option value="all">All Classifications</option>
                        @foreach ($classifications as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex align-items-center small text-muted">
                    <i class="fas fa-info-circle me-2 text-danger"></i>
                    Click on any shape on the map to view details
                </div>
            </div>

        </div>
    </div>
</div>

            <!-- Right Panel: Map -->
            <div class="col-lg-8 d-flex">
                <div class="card w-100 shadow-sm d-flex flex-column">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-semibold mb-0 text-danger"><i class="fas fa-map-marked-alt me-2"></i>Interactive Map
                        </h5>
                        <span class="badge" style="width:12px; height:12px; background-color:#b71c1c;"></span>
                    </div>
                    <div class="card-body p-0 flex-grow-1 d-flex">
                        <div id="map" class="w-100 h-100"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Metadata Modal (unchanged) -->
    <div class="modal fade" id="metadataModal" tabindex="-1" aria-labelledby="metadataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header text-white" style="background-color: #b71c1c;">
                    <h5 class="modal-title" id="metadataModalLabel"> <i class="fas fa-database me-2"></i>Shapefile Metadata
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="sticky-top bg-light p-4 border-bottom">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <span class="badge fs-6" id="modalCategory"
                                        style="background-color: rgba(183, 28, 28, 0.1); color: #b71c1c;"></span>
                                </div>
                                <h6 class="fw-semibold mb-1">Metadata Items</h6>
                                <p class="text-muted mb-0" id="metadataCount">0 items</p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="text-muted small">
                                    <i class="fas fa-calendar-alt me-1" style="color:#b71c1c;"></i> <span
                                        id="modalTimestamp">Loaded just now</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-4" id="metadataModalBody"> <!-- Metadata content will be inserted here --> </div>
                </div>
                <div class="modal-footer border-top-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"> <i
                            class="fas fa-times me-1"></i> Close </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.fullscreen@1.6.0/Control.FullScreen.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.fullscreen@1.6.0/Control.FullScreen.css" />

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

            /* Ensure modal is above Leaflet fullscreen */
            .modal {
                z-index: 1055 !important;
            }

            .leaflet-container.fullscreen-on {
                z-index: 1000 !important;
                /* optional, make sure it's lower than modal */
            }

            /* Left Panel */
            /* Scrollable category container */
            .category-scroll {
                overflow-y: auto;
                max-height: 260px;
                padding-right: 4px;
            }

            /* Modern category cards */
            .category-card {
                transition: all .2s ease;
            }

            .category-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
            }

            /* Modern scrollbar */
            .category-scroll::-webkit-scrollbar {
                width: 6px;
            }

            .category-scroll::-webkit-scrollbar-thumb {
                background: #ddd;
                border-radius: 10px;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            const shapefiles = @json($geojson);
            const categories = @json($categories);
            const classifications = @json($classifications);

            document.addEventListener('DOMContentLoaded', () => {
                const map = L.map('map', {
                    center: [14.28, 121.4],
                    zoom: 10,
                    zoomControl: true,
                    scrollWheelZoom: true
                });
                const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 20,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);
                const satellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                });
                const hybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                });
                const cartoLight = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                    maxZoom: 20,
                    attribution: '&copy; CartoDB'
                });
                const cartoDark = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                    maxZoom: 20,
                    attribution: '&copy; CartoDB'
                });

                L.control.scale({
                    imperial: false,
                    position: 'bottomleft'
                }).addTo(map);
                L.control.layers({
                    "OSM": osm,
                    "Satellite": satellite,
                    "Hybrid": hybrid,
                    "Carto Light": cartoLight,
                    "Carto Dark": cartoDark
                }).addTo(map);
                L.control.fullscreen({
                    position: 'topleft',
                    title: 'Fullscreen',
                    titleCancel: 'Exit fullscreen'
                }).addTo(map);

                const layerGroup = L.featureGroup().addTo(map);
                let legendControl = null;

                function getClassificationColor(id) {
                    const cls = classifications.find(c => c.id == id);
                    return cls?.color ?? '#b71c1c';
                }

                function updateLegend(filteredShapes) {
                    if (legendControl) map.removeControl(legendControl);
                    legendControl = L.control({
                        position: 'bottomright'
                    });
                    legendControl.onAdd = function() {
                        const div = L.DomUtil.create('div', 'card p-3 shadow-sm');
                        div.style.background = 'white';
                        div.style.borderRadius = '12px';
                        let html = `<h6 class="fw-bold mb-2" style="color:#b71c1c;">Legend</h6>`;
                        const usedClsIds = [...new Set(filteredShapes.map(s => s.classification_id))];
                        classifications.filter(c => usedClsIds.includes(c.id)).forEach(c => {
                            html += `<div class="d-flex align-items-center mb-1">
                                <span style="width:16px;height:16px;background:${c.color};display:inline-block;margin-right:8px;border-radius:4px;"></span>
                                <span class="small">${c.name}</span>
                            </div>`;
                        });
                        div.innerHTML = html;
                        return div;
                    };
                    legendControl.addTo(map);
                }

                function updateClassificationOptions(category) {
                    const select = document.getElementById('classificationFilter');
                    select.innerHTML = `<option value="all">All Classifications</option>`;
                    const filteredCls = category === 'all' ?
                        classifications :
                        classifications.filter(c => shapefiles.some(s => s.category === category && s
                            .classification_id === c.id));
                    filteredCls.forEach(c => {
                        select.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                    });
                }

                function renderMap(categoryFilter = "all", classificationFilter = "all") {
                    layerGroup.clearLayers();
                    const filteredShapes = shapefiles.filter(item => {
                        if (!item.geometry) return false;
                        if (categoryFilter !== 'all' && item.category !== categoryFilter) return false;
                        if (classificationFilter !== 'all' && item.classification_id != classificationFilter)
                            return false;
                        return true;
                    });

                    filteredShapes.forEach(item => {
                        const style = {
                            color: getClassificationColor(item.classification_id),
                            fillColor: getClassificationColor(item.classification_id),
                            weight: 3,
                            opacity: 0.8,
                            fillOpacity: 0.2
                        };
                        L.geoJSON(item.geometry, {
                            style: style,
                            onEachFeature: (feature, layer) => {
                                let metaHtml = '';
                                const MAX = 5;
                                let extraCount = 0;
                                if (item.metadata?.length) {
                                    item.metadata.slice(0, MAX).forEach(m => {
                                        metaHtml += `<div class="mb-2">
                                    <span class="fw-semibold">${m.meta_key}:</span>
                                    <span class="ms-2">${m.meta_value || '<em class="text-muted">Not specified</em>'}</span>
                                </div>`;
                                    });
                                    extraCount = item.metadata.length - MAX;
                                }

                                const popupContent = `
                        <div style="max-width: 320px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge fs-6 ${(item.category)} text-black"> ${(item.category)} </span>
                                </div>
                            </div>
                            <div class="mb-3" style="max-height:180px; overflow-y:auto; padding-right:8px;">
                                ${metaHtml || '<div class="text-center text-muted py-3"><i class="fas fa-info-circle me-1"></i>No metadata available</div>'}
                            </div>
                            ${extraCount>0 ? `<div class="text-center pt-2 border-top">
                                                                <button type="button" class="btn btn-sm view-meta" data-id="${item.feature_id}" style="background-color:#b71c1c;color:white;border-radius:20px;padding:0.25rem 1rem;border:none;">
                                                                    <i class="fas fa-ellipsis-h me-1"></i> View all metadata (${item.metadata.length})
                                                                </button>
                                                            </div>` : ''}
                            <div class="mt-3 pt-2 border-top small text-muted text-center">
                                <i class="fas fa-mouse-pointer me-1"></i> Click for details
                            </div>
                        </div>`;
                                layer.bindPopup(popupContent);
                                layer.on('mouseover', () => layer.setStyle({
                                    weight: 4,
                                    fillOpacity: 0.3
                                }));
                                layer.on('mouseout', () => layer.setStyle(style));
                            }
                        }).addTo(layerGroup);
                    });

                    if (layerGroup.getLayers().length) map.fitBounds(layerGroup.getBounds(), {
                        padding: [50, 50],
                        maxZoom: 15
                    });
                    updateLegend(filteredShapes);
                }

                // INITIAL RENDER
                renderMap();

                // CATEGORY FILTER CHANGE
                document.getElementById('categoryFilter').addEventListener('change', function() {
                    const category = this.value;
                    updateClassificationOptions(category);
                    const classification = document.getElementById('classificationFilter').value;
                    renderMap(category, classification);
                });

                // CLASSIFICATION FILTER CHANGE
                document.getElementById('classificationFilter').addEventListener('change', function() {
                    const category = document.getElementById('categoryFilter').value;
                    const classification = this.value;
                    renderMap(category, classification);
                });

                // METADATA MODAL HANDLER (unchanged)
                document.addEventListener('click', e => {
                    const btn = e.target.closest('.view-meta');
                    if (!btn) return;
                    const id = btn.dataset.id;
                    const item = shapefiles.find(s => s.feature_id == id);
                    if (!item) return;

                    const badge = document.getElementById('modalCategory');
                    badge.textContent = item.category;
                    badge.className = 'badge fs-6 text-black';
                    document.getElementById('metadataCount').textContent =
                        `${item.metadata.length} metadata items`;
                    document.getElementById('modalTimestamp').textContent = new Date().toLocaleString();

                    let html = '';
                    if (item.metadata.length === 0) {
                        html =
                            `<div class="text-center py-5"><i class="fas fa-database fa-3x mb-3" style="color:#b71c1c;"></i><h6 class="text-muted">No metadata available</h6><p class="small text-muted mt-2">This shapefile doesn't have any metadata attached.</p></div>`;
                    } else {
                        html = '<div class="row g-3">';
                        item.metadata.forEach((m, index) => {
                            html +=
                                `<div class="col-md-6"><div class="metadata-item"><div class="d-flex justify-content-between align-items-start mb-2"><span class="fw-bold">${m.meta_key}</span><span class="badge bg-light text-dark small">#${index+1}</span></div><div class="text-muted" style="word-break:break-word;line-height:1.6;">${m.meta_value || '<span class="text-muted fst-italic">Not specified</span>'}</div></div></div>`;
                        });
                        html += '</div>';
                        html +=
                            `<div class="mt-4 p-3 rounded-3" style="background-color: rgba(183,28,28,0.05);"><div class="row"><div class="col-md-6"><div class="small"><i class="fas fa-layer-group me-1" style="color:#b71c1c;"></i><strong style="color:#b71c1c;">Total Items:</strong> ${item.metadata.length}</div></div><div class="col-md-6 text-md-end"><div class="small"><i class="fas fa-tag me-1" style="color:#b71c1c;"></i><strong style="color:#b71c1c;">Category:</strong> ${(item.category)}</div></div></div></div>`;
                    }
                    document.getElementById('metadataModalBody').innerHTML = html;
                    const metadataModal = new bootstrap.Modal(document.getElementById('metadataModal'), {
                        backdrop: 'static'
                    });
                    metadataModal.show();
                });

            });
        </script>
    @endpush
@endsection
