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

            <!-- ✅ Dynamic metadata inputs for Laravel array -->
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

                    <!-- CATEGORY -->
                    <div class="card border-0 shadow-sm rounded-4 mb-3">
                        <div class="card-body p-3">

                            <h6 class="fw-bold mb-3">Layer</h6>

                            @if ($user->role === 'super_admin')
                                <select name="category_id" class="form-select form-select-sm mb-2" required>
                                    <option value="">-- Select Layer --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">
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

                            <select name="classification_id" id="classification_id" class="form-select form-select-sm"
                                required>
                                <option value="">-- Select Feature Type --</option>
                                @foreach ($classifications as $c)
                                    <option value="{{ $c->id }}" data-color="{{ $c->color }}">
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
                            
                            <!-- Survey Date -->
                            <label class="form-label small mb-1">Date Collected</label>
                            <input type="date" name="survey_date" class="form-control form-control-sm mb-2" required>
                            
                            <!-- Location -->
                            <div class="row">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold mt-2">District</label>
                                    <select name="district" id="district" class="form-select" required> <!-- ✅ Added required -->
                                        <option value="">--Select District--</option>
                                        @foreach ($district as $d)
                                            <option value="{{ $d }}">{{ $d }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label fw-semibold mt-2">Municipality/City</label>
                                    <select id="municity" name="municity" class="form-select" required> <!-- ✅ Added required -->
                                        <option value="">Select Municipality/City</option>
                                    </select>
                                </div>
                            </div>
                            
                            <label class="form-label fw-semibold mt-2">Barangay</label>
                            <select id="brgy" name="brgy" class="form-select" required> <!-- ✅ Added required -->
                                <option value="">Select Barangay</option>
                            </select>
                            
                            <!-- Description -->
                            <label class="form-label small mb-1">Description</label>
                            <textarea name="description" rows="3" class="form-control form-control-sm mb-2" placeholder="Enter description..."
                                required></textarea>
                            
                            <!-- Visibility -->
                            <label for="visibility" class="fw-bold mt-2">Visibility:</label>
                            <select name="visibility" id="visibility" class="form-select" required> <!-- ✅ Added form-select class -->
                                <option value="public">Public</option>
                                <option value="private">Private</option>
                            </select>
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
                        Save Shapefile
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

                    <button type="button" id="modal-add-meta" class="btn btn-outline-danger btn-sm mt-2">
                        + Add Attribute
                    </button>
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

                function getSelectedColor() {
                    const selectedOption = classificationSelect.options[classificationSelect.selectedIndex];
                    return selectedOption?.dataset.color || '#3388ff';
                }

                const map = L.map('map', {
                    center: [14.28, 121.40],
                    zoom: 10
                });
                const drawnItems = new L.FeatureGroup();
                map.addLayer(drawnItems);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

                const drawControl = new L.Control.Draw({
                    edit: {
                        featureGroup: drawnItems
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

                // Whenever a polygon is drawn
                map.on(L.Draw.Event.CREATED, e => {
                    drawnItems.clearLayers(); // clear previous
                    const layer = e.layer;
                    layer.setStyle({
                        color: getSelectedColor()
                    });
                    drawnItems.addLayer(layer);
                    saveGeometry(layer);
                });

                // Save geometry function
                function saveGeometry(layer) {
                    document.getElementById('geometry').value = JSON.stringify({
                        type: 'FeatureCollection',
                        features: [{
                            type: 'Feature',
                            geometry: layer.toGeoJSON().geometry,
                            properties: {
                                classification_id: classificationSelect.value,
                                color: getSelectedColor()
                            }
                        }]
                    });
                }

                // Update polygon color if classification changes
                classificationSelect.addEventListener('change', () => {
                    const color = getSelectedColor();
                    drawnItems.eachLayer(layer => {
                        layer.setStyle({
                            color: color
                        });
                        saveGeometry(layer);
                    });
                });
                
                /* ================= METADATA ================= */
                let metadata = [];

                const modalEl = document.getElementById('metadataModal');
                const addBtn = document.getElementById('add-metadata-btn');

                addBtn.addEventListener('click', () => {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                });

                modalEl.addEventListener('show.bs.modal', renderModalMetadata);

                function renderModalMetadata() {
                    const container = document.getElementById('modal-metadata-container');
                    container.innerHTML = '';
                    metadata.forEach((m, i) => {
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
                            </div>
                        `;
                    });
                }

                function syncModalToArray() {
                    const rows = document.querySelectorAll('#modal-metadata-container .row');
                    metadata = [...rows].map(row => ({
                        key: row.querySelector('.modal-key').value.trim(),
                        value: row.querySelector('.modal-value').value.trim()
                    }));
                }

                document.getElementById('modal-add-meta').onclick = () => {
                    syncModalToArray();
                    metadata.push({
                        key: '',
                        value: ''
                    });
                    renderModalMetadata();
                };

                document.addEventListener('click', e => {
                    if (e.target.classList.contains('remove-meta')) {
                        const index = e.target.dataset.index;
                        syncModalToArray();
                        metadata.splice(index, 1);
                        renderModalMetadata();
                    }
                });

                document.getElementById('save-metadata').onclick = () => {
                    syncModalToArray();

                    const formContainer = document.getElementById('metadata-form-container');
                    formContainer.innerHTML = '';

                    metadata.forEach((m, i) => {
                        const keyInput = document.createElement('input');
                        keyInput.type = 'hidden';
                        keyInput.name = `metadata[${i}][key]`;
                        keyInput.value = m.key;

                        const valueInput = document.createElement('input');
                        valueInput.type = 'hidden';
                        valueInput.name = `metadata[${i}][value]`;
                        valueInput.value = m.value;

                        formContainer.appendChild(keyInput);
                        formContainer.appendChild(valueInput);
                    });

                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    setTimeout(() => {
                        document.body.classList.remove('modal-open');
                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    }, 300);
                };

                /* ================= FORM VALIDATION ================= */
                document.getElementById('shapefile-form').onsubmit = e => {
                    if (!classificationSelect.value) {
                        e.preventDefault();
                        alert('Please select a feature type');
                        return false;
                    }
                    if (!document.getElementById('geometry').value) {
                        e.preventDefault();
                        alert('Please draw a polygon on the map');
                        return false;
                    }
                    // Location validation (HTML5 required will catch these, but double-check)
                    const district = document.getElementById('district').value;
                    const municity = document.getElementById('municity').value;
                    const brgy = document.getElementById('brgy').value;
                    
                    if (!district || !municity || !brgy) {
                        e.preventDefault();
                        alert('Please select district, municipality/city, and barangay');
                        return false;
                    }
                    
                    return true;
                };

                /* ================= LOCATION DROPDOWNS ================= */
                document.getElementById('district').addEventListener('change', function() {
                    let district = this.value;
                    if (district) {
                        fetch(`/admin/get-municity/${district}`)
                            .then(res => res.json())
                            .then(data => {
                                let municityDropdown = document.getElementById('municity');
                                municityDropdown.innerHTML = '<option value="">Select Municipality/City</option>';
                                
                                if (data && data.length > 0) {
                                    data.forEach(item => {
                                        municityDropdown.innerHTML += `<option value="${item}">${item}</option>`;
                                    });
                                } else {
                                    municityDropdown.innerHTML += '<option value="" disabled>No municipalities found</option>';
                                }
                                
                                // Reset brgy
                                document.getElementById('brgy').innerHTML = '<option value="">Select Barangay</option>';
                            })
                            .catch(error => {
                                console.error('Error fetching municipalities:', error);
                                document.getElementById('municity').innerHTML = '<option value="">Error loading data</option>';
                            });
                    } else {
                        document.getElementById('municity').innerHTML = '<option value="">Select Municipality/City</option>';
                        document.getElementById('brgy').innerHTML = '<option value="">Select Barangay</option>';
                    }
                });

                document.getElementById('municity').addEventListener('change', function() {
                    let municity = this.value;
                    if (municity) {
                        fetch(`/admin/get-brgy/${municity}`)
                            .then(res => res.json())
                            .then(data => {
                                let brgyDropdown = document.getElementById('brgy');
                                brgyDropdown.innerHTML = '<option value="">Select Barangay</option>';
                                
                                if (data && data.length > 0) {
                                    data.forEach(item => {
                                        brgyDropdown.innerHTML += `<option value="${item}">${item}</option>`;
                                    });
                                } else {
                                    brgyDropdown.innerHTML += '<option value="" disabled>No barangays found</option>';
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching barangays:', error);
                                document.getElementById('brgy').innerHTML = '<option value="">Error loading data</option>';
                            });
                    } else {
                        document.getElementById('brgy').innerHTML = '<option value="">Select Barangay</option>';
                    }
                });
            });
        </script>
    @endpush
@endsection