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
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm rounded-pill">← Back</a>
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

    <form id="shapefile-form" action="{{ route('shapefiles.update', $feature->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- ✅ Metadata hidden inputs -->
        <div id="metadata-form-container"></div>

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

                <!-- CATEGORY + CLASSIFICATION -->
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-3">Layer</h6>

                        @if ($user->role === 'super_admin')
                            <select name="category_id" class="form-select form-select-sm mb-2" required>
                                <option value="">-- Select Layer --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ $feature->shapefile->category_id == $cat->id ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $cat->name)) }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" name="category_id" value="{{ $user->category_id }}">
                            <div class="form-control form-control-sm bg-light mb-2">
                                {{ ucfirst(str_replace('_', ' ', $adminCategory)) }}
                            </div>
                        @endif

                        <select name="classification_id" id="classification_id" class="form-select form-select-sm" required>
                            <option value="">-- Select Feature Type --</option>
                            @foreach ($classifications as $c)
                                <option value="{{ $c->id }}" data-color="{{ $c->color }}"
                                    {{ old('classification_id', $feature->classification_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- FEATURE INFO -->
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">Feature Attributes</h6>
                        <label class="form-label small mb-1">Date Collected</label>
                        <input type="date" name="survey_date" class="form-control form-control-sm mb-2" 
                               value="{{ old('survey_date', $feature->survey_date) }}" required>

                        <label class="form-label small mb-1">Reference Location</label>
                        <input type="text" name="location" class="form-control form-control-sm mb-2"
                               value="{{ old('location', $feature->location) }}" required>

                        <label class="form-label small mb-1">Description</label>
                        <textarea name="description" rows="3" class="form-control form-control-sm" required>{{ old('description', $feature->description) }}</textarea>
                    </div>
                </div>

                <!-- METADATA BUTTONS ONLY -->
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-2">Metadata</h6>
                        <div class="d-flex gap-2">
                            <button type="button" id="add-metadata-btn" class="btn btn-outline-danger btn-sm">
                                Add Metadata
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SUBMIT -->
                <button type="submit" class="btn btn-danger rounded-3 py-2 w-100 mt-auto">
                    Update Shapefile
                </button>

            </div>
        </div>
    </form>
</div>

<!-- MODAL -->
<div class="modal fade" id="metadataModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header">
                <h5 class="modal-title">Manage Attributes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-metadata-container"></div>
                <button type="button" id="modal-add-meta" class="btn btn-outline-danger btn-sm mt-2">+ Add Attribute</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" id="save-metadata" class="btn btn-danger">Save Attributes</button>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    /* ================= MAP ================= */
    const classificationSelect = document.getElementById('classification_id');
    const geometryInput = document.getElementById('geometry');
    let selectedColor = classificationSelect.selectedOptions[0]?.dataset.color || '#3388ff';

    classificationSelect.addEventListener('change', () => {
        selectedColor = classificationSelect.selectedOptions[0]?.dataset.color || '#3388ff';
        drawnItems.eachLayer(layer => layer.setStyle({ color: selectedColor }));
        saveGeometry();
    });

    const map = L.map('map', { center: [14.28, 121.40], zoom: 10 });
    const drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    const drawControl = new L.Control.Draw({
        edit: { featureGroup: drawnItems },
        draw: { polygon: true, polyline: false, rectangle: false, circle: false, marker: false }
    });
    map.addControl(drawControl);

    // Load existing polygon
    let geoJson = {!! $geoJson ? json_encode($geoJson) : 'null' !!};
    if (geoJson) {
        const layer = L.geoJSON(geoJson, { style: { color: selectedColor } }).getLayers()[0];
        drawnItems.addLayer(layer);
        map.fitBounds(layer.getBounds());
        saveGeometry();
    }

    function saveGeometry() {
        const features = drawnItems.getLayers().map(layer => ({
            type: 'Feature',
            geometry: layer.toGeoJSON().geometry,
            properties: { classification_id: classificationSelect.value }
        }));
        geometryInput.value = JSON.stringify({ type: 'FeatureCollection', features });
    }

    map.on(L.Draw.Event.CREATED, e => {
        drawnItems.clearLayers();
        e.layer.setStyle({ color: selectedColor });
        drawnItems.addLayer(e.layer);
        saveGeometry();
    });
    map.on(L.Draw.Event.EDITED, saveGeometry);
    map.on(L.Draw.Event.DELETED, () => geometryInput.value = '');

    // ================= METADATA =================
    // Map existing Eloquent models to plain objects with key/value
    let metadata = Array.isArray(@json($feature->metadata)) 
        ? @json($feature->metadata).map(m => ({ key: m.meta_key, value: m.meta_value }))
        : [];

    const modalEl = document.getElementById('metadataModal');
    const formContainer = document.getElementById('metadata-form-container');

    document.getElementById('add-metadata-btn').addEventListener('click', () => {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
        renderModal();
    });

    function renderModal() {
        const container = document.getElementById('modal-metadata-container');
        container.innerHTML = '';
        metadata.forEach((m,i) => {
            container.innerHTML += `
            <div class="row mb-2 align-items-center">
                <div class="col-md-5">
                    <input class="form-control form-control-sm modal-key" value="${m.key || ''}" placeholder="Key">
                </div>
                <div class="col-md-5">
                    <input class="form-control form-control-sm modal-value" value="${m.value || ''}" placeholder="Value">
                </div>
                <div class="col-md-2 text-end">
                    <button class="btn btn-danger btn-sm remove-meta" data-index="${i}">✕</button>
                </div>
            </div>`;
        });
    }

    document.getElementById('modal-add-meta').onclick = () => {
        metadata.push({ key:'', value:'' });
        renderModal();
    };

    document.addEventListener('click', e => {
        if (e.target.classList.contains('remove-meta')) {
            metadata.splice(e.target.dataset.index,1);
            renderModal();
        }
    });

    document.getElementById('save-metadata').onclick = () => {
        const keys = document.querySelectorAll('.modal-key');
        const values = document.querySelectorAll('.modal-value');
        metadata = Array.from(keys).map((k,i)=>({ key:k.value, value:values[i].value }));

        formContainer.innerHTML = '';
        metadata.forEach((m,i)=>{
            const kInput = document.createElement('input');
            kInput.type='hidden'; kInput.name=`metadata[${i}][key]`; kInput.value=m.key;
            const vInput = document.createElement('input');
            vInput.type='hidden'; vInput.name=`metadata[${i}][value]`; vInput.value=m.value;
            formContainer.appendChild(kInput);
            formContainer.appendChild(vInput);
        });

        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
    };

});
</script>
@endpush
@endsection