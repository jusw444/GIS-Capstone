@extends('layouts.app')

@section('content')
<div class="d-flex" style="min-height:100vh;font-family:'Nunito',sans-serif;">

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column" style="width:250px;background:#b71c1c;color:white;padding:30px;">
        <h2 class="mb-4" style="font-weight:bold;">Admin Panel</h2>
        <ul class="nav flex-column">
            <li class="nav-item mb-3">
                <a href="{{ route('admin.dashboard') }}" class="nav-link text-white">Dashboard</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content flex-grow-1 p-4" style="background:#f5f5f5;">
        <h1 class="mb-4" style="color:#b71c1c;">Edit Shapefile</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('shapefiles.update', $shapefile->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Category -->
            <div class="mb-4">
                <label class="form-label fw-bold">Category</label>
                <select name="category" class="form-select" required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $shapefile->category == $cat ? 'selected' : '' }}>
                            {{ ucfirst($cat) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Map -->
            <div class="mb-4">
                <label class="form-label fw-bold">Edit Polygon</label>
                <div id="map" style="height:400px;border-radius:10px;"></div>
                <input type="hidden" name="geometry" id="geometry">
            </div>

            <!-- Metadata -->
            <div class="mb-4">
                <label class="form-label fw-bold">Metadata</label>
                <div id="metadata-container">
                    @foreach ($shapefile->metadata as $i => $meta)
                        <div class="metadata-row mb-2 d-flex gap-2">
                            <input type="text" name="metadata[{{ $i }}][key]" value="{{ $meta->meta_key }}" class="form-control" required>
                            <input type="text" name="metadata[{{ $i }}][value]" value="{{ $meta->meta_value }}" class="form-control">
                            <button type="button" class="btn btn-danger remove-meta">-</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-meta" class="btn btn-danger mt-2">+ Add Metadata</button>
            </div>

            <button type="submit" class="btn btn-danger mt-3">Update Shapefile</button>
        </form>
    </div>
</div>

<!-- Leaflet & Draw -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

<script>
let originalGeo = {!! $geoJson ? json_encode($geoJson) : 'null' !!};
let drawnItems = new L.FeatureGroup();
let map = L.map('map', { center:[14.28,121.4], zoom:10 });
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
drawnItems.addTo(map);

// Add existing polygon if any
if (originalGeo && originalGeo.type && originalGeo.coordinates) {
    let coords = originalGeo.coordinates.map(ring => ring.map(c => [c[1], c[0]]));
    let layer = L.polygon(coords).addTo(drawnItems);
    map.fitBounds(layer.getBounds());
    document.getElementById('geometry').value = JSON.stringify(originalGeo);
}

// Leaflet Draw Controls
let drawControl = new L.Control.Draw({
    position: 'topright',
    edit: {
        featureGroup: drawnItems,
        edit: true,
        remove: true
    },
    draw: {
        polygon: true,
        polyline: false,
        rectangle: false,
        circle: false,
        marker: false
    }
});
map.addControl(drawControl);

// Function to update hidden input
function updateGeometry(layer){
    let geo = layer.toGeoJSON().geometry;
    document.getElementById('geometry').value = JSON.stringify(geo);
}

// Events
map.on(L.Draw.Event.CREATED, e => {
    drawnItems.clearLayers(); // only one polygon at a time
    drawnItems.addLayer(e.layer);
    updateGeometry(e.layer);
});

map.on(L.Draw.Event.EDITED, e => {
    e.layers.eachLayer(layer => updateGeometry(layer));
});

map.on(L.Draw.Event.DELETED, e => {
    document.getElementById('geometry').value = '';
});

// Metadata dynamic fields
let metaIndex = {{ count($shapefile->metadata) }};
document.getElementById('add-meta').addEventListener('click', () => {
    let row = document.createElement('div');
    row.className = "metadata-row mb-2 d-flex gap-2";
    row.innerHTML = `
        <input type="text" name="metadata[${metaIndex}][key]" class="form-control" placeholder="Meta Key" required>
        <input type="text" name="metadata[${metaIndex}][value]" class="form-control" placeholder="Meta Value">
        <button type="button" class="btn btn-danger remove-meta">-</button>
    `;
    document.getElementById('metadata-container').appendChild(row);
    metaIndex++;
});

document.getElementById('metadata-container').addEventListener('click', function(e){
    if(e.target.classList.contains('remove-meta')){
        e.target.parentNode.remove();
    }
});
</script>
@endsection
