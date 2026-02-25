@extends('layouts.app')

@section('page_title', $page['pageTitle'])

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-5">
        <div>
            <h1 class="h3 fw-bold mb-2" style="color:#b71c1c;">{{ $page['pageName'] }}</h1>
            <p class="text-muted mb-0">Interactive map visualization for your assigned category</p>
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
        <!-- Left Column: Analysis -->
        <div class="col-lg-4">
            <!-- Analysis Panel -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="fw-semibold mb-0" style="color:#b71c1c;">
                        <i class="fas fa-chart-pie me-2" style="color:#b71c1c;"></i>Analysis Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="text-center p-3 rounded-3" style="background-color: rgba(183, 28, 28, 0.1);">
                                <div class="h4 fw-bold mb-1" style="color: #b71c1c;">
                                    {{ count($geojson) }}
                                </div>
                                <div class="small" style="color:#b71c1c;">
                                    {{ ucfirst($geojson[0]->category ?? 'Category') }}
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
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-semibold mb-0" style="color:#b71c1c;">
                        <i class="fas fa-map-marked-alt me-2" style="color:#b71c1c;"></i>Interactive Map
                    </h5>
                    <div class="d-flex align-items-center">
                        <span class="badge me-1"
                              style="width: 12px; height: 12px; background-color: #b71c1c;"></span>
                        {{ ucfirst($geojson[0]->category ?? '') }}
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 650px; border-radius: 0 0 8px 8px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Metadata Modal -->
<div class="modal fade" id="metadataModal" tabindex="-1" aria-labelledby="metadataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: #b71c1c;">
                <h5 class="modal-title" id="metadataModalLabel">
                    <i class="fas fa-database me-2"></i>Shapefile Metadata
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="sticky-top bg-light p-4 border-bottom">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <span class="badge fs-6" id="modalCategory"></span>
                            </div>
                            <h6 class="fw-semibold mb-1">Metadata Items</h6>
                            <p class="text-muted mb-0" id="metadataCount">0 items</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="text-muted small">
                                <i class="fas fa-calendar-alt me-1" style="color:#b71c1c;"></i>
                                <span id="modalTimestamp">Loaded just now</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-4" id="metadataModalBody"></div>
            </div>
            <div class="modal-footer border-top-0 bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.fullscreen@1.6.0/Control.FullScreen.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet.fullscreen@1.6.0/Control.FullScreen.css" />

@push('scripts')
<script>
    const shapefiles = @json($geojson);

    document.addEventListener('DOMContentLoaded', () => {
        const map = L.map('map', {
            center: [14.28, 121.4],
            zoom: 10,
            zoomControl: true,
        });

        // Base Tile
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const layerGroup = L.featureGroup().addTo(map);

        const categoryStyle = {
            color: '#b71c1c',
            fillColor: '#b71c1c',
            weight: 3,
            opacity: 0.8,
            fillOpacity: 0.2
        };

        shapefiles.forEach(item => {
            if (!item.geometry) return;

            const geoLayer = L.geoJSON(item.geometry, {
                style: categoryStyle,
                onEachFeature: (feature, layer) => {
                    let metaHtml = '';
                    (item.metadata || []).forEach(m => {
                        metaHtml += `<div><strong>${m.meta_key}:</strong> ${m.meta_value || '<em>Not specified</em>'}</div>`;
                    });

                    const popupContent = `
                        <div style="max-width: 320px;">
                            <span class="badge disaster-badge">${item.category}</span>
                            <div style="margin-top:8px;">${metaHtml || '<em>No metadata available</em>'}</div>
                            <div class="mt-2 small text-muted text-center">Click for details</div>
                        </div>
                    `;

                    layer.bindPopup(popupContent);

                    layer.on('click', () => {
                        const modal = new bootstrap.Modal(document.getElementById('metadataModal'));
                        document.getElementById('modalCategory').textContent = item.category;
                        document.getElementById('metadataCount').textContent = `${item.metadata.length} metadata items`;

                        let html = '<div class="row g-3">';
                        item.metadata.forEach((m, i) => {
                            html += `<div class="col-md-6">
                                        <div class="metadata-item">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="fw-bold">${m.meta_key}</span>
                                                <span class="badge bg-light text-dark small">#${i+1}</span>
                                            </div>
                                            <div class="text-muted">${m.meta_value || '<em>Not specified</em>'}</div>
                                        </div>
                                     </div>`;
                        });
                        html += '</div>';
                        document.getElementById('metadataModalBody').innerHTML = html;

                        const now = new Date();
                        document.getElementById('modalTimestamp').textContent = now.toLocaleString();

                        modal.show();
                    });
                }
            }).addTo(layerGroup);
        });

        if (layerGroup.getLayers().length) {
            map.fitBounds(layerGroup.getBounds(), { padding: [50, 50], maxZoom: 15 });
        }

        L.control.fullscreen({ position: 'topleft', title: 'Fullscreen', titleCancel: 'Exit fullscreen' }).addTo(map);
    });
</script>
@endpush
@endsection