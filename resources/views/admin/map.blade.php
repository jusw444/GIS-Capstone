@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">Laguna GIS Viewer</h2>

    <!-- Category Filter -->
    <form method="GET" action="{{ route('admin.view') }}" class="mb-3">
        <select name="category" class="form-control" onchange="this.form.submit()">
            <option value="">-- All Categories --</option>
            <option value="disaster" {{ $selectedCategory=='disaster' ? 'selected' : '' }}>Disaster Risk</option>
            <option value="health" {{ $selectedCategory=='health' ? 'selected' : '' }}>Public Health</option>
            <option value="land_use" {{ $selectedCategory=='land_use' ? 'selected' : '' }}>Land Use</option>
        </select>
    </form>

    <!-- Map Container -->
    <div id="map" style="height: 600px; border-radius: 10px;"></div>

    <br>

    <!-- Analysis Panel -->
    <div class="card mt-4">
        <div class="card-header">
            <strong>Map Analysis</strong>
        </div>
        <div class="card-body">
            <p><strong>Total Shapefiles:</strong> {{ count($geojson) }}</p>
            <p><strong>Breakdown:</strong></p>
            <ul>
                <li>Disaster Risk: {{ $geojson->where('category', 'disaster')->count() }}</li>
                <li>Public Health: {{ $geojson->where('category', 'health')->count() }}</li>
                <li>Land Use: {{ $geojson->where('category', 'land_use')->count() }}</li>
            </ul>
        </div>
    </div>
</div>

<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let shapefiles = @json($geojson);

    // Initialize map (Laguna center fallback)
    var map = L.map('map').setView([14.28, 121.40], 10);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 20,
    }).addTo(map);

    // Create a feature group to hold all polygons
    var allLayers = L.featureGroup();

    shapefiles.forEach(item => {
        if (!item.geometry || !item.geometry.type || !item.geometry.coordinates) {
            console.warn('Invalid geometry for ID:', item.id);
            return;
        }

        // Create GeoJSON layer
        let layer = L.geoJSON(item.geometry, {
            style: {
                color: item.category === 'disaster' ? 'red' :
                       item.category === 'health' ? 'green' : 'blue',
                weight: 2,
                fillOpacity: 0.4
            }
        });

        // Prepare metadata popup
        let metaHtml = '';
        item.metadata.forEach(m => {
            metaHtml += `<strong>${m.meta_key}:</strong> ${m.meta_value}<br>`;
        });

        layer.bindPopup(`
            <strong>Category:</strong> ${item.category}<br>
            ${metaHtml}
        `);

        layer.addTo(allLayers);
    });

    // Add all layers to map
    allLayers.addTo(map);

    // Fit map bounds
    if (allLayers.getLayers().length > 0) {
        map.fitBounds(allLayers.getBounds(), {padding: [20, 20]});
    }
</script>
@endsection
