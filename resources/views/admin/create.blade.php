@extends('layouts.app')

@section('content')
<div class="d-flex" style="min-height: 100vh; font-family: 'Nunito', sans-serif;">

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column" style="width:250px; background:#b71c1c; color:white; padding:30px;">
        <h2 class="mb-4" style="font-weight:bold;">Admin Panel</h2>
        <ul class="nav flex-column">
            <li class="nav-item mb-3">
                <a href="{{ route('admin.dashboard') }}" class="nav-link text-white" style="text-decoration:none;">Dashboard</a>
            </li>
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white" style="text-decoration:none;">Create Shapefile</a>
            </li>
            <li class="mt-auto">
                <a href="{{ route('logout') }}" class="nav-link text-white" style="text-decoration:none;">Logout</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content flex-grow-1 p-4" style="background:#f5f5f5;">
        <h1 class="mb-4" style="color:#b71c1c;">Create Shapefile</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('shapefiles.store') }}" method="POST">
            @csrf

            <!-- Category -->
            <div class="mb-4">
                <label class="form-label" style="font-weight:bold;">Category</label>
                <select name="category" class="form-select" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Map -->
            <div class="mb-4">
                <label class="form-label" style="font-weight:bold;">Draw Polygon</label>
                <div id="map" style="height: 400px; border-radius:10px;"></div>
                <input type="hidden" name="geometry" id="geometry">
            </div>

            <!-- Dynamic Metadata -->
            <div class="mb-4">
                <label class="form-label" style="font-weight:bold;">Metadata</label>
                <div id="metadata-container">
                    <div class="metadata-row mb-2 d-flex gap-2">
                        <input type="text" name="metadata[0][key]" placeholder="Meta Key" class="form-control" required>
                        <input type="text" name="metadata[0][value]" placeholder="Meta Value" class="form-control">
                        <button type="button" class="btn btn-danger remove-meta">-</button>
                    </div>
                </div>
                <button type="button" id="add-meta" class="btn btn-danger mt-2">+ Add Metadata</button>
            </div>

            <button type="submit" class="btn btn-danger">Save Shapefile</button>
        </form>
    </div>
</div>

<!-- Leaflet JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<!-- Leaflet Draw -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

<script>
    // Initialize Map
    var map = L.map('map').setView([12.5, 121.0], 8); // Center to your province

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(map);

    // FeatureGroup for drawn layers
    var drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    // Leaflet Draw control
    var drawControl = new L.Control.Draw({
        edit: { featureGroup: drawnItems },
        draw: { polygon: true, polyline: false, rectangle: false, circle: false, marker: false, circlemarker: false }
    });
    map.addControl(drawControl);

    // Capture geometry in GeoJSON
    map.on(L.Draw.Event.CREATED, function (e) {
        var layer = e.layer;
        drawnItems.clearLayers(); // allow only 1 polygon
        drawnItems.addLayer(layer);
        var geojson = layer.toGeoJSON();
        document.getElementById('geometry').value = JSON.stringify(geojson.geometry);
    });

    map.on(L.Draw.Event.EDITED, function (e) {
        var layers = e.layers;
        layers.eachLayer(function(layer) {
            var geojson = layer.toGeoJSON();
            document.getElementById('geometry').value = JSON.stringify(geojson.geometry);
        });
    });

    // Dynamic Metadata
    let metaIndex = 1;
    document.getElementById('add-meta').addEventListener('click', function() {
        let container = document.getElementById('metadata-container');
        let row = document.createElement('div');
        row.className = 'metadata-row mb-2 d-flex gap-2';
        row.innerHTML = `
            <input type="text" name="metadata[${metaIndex}][key]" placeholder="Meta Key" class="form-control" required>
            <input type="text" name="metadata[${metaIndex}][value]" placeholder="Meta Value" class="form-control">
            <button type="button" class="btn btn-danger remove-meta">-</button>
        `;
        container.appendChild(row);
        metaIndex++;
    });

    document.getElementById('metadata-container').addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('remove-meta')){
            e.target.parentNode.remove();
        }
    });
</script>
@endsection
