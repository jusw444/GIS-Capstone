@extends('layouts.app')

@section('page_title', $page['pageTitle'])

@section('content')
<div class="container-fluid" style="background:#f4f6f9; min-height:100vh; font-family:'Nunito',sans-serif;">

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

    <form id="shapefile-form" action="{{ route('shapefiles.update', $shapefile->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- FLEX CONTAINER FOR MAP AND SIDEBAR -->
        <div class="d-flex gap-3 align-items-stretch" style="min-height:80vh;" id="map-sidebar-container">

            <!-- MAP AREA -->
            <div class="flex-grow-1">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-2 d-flex flex-column" style="height:100%;">
                        <div id="map" class="flex-grow-1 rounded-4" style="min-height:500px;"></div>
                        <input type="hidden" name="geometry" id="geometry">
                    </div>
                </div>
            </div>

            <!-- SIDEBAR -->
            <div id="sidebar" style="width:320px; flex-shrink:0; display:flex; flex-direction:column;">

                <!-- CATEGORY -->
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">Category</h6>
                        <select name="category" class="form-select form-select-sm" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ $shapefile->category == $cat ? 'selected' : '' }}>
                                    {{ ucfirst($cat) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- METADATA (SCROLLABLE) -->
                <div class="card border-0 shadow-sm rounded-4 mb-3 flex-grow-1 d-flex flex-column" style="overflow:hidden;">
                    <div class="card-body d-flex flex-column p-2 flex-grow-1">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0">Metadata</h6>
                            <button type="button" id="add-meta" class="btn btn-outline-danger btn-sm">Add</button>
                        </div>

                        <!-- METADATA LIST -->
                        <div id="metadata-container" class="overflow-auto" style="max-height: calc(5 * 60px + 8px);">
                            {{-- 5 metadata rows approx. 60px each including margin --}}
                        </div>
                    </div>
                </div>

                <!-- SUBMIT BUTTON -->
                <div class="d-grid mt-auto">
                    <button type="submit" class="btn btn-danger rounded-3 py-2 w-100">
                        Update Shapefile
                    </button>
                </div>

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
    let originalGeo = {!! $geoJson ? json_encode($geoJson) : 'null' !!};
    let map = L.map('map', { center:[14.28,121.4], zoom:10 });
    let drawnItems = new L.FeatureGroup();

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    map.addLayer(drawnItems);

    function addGeoJSONToMap(geo){
        if(!geo || geo.type !== 'FeatureCollection') return;

        geo.features.forEach(f => {
            if(!f.geometry) return;
            let g = f.geometry;

            if(g.type === 'Polygon'){
                let c = g.coordinates.map(r => r.map(x => [x[1], x[0]]));
                drawnItems.addLayer(L.polygon(c));
            }

            if(g.type === 'MultiPolygon'){
                g.coordinates.forEach(p => {
                    let c = p.map(r => r.map(x => [x[1], x[0]]));
                    drawnItems.addLayer(L.polygon(c));
                });
            }
        });
    }

    addGeoJSONToMap(originalGeo);

    if(drawnItems.getLayers().length){
        map.fitBounds(drawnItems.getBounds());
        document.getElementById('geometry').value = JSON.stringify(originalGeo);
    }

    map.addControl(new L.Control.Draw({
        edit: { featureGroup: drawnItems },
        draw: { polygon:true, polyline:false, rectangle:false, circle:false, marker:false }
    }));

    function updateGeometry(layer){
        document.getElementById('geometry').value = JSON.stringify({
            type:'FeatureCollection',
            features:[{ type:'Feature', geometry: layer.toGeoJSON().geometry, properties:{} }]
        });
    }

    map.on(L.Draw.Event.CREATED, e => {
        drawnItems.clearLayers();
        drawnItems.addLayer(e.layer);
        updateGeometry(e.layer);
    });
    map.on(L.Draw.Event.EDITED, e => e.layers.eachLayer(updateGeometry));
    map.on(L.Draw.Event.DELETED, () => document.getElementById('geometry').value = '');

    /* ================= METADATA ================= */
    let metadata = Array.isArray(@json($shapefile->metadata)) ? @json($shapefile->metadata) : [];
    const container = document.getElementById('metadata-container');

    function renderMetadata(focusIndex = null) {
        container.innerHTML = '';
        metadata.forEach((m, i) => {
            const div = document.createElement('div');
            div.className = 'border rounded-3 p-2 mb-2 bg-light d-flex align-items-center gap-2';
            div.innerHTML = `
                <div class="flex-grow-1">
                    <input type="text" class="form-control form-control-sm mb-1"
                           name="metadata[${i}][key]" placeholder="Key" value="${m.meta_key ?? ''}" required>
                    <input type="text" class="form-control form-control-sm"
                           name="metadata[${i}][value]" placeholder="Value" value="${m.meta_value ?? ''}">
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm remove-meta rounded-circle"
                        data-index="${i}" style="width:28px;height:28px;line-height:1;">✕</button>`;
            container.appendChild(div);
        });

        // Focus on new metadata input
        if(focusIndex !== null){
            const newInput = container.querySelectorAll('input[name$="[key]"]')[focusIndex];
            if(newInput) {
                newInput.focus();
                newInput.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    }

    function syncMetadata(){
        const divs = container.querySelectorAll('div.border');
        metadata = Array.from(divs).map(div => ({
            meta_key: div.querySelector('input[name$="[key]"]').value,
            meta_value: div.querySelector('input[name$="[value]"]').value
        }));
    }

    // Add new metadata
    document.getElementById('add-meta').addEventListener('click', () => {
        syncMetadata();
        metadata.push({meta_key:'', meta_value:''});
        renderMetadata(metadata.length - 1); // focus on last added
    });

    // Remove metadata
    container.addEventListener('click', e => {
        if(e.target.classList.contains('remove-meta')){
            syncMetadata();
            const index = parseInt(e.target.dataset.index);
            metadata.splice(index, 1);
            renderMetadata();
        }
    });

    document.getElementById('shapefile-form').addEventListener('submit', ()=>{
        syncMetadata();
    });

    renderMetadata();

    /* ================= DYNAMIC SIDEBAR HEIGHT ================= */
    function adjustSidebarHeight(){
        const mapEl = document.getElementById('map');
        const sidebarEl = document.getElementById('sidebar');
        const mapHeight = mapEl.getBoundingClientRect().height;
        sidebarEl.style.height = mapHeight + 'px';
    }

    // Adjust on load and resize
    adjustSidebarHeight();
    window.addEventListener('resize', adjustSidebarHeight);

});
</script>
@endpush
@endsection
