@extends('layouts.app')

@section('page_title', $page['pageTitle'])

@section('content')
<div class="container">
    <h3 class="fw-bold mb-4" style="color:#b71c1c;">{{ $page['pageName'] }}</h3>

    <!-- Category Filter -->
    <form method="GET" action="{{ route('admin.view') }}" class="mb-3">
        <select name="category" class="form-control" onchange="this.form.submit()">
            <option value="">-- All Categories --</option>
            <option value="disaster" {{ $category=='disaster' ? 'selected' : '' }}>Disaster Risk</option>
            <option value="health" {{ $category=='health' ? 'selected' : '' }}>Public Health</option>
            <option value="land_use" {{ $category=='land_use' ? 'selected' : '' }}>Land Use</option>
        </select>
    </form>

    <!-- Map -->
    <div id="map" style="height:600px;border-radius:10px;"></div>

    <br>

    <!-- Analysis Panel -->
    <div class="card mt-4">
        <div class="card-header"><strong>Map Analysis</strong></div>
        <div class="card-body">
            <p><strong>Total Shapefiles:</strong> {{ count($geojson) }}</p>
            <ul>
                <li>Disaster Risk: {{ $geojson->where('category','disaster')->count() }}</li>
                <li>Public Health: {{ $geojson->where('category','health')->count() }}</li>
                <li>Land Use: {{ $geojson->where('category','land_use')->count() }}</li>
            </ul>
        </div>
    </div>
</div>

<!-- Metadata Modal -->
<div class="modal fade" id="metadataModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Shapefile Metadata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="metadataModalBody"></div>
        </div>
    </div>  
</div>

<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@push('scripts')
<script>
// 🔑 GLOBAL DATA (IMPORTANT FIX)
window.shapefiles = @json($geojson);

document.addEventListener('DOMContentLoaded', () => {

    const map = L.map('map', {
        center: [14.28, 121.40],
        zoom: 10
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 20
    }).addTo(map);

    const allLayers = L.featureGroup().addTo(map);

    shapefiles.forEach(item => {

        if (!item.geometry || item.geometry.type !== 'FeatureCollection') return;

        const layer = L.geoJSON(item.geometry, {
            style: {
                color:
                    item.category === 'disaster' ? '#c62828' :
                    item.category === 'health'   ? '#2e7d32' :
                                                   '#1565c0',
                weight: 2,
                fillOpacity: 0.4
            },
            onEachFeature: (feature, layer) => {

                const MAX_META = 5;
                let metaHtml = '';
                let extraCount = 0;

                if (item.metadata?.length) {
                    item.metadata.slice(0, MAX_META).forEach(m => {
                        metaHtml += `<div><strong>${m.meta_key}:</strong> ${m.meta_value}</div>`;
                    });
                    extraCount = item.metadata.length - MAX_META;
                }

                layer.bindPopup(`
                    <div style="max-width:300px">
                        <strong>Category:</strong> ${item.category}<br><br>
                        <div style="max-height:150px;overflow:auto">
                            ${metaHtml || '<em>No metadata</em>'}
                        </div>
                        ${extraCount > 0 ? `
                            <hr>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger view-meta"
                                data-id="${item.id}">
                                View all metadata (${item.metadata.length})
                            </button>
                        ` : ''}
                    </div>
                `);
            }
        });

        layer.addTo(allLayers);
    });

    if (allLayers.getLayers().length) {
        map.fitBounds(allLayers.getBounds(), { padding: [20, 20] });
    }
});

// ✅ MODAL HANDLER (NOW WORKS)
document.addEventListener('click', e => {

    const btn = e.target.closest('.view-meta');
    if (!btn) return;

    const id = btn.dataset.id;
    const item = shapefiles.find(s => s.id == id);
    if (!item) return;

    let html = `
        <p><strong>Category:</strong> ${item.category}</p>
        <hr>
    `;

    item.metadata.forEach(m => {
        html += `
            <div class="mb-2">
                <strong>${m.meta_key}:</strong><br>
                <span>${m.meta_value ?? '<em>null</em>'}</span>
            </div>
        `;
    });

    document.getElementById('metadataModalBody').innerHTML = html;

    new bootstrap.Modal(
        document.getElementById('metadataModal')
    ).show();
});
</script>
@endpush
@endsection
