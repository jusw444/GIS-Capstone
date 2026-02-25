@extends('layouts.app')

@section('page_title', $page['pageTitle'])

@section('content')
<div class="container-fluid" style="background:#f4f6f9; min-height:100vh; font-family:'Nunito',sans-serif;">

    @php
        $user = auth()->user();
        $adminCategory = $user->category->name ?? null;
    @endphp

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-0" style="color:#b71c1c;">{{ $page['pageName'] }}</h3>
            <small class="text-muted">Polygon editor with metadata management</small>
        </div>

        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm rounded-pill">
            ← Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger rounded-3 mb-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="shapefile-form" action="{{ route('shapefiles.store') }}" method="POST">
        @csrf

        <div class="d-flex gap-3 align-items-stretch" style="min-height:80vh;">

            <!-- MAP -->
            <div class="flex-grow-1">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-2">
                        <div id="map" class="rounded-4" style="height:100%; min-height:500px;"></div>
                        <input type="hidden" name="geometry" id="geometry">
                    </div>
                </div>
            </div>

            <!-- SIDEBAR -->
            <div style="width:320px; display:flex; flex-direction:column;">

                <!-- CATEGORY -->
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">Category</h6>

                        @if($user->role === 'super_admin')
                            <select name="category_id" class="form-select form-select-sm" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">
                                        {{ ucfirst(str_replace('_',' ', $cat->name)) }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <!-- ADMIN: FIXED CATEGORY -->
                            <input type="hidden" name="category_id" value="{{ $user->category_id }}">

                            <div class="form-control form-control-sm bg-light">
                                {{ ucfirst(str_replace('_',' ', $adminCategory)) }}
                            </div>
                        @endif

                    </div>
                </div>

                <!-- METADATA -->
                <div class="card border-0 shadow-sm rounded-4 mb-3 flex-grow-1">
                    <div class="card-body p-2 d-flex flex-column">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0">Metadata</h6>
                            <button type="button" id="add-meta" class="btn btn-outline-danger btn-sm">
                                Add
                            </button>
                        </div>

                        <div id="metadata-container" class="overflow-auto" style="max-height:300px;"></div>
                    </div>
                </div>

                <!-- SUBMIT -->
                <button type="submit" class="btn btn-danger rounded-3 py-2 w-100 mt-auto">
                    Save Shapefile
                </button>

            </div>
        </div>
    </form>
</div>

<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){

    /* ================= MAP ================= */

    const map = L.map('map', {
        center: [14.28, 121.40],
        zoom: 10,
        minZoom: 8
    });

    const drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    map.addControl(new L.Control.Draw({
        edit: { featureGroup: drawnItems },
        draw: {
            polygon: true,
            polyline: false,
            rectangle: false,
            circle: false,
            marker: false
        }
    }));

    function saveGeometry(layer){
        document.getElementById('geometry').value = JSON.stringify({
            type: 'FeatureCollection',
            features: [{
                type: 'Feature',
                geometry: layer.toGeoJSON().geometry,
                properties: {}
            }]
        });
    }

    map.on(L.Draw.Event.CREATED, e => {
        drawnItems.clearLayers();
        drawnItems.addLayer(e.layer);
        saveGeometry(e.layer);
    });

    map.on(L.Draw.Event.EDITED, e => {
        e.layers.eachLayer(saveGeometry);
    });

    map.on(L.Draw.Event.DELETED, () => {
        document.getElementById('geometry').value = '';
    });

    setTimeout(() => map.invalidateSize(), 300);

    /* ================= METADATA ================= */

    let metadata = [];
    const container = document.getElementById('metadata-container');

    function renderMetadata(focusIndex = null){
        container.innerHTML = '';

        metadata.forEach((m, i) => {
            const row = document.createElement('div');
            row.className = 'border rounded-3 p-2 mb-2 bg-light d-flex align-items-center gap-2';

            row.innerHTML = `
                <div class="flex-grow-1">
                    <input class="form-control form-control-sm mb-1 meta-key"
                        name="metadata[${i}][key]"
                        placeholder="Key"
                        required
                        value="${m.meta_key ?? ''}">

                    <input class="form-control form-control-sm"
                        name="metadata[${i}][value]"
                        placeholder="Value"
                        value="${m.meta_value ?? ''}">
                </div>

                <button type="button"
                    class="btn btn-outline-danger btn-sm rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 remove-meta"
                    data-index="${i}"
                    style="width:32px;height:32px;">
                    ✕
                </button>
            `;

            container.appendChild(row);
        });

        if (focusIndex !== null) {
            const inputs = container.querySelectorAll('.meta-key');
            inputs[focusIndex]?.focus();
        }
    }

    function syncMetadata(){
        metadata = [...container.children].map(row => ({
            meta_key: row.querySelector('input[name$="[key]"]').value,
            meta_value: row.querySelector('input[name$="[value]"]').value
        }));
    }

    document.getElementById('add-meta').onclick = () => {
        syncMetadata();
        metadata.push({ meta_key:'', meta_value:'' });
        renderMetadata(metadata.length - 1);
    };

    container.onclick = e => {
        if(e.target.classList.contains('remove-meta')){
            syncMetadata();
            metadata.splice(e.target.dataset.index, 1);
            renderMetadata();
        }
    };

    document.getElementById('shapefile-form').onsubmit = e => {
        syncMetadata();
        if(!document.getElementById('geometry').value){
            e.preventDefault();
            alert('Please draw a polygon on the map.');
        }
    };

});
</script>
@endpush
@endsection