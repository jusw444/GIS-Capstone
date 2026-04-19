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

            <!-- Dynamic metadata inputs for Laravel array -->
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

                            <select name="classification_id" id="classification_id" class="form-select form-select-sm" required>
                                <option value="">-- Select Feature Type --</option>
                                @foreach ($classifications as $c)
                                    <option value="{{ $c->id }}" data-color="{{ $c->color }}">
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- DEFAULT LOCATION TOGGLE -->
                    <div class="card border-0 shadow-sm rounded-4 mb-3">
                        <div class="card-body p-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="use-default-location">
                                <label class="form-check-label fw-bold" for="use-default-location">
                                    Use Existing Default Location
                                </label>
                            </div>
                            
                            <!-- Default Location Cascading Dropdowns (hidden by default) -->
                            <div id="default-location-selector" style="display:none;" class="mt-3">
                                <label class="form-label fw-semibold">Select Default Location</label>
                                
                                <!-- District -->
                                <div class="mb-2">
                                    <label class="form-label small mb-1">District</label>
                                    <select id="default-district" class="form-select form-select-sm">
                                        <option value="">-- Select District --</option>
                                        @foreach($defaultDistricts as $d)
                                            <option value="{{ $d }}">{{ $d }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Municipality/City -->
                                <div class="mb-2">
                                    <label class="form-label small mb-1">Municipality/City</label>
                                    <select id="default-municity" class="form-select form-select-sm" disabled>
                                        <option value="">Select Municipality/City</option>
                                    </select>
                                </div>
                                
                                <!-- Barangay (OPTIONAL) -->
                                <div class="mb-2">
                                    <label class="form-label small mb-1">Barangay <span class="text-muted">(Optional)</span></label>
                                    <select id="default-brgy" class="form-select form-select-sm" disabled>
                                        <option value="">All Barangays (Optional)</option>
                                    </select>
                                </div>
                                
                                <!-- Hidden input for default_location_id -->
                                <input type="hidden" name="default_location_id" id="default-location-id-input">
                            </div>
                        </div>
                    </div>

                    <!-- FEATURE INFO -->
                    <div class="card border-0 shadow-sm rounded-4 mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold mb-2">Feature Attributes</h6>
                            
                            <!-- Survey Date -->
                            <label class="form-label small mb-1">Date Collected</label>
                            <input type="date" name="survey_date" class="form-control form-control-sm mb-2" required>
                            
                            <!-- Manual Location Section -->
                            <div id="manual-location-section">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold mt-2">District</label>
                                        <select name="district" id="district" class="form-select" required>
                                            <option value="">--Select District--</option>
                                            @foreach ($district as $d)
                                                <option value="{{ $d }}">{{ $d }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <label class="form-label fw-semibold mt-2">Municipality/City</label>
                                        <select id="municity" name="municity" class="form-select" required>
                                            <option value="">Select Municipality/City</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <label class="form-label fw-semibold mt-2">Barangay <span class="text-muted">(Optional)</span></label>
                                <select id="brgy" name="brgy" class="form-select">
                                    <option value="">All Barangays (Optional)</option>
                                </select>
                            </div>
                            
                            <!-- Description -->
                            <label class="form-label small mb-1 mt-2">Description</label>
                            <textarea name="description" rows="3" class="form-control form-control-sm mb-2" placeholder="Enter description..." required></textarea>
                            
                            <!-- Visibility -->
                            <label for="visibility" class="fw-bold mt-2">Visibility:</label>
                            <select name="visibility" id="visibility" class="form-select" required>
                                <option value="public">Public</option>
                                <option value="private">Private</option>
                            </select>
                        </div>
                    </div>

                    <!-- METADATA -->
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
                        Save Spatial Data
                    </button>

                </div>
            </div>
        </form>
    </div>

    <!-- METADATA MODAL -->
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
    <script src="https://cdn.jsdelivr.net/npm/@turf/turf@6.5.0/turf.min.js"></script>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
    
                /* ================= MAP SETUP ================= */
                const classificationSelect = document.getElementById('classification_id');
                const geometryInput = document.getElementById('geometry');
                
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
                    edit: { featureGroup: drawnItems },
                    draw: {
                        polygon: true,
                        polyline: false,
                        rectangle: false,
                        circle: false,
                        marker: false
                    }
                });
                map.addControl(drawControl);

                // Save drawn geometry
                function saveGeometry(layer) {
                    geometryInput.value = JSON.stringify({
                        type: 'FeatureCollection',
                        features: [{
                            type: 'Feature',
                            geometry: layer.toGeoJSON().geometry,
                            properties: {
                                classification_id: classificationSelect.value,
                                color: getSelectedColor(),
                                from_default_location: false
                            }
                        }]
                    });
                }

                /* ================= DEFAULT LOCATION TOGGLE ================= */
                const useDefaultCheckbox = document.getElementById('use-default-location');
                const defaultLocationSelector = document.getElementById('default-location-selector');
                const manualLocationSection = document.getElementById('manual-location-section');
                const defaultDistrict = document.getElementById('default-district');
                const defaultMunicity = document.getElementById('default-municity');
                const defaultBrgy = document.getElementById('default-brgy');
                const defaultLocationIdInput = document.getElementById('default-location-id-input');
                
                let hasDrawnPolygon = false;
                let currentDefaultLayer = null;
                const defaultLocationsData = @json($defaultLoc);

                function toggleDrawingControls(enable) {
                    if (enable) {
                        map.addControl(drawControl);
                        drawnItems.eachLayer(layer => {
                            if (layer.editing) layer.editing.enable();
                        });
                    } else {
                        map.removeControl(drawControl);
                        drawnItems.eachLayer(layer => {
                            if (layer.editing) layer.editing.disable();
                        });
                    }
                }

                function clearDrawnPolygon() {
                    drawnItems.clearLayers();
                    hasDrawnPolygon = false;
                }

                function clearDefaultPolygon() {
                    if (currentDefaultLayer) {
                        map.removeLayer(currentDefaultLayer);
                        currentDefaultLayer = null;
                    }
                }

                function saveDefaultGeometry(geometry, locationId) {
                    geometryInput.value = JSON.stringify({
                        type: 'FeatureCollection',
                        features: [{
                            type: 'Feature',
                            geometry: geometry,
                            properties: {
                                classification_id: classificationSelect.value,
                                color: getSelectedColor(),
                                from_default_location: true,
                                default_location_id: locationId
                            }
                        }]
                    });
                }

                
                // Load default location polygon when district and municity are selected (barangay optional)
                function loadDefaultLocationPolygon() {
                    const district = defaultDistrict.value;
                    const municity = defaultMunicity.value;
                    const brgy = defaultBrgy.value;
                    
                    if (!district || !municity) return;
                    
                    // Find matching locations
                    let matchedLocations = defaultLocationsData.filter(loc => 
                        loc.district === district && 
                        loc.municity === municity
                    );
                    
                    // Filter by barangay if selected
                    if (brgy) {
                        matchedLocations = matchedLocations.filter(loc => loc.brgy === brgy);
                    }
                    
                    if (!matchedLocations || matchedLocations.length === 0) {
                        alert('No geometry found for this location');
                        return;
                    }
                    
                    // Clear any existing default polygon
                    clearDefaultPolygon();
                    
                    // If multiple locations (no barangay selected), merge them into a single boundary
                    if (matchedLocations.length > 1 && !brgy) {
                        // Use Turf.js to merge/dissolve polygons
                        try {
                            // Create a feature collection
                            const featureCollection = {
                                type: 'FeatureCollection',
                                features: matchedLocations.map(loc => ({
                                    type: 'Feature',
                                    geometry: loc.geometry,
                                    properties: {}
                                }))
                            };
                            
                            // Use Turf.js union to merge all polygons
                            let mergedGeometry = null;
                            
                            // Start with the first polygon
                            if (matchedLocations[0].geometry) {
                                mergedGeometry = matchedLocations[0].geometry;
                                
                                // Union with each subsequent polygon
                                for (let i = 1; i < matchedLocations.length; i++) {
                                    if (matchedLocations[i].geometry) {
                                        mergedGeometry = turf.union(
                                            turf.polygon(mergedGeometry.coordinates),
                                            turf.polygon(matchedLocations[i].geometry.coordinates)
                                        ).geometry;
                                    }
                                }
                            }
                            
                            if (mergedGeometry) {
                                currentDefaultLayer = L.geoJSON(mergedGeometry, {
                                    style: {
                                        color: getSelectedColor(),
                                        weight: 2,
                                        fillOpacity: 0.3
                                    }
                                }).addTo(map);
                                
                                // Save merged geometry
                                geometryInput.value = JSON.stringify({
                                    type: 'FeatureCollection',
                                    features: [{
                                        type: 'Feature',
                                        geometry: mergedGeometry,
                                        properties: {
                                            classification_id: classificationSelect.value,
                                            color: getSelectedColor(),
                                            from_default_location: true,
                                            default_location_id: matchedLocations[0].id,
                                            is_municipal_boundary: true
                                        }
                                    }]
                                });
                                
                                // Fit map to bounds
                                map.fitBounds(currentDefaultLayer.getBounds());
                            } else {
                                // Fallback to showing all polygons if merge fails
                                currentDefaultLayer = L.geoJSON(featureCollection, {
                                    style: {
                                        color: getSelectedColor(),
                                        weight: 2,
                                        fillOpacity: 0.3
                                    }
                                }).addTo(map);
                                map.fitBounds(currentDefaultLayer.getBounds());
                            }
                            
                            // Set location ID as first one
                            defaultLocationIdInput.value = matchedLocations[0].id;
                            
                        } catch (error) {
                            console.error('Error merging polygons:', error);
                            
                            // Fallback to showing all polygons
                            const featureCollection = {
                                type: 'FeatureCollection',
                                features: matchedLocations.map(loc => ({
                                    type: 'Feature',
                                    geometry: loc.geometry,
                                    properties: {}
                                }))
                            };
                            
                            currentDefaultLayer = L.geoJSON(featureCollection, {
                                style: {
                                    color: getSelectedColor(),
                                    weight: 2,
                                    fillOpacity: 0.3
                                }
                            }).addTo(map);
                            
                            map.fitBounds(currentDefaultLayer.getBounds());
                            defaultLocationIdInput.value = matchedLocations[0].id;
                        }
                        
                    } else {
                        // Single location or specific barangay selected
                        const matchedLocation = matchedLocations[0];
                        
                        currentDefaultLayer = L.geoJSON(matchedLocation.geometry, {
                            style: {
                                color: getSelectedColor(),
                                weight: 2,
                                fillOpacity: 0.3
                            }
                        }).addTo(map);
                        
                        defaultLocationIdInput.value = matchedLocation.id;
                        saveDefaultGeometry(matchedLocation.geometry, matchedLocation.id);
                        
                        // Fit map to bounds
                        map.fitBounds(currentDefaultLayer.getBounds());
                    }
                }

                // Checkbox toggle
                useDefaultCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        if (hasDrawnPolygon) {
                            const confirmSwitch = confirm('⚠️ You have already drawn a polygon. Switching to default location will delete your drawn polygon. Continue?');
                            if (!confirmSwitch) {
                                this.checked = false;
                                return;
                            }
                            clearDrawnPolygon();
                        }
                        
                        manualLocationSection.style.display = 'none';
                        document.getElementById('district').removeAttribute('required');
                        document.getElementById('municity').removeAttribute('required');
                        
                        defaultLocationSelector.style.display = 'block';
                        toggleDrawingControls(false);
                    } else {
                        manualLocationSection.style.display = 'block';
                        document.getElementById('district').setAttribute('required', 'required');
                        document.getElementById('municity').setAttribute('required', 'required');
                        
                        defaultLocationSelector.style.display = 'none';
                        clearDefaultPolygon();
                        toggleDrawingControls(true);
                        
                        geometryInput.value = '';
                        defaultLocationIdInput.value = '';
                        defaultDistrict.value = '';
                        defaultMunicity.value = '';
                        defaultMunicity.disabled = true;
                        defaultBrgy.value = '';
                        defaultBrgy.disabled = true;
                    }
                });

                // Cascading dropdowns for default location
                defaultDistrict.addEventListener('change', function() {
                    const district = this.value;
                    defaultMunicity.value = '';
                    defaultMunicity.disabled = !district;
                    defaultBrgy.value = '';
                    defaultBrgy.disabled = true;
                    
                    if (district) {
                        const municities = [...new Set(
                            defaultLocationsData
                                .filter(loc => loc.district === district)
                                .map(loc => loc.municity)
                                .filter(m => m)
                                .sort()
                        )];
                        
                        defaultMunicity.innerHTML = '<option value="">Select Municipality/City</option>';
                        municities.forEach(m => {
                            defaultMunicity.innerHTML += `<option value="${m}">${m}</option>`;
                        });
                        
                        clearDefaultPolygon();
                        geometryInput.value = '';
                    }
                });

                defaultMunicity.addEventListener('change', function() {
                    const district = defaultDistrict.value;
                    const municity = this.value;
                    
                    defaultBrgy.value = '';
                    defaultBrgy.disabled = !municity;
                    
                    if (district && municity) {
                        const barangays = [...new Set(
                            defaultLocationsData
                                .filter(loc => loc.district === district && loc.municity === municity)
                                .map(loc => loc.brgy)
                                .filter(b => b)
                                .sort()
                        )];
                        
                        defaultBrgy.innerHTML = '<option value="">All Barangays (Optional)</option>';
                        barangays.forEach(b => {
                            defaultBrgy.innerHTML += `<option value="${b}">${b}</option>`;
                        });
                        
                        // LOAD POLYGON IMMEDIATELY at municipality level
                        loadDefaultLocationPolygon();
                    }
                });

                defaultBrgy.addEventListener('change', function() {
                    const district = defaultDistrict.value;
                    const municity = defaultMunicity.value;
                    
                    if (district && municity) {
                        loadDefaultLocationPolygon();
                    }
                });

                // Draw event
                map.on(L.Draw.Event.CREATED, e => {
                    drawnItems.clearLayers();
                    const layer = e.layer;
                    layer.setStyle({ color: getSelectedColor() });
                    drawnItems.addLayer(layer);
                    hasDrawnPolygon = true;
                    saveGeometry(layer);
                    
                    if (useDefaultCheckbox.checked) {
                        useDefaultCheckbox.checked = false;
                        manualLocationSection.style.display = 'block';
                        defaultLocationSelector.style.display = 'none';
                        toggleDrawingControls(true);
                        document.getElementById('district').setAttribute('required', 'required');
                        document.getElementById('municity').setAttribute('required', 'required');
                    }
                });

                // Update color on classification change
                classificationSelect.addEventListener('change', () => {
                    const color = getSelectedColor();
                    drawnItems.eachLayer(layer => {
                        layer.setStyle({ color: color });
                        if (hasDrawnPolygon) saveGeometry(layer);
                    });
                    if (currentDefaultLayer) {
                        currentDefaultLayer.setStyle({ color: color });
                        if (defaultLocationIdInput.value) {
                            const matchedLocation = defaultLocationsData.find(loc => loc.id == defaultLocationIdInput.value);
                            if (matchedLocation) saveDefaultGeometry(matchedLocation.geometry, matchedLocation.id);
                        }
                    }
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
                    
                    if (metadata.length === 0) {
                        container.innerHTML = '<p class="text-muted small mb-0">No metadata added yet.</p>';
                        return;
                    }
                    
                    metadata.forEach((m, i) => {
                        container.innerHTML += `
                            <div class="row mb-2 align-items-center">
                                <div class="col-md-5">
                                    <input class="form-control form-control-sm modal-key" value="${escapeHtml(m.key || '')}" placeholder="Key">
                                </div>
                                <div class="col-md-5">
                                    <input class="form-control form-control-sm modal-value" value="${escapeHtml(m.value || '')}" placeholder="Value">
                                </div>
                                <div class="col-md-2 text-end">
                                    <button type="button" class="btn btn-danger btn-sm remove-meta" data-index="${i}">✕</button>
                                </div>
                            </div>
                        `;
                    });
                }

                function escapeHtml(text) {
                    if (!text) return '';
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                }

                function syncModalToArray() {
                    const rows = document.querySelectorAll('#modal-metadata-container .row');
                    metadata = [...rows].map(row => ({
                        key: row.querySelector('.modal-key')?.value.trim() || '',
                        value: row.querySelector('.modal-value')?.value.trim() || ''
                    })).filter(m => m.key !== '');
                }

                function updateHiddenInputs() {
                    const container = document.getElementById('metadata-form-container');
                    container.innerHTML = '';
                    metadata.forEach((m, i) => {
                        container.innerHTML += `
                            <input type="hidden" name="metadata[${i}][key]" value="${escapeHtml(m.key)}">
                            <input type="hidden" name="metadata[${i}][value]" value="${escapeHtml(m.value)}">
                        `;
                    });
                }

                document.getElementById('modal-add-meta').addEventListener('click', () => {
                    syncModalToArray();
                    metadata.push({ key: '', value: '' });
                    renderModalMetadata();
                });

                document.addEventListener('click', e => {
                    if (e.target.classList.contains('remove-meta')) {
                        const index = e.target.dataset.index;
                        syncModalToArray();
                        metadata.splice(index, 1);
                        renderModalMetadata();
                    }
                });

                document.getElementById('save-metadata').addEventListener('click', () => {
                    syncModalToArray();
                    updateHiddenInputs();
                    bootstrap.Modal.getInstance(modalEl).hide();
                });

                /* ================= MANUAL LOCATION DROPDOWNS ================= */
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
                                }
                                document.getElementById('brgy').innerHTML = '<option value="">All Barangays (Optional)</option>';
                            });
                    } else {
                        document.getElementById('municity').innerHTML = '<option value="">Select Municipality/City</option>';
                        document.getElementById('brgy').innerHTML = '<option value="">All Barangays (Optional)</option>';
                    }
                });

                document.getElementById('municity').addEventListener('change', function() {
                    let municity = this.value;
                    if (municity) {
                        fetch(`/admin/get-brgy/${municity}`)
                            .then(res => res.json())
                            .then(data => {
                                let brgyDropdown = document.getElementById('brgy');
                                brgyDropdown.innerHTML = '<option value="">All Barangays (Optional)</option>';
                                if (data && data.length > 0) {
                                    data.forEach(item => {
                                        brgyDropdown.innerHTML += `<option value="${item}">${item}</option>`;
                                    });
                                }
                            });
                    } else {
                        document.getElementById('brgy').innerHTML = '<option value="">All Barangays (Optional)</option>';
                    }
                });

                /* ================= FORM VALIDATION ================= */
                document.getElementById('shapefile-form').addEventListener('submit', function(e) {
                    if (!classificationSelect.value) {
                        e.preventDefault();
                        alert('Please select a feature type');
                        return;
                    }
                    
                    if (!geometryInput.value) {
                        e.preventDefault();
                        alert('Please draw a polygon or select a default location');
                        return;
                    }
                    
                    if (useDefaultCheckbox.checked) {
                        if (!defaultDistrict.value || !defaultMunicity.value) {
                            e.preventDefault();
                            alert('Please select district and municipality/city');
                            return;
                        }
                    } else {
                        const district = document.getElementById('district').value;
                        const municity = document.getElementById('municity').value;
                        
                        if (!district || !municity) {
                            e.preventDefault();
                            alert('Please select district and municipality/city');
                            return;
                        }
                    }
                    
                    updateHiddenInputs();
                });

            });
        </script>
    @endpush
@endsection