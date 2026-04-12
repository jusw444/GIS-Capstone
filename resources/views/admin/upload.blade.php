@extends('layouts.app')

@section('page_title', $page['pageTitle'])

@section('content')
    <div class="container">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold" style="color:#b71c1c;">{{ $page['pageName'] }}</h3>
                <p class="text-muted mb-0">Upload GeoJSON or CSV with default location data</p>
            </div>
        </div>

        <!-- Alerts -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Upload Card -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('admin.geojson.store') }}" method="POST" enctype="multipart/form-data" id="upload-form">
                    @csrf

                    <!-- Use Default Location Toggle -->
                    <div class="mb-4">
                        <div class="card border">
                            <div class="card-body bg-light">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="use-default-location">
                                    <label class="form-check-label fw-bold" for="use-default-location">
                                        Use Existing Default Location
                                    </label>
                                </div>
                                <small class="text-muted">Check this to upload CSV with location reference instead of geometry files</small>
                            </div>
                        </div>
                    </div>

                    
                    @if(auth()->user()->role === 'super_admin')
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ ucfirst(str_replace('_', ' ', $cat->name)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="category_id" value="{{ auth()->user()->category_id }}">
                    @endif

                    <!-- Classification -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Classification</label>
                        <select name="classification_id" id="classification_id" class="form-select" required>
                            <option value="">-- Select Classification --</option>
                            @foreach ($classifications as $c)
                                <option value="{{ $c->id }}" data-color="{{ $c->color }}">
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Survey Date -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date Collected</label>
                        <input type="date" name="survey_date" class="form-control" required>
                    </div>

                    <!-- Manual Location Section -->
                    <div id="manual-location-section">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">District</label>
                                <select name="district" id="district" class="form-select">
                                    <option value="">-- Select District --</option>
                                    @foreach ($defaultDistricts as $d)
                                        <option value="{{ $d }}">{{ $d }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Municipality/City</label>
                                <select id="municity" name="municity" class="form-select">
                                    <option value="">Select Municipality/City</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Barangay <span class="text-muted">(Optional)</span></label>
                                <select id="brgy" name="brgy" class="form-select">
                                    <option value="">All Barangays (Optional)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Default Location Section (Hidden by default) -->
                    <div id="default-location-section" style="display: none;">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">District</label>
                                <select id="default-district" class="form-select">
                                    <option value="">-- Select District --</option>
                                    @foreach($defaultDistricts as $d)
                                        <option value="{{ $d }}">{{ $d }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Municipality/City</label>
                                <select id="default-municity" class="form-select" disabled>
                                    <option value="">Select Municipality/City</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Barangay <span class="text-muted">(Optional)</span></label>
                                <select id="default-brgy" class="form-select" disabled>
                                    <option value="">All Barangays (Optional)</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="default_district" id="default-district-input">
                        <input type="hidden" name="default_municity" id="default-municity-input">
                        <input type="hidden" name="default_brgy" id="default-brgy-input">
                        <input type="hidden" name="default_location_id" id="default-location-id-input">
                    </div>

                    <!-- Description -->
                    <div class="mb-3 mt-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Enter description..." required></textarea>
                    </div>

                    <!-- Visibility -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Visibility</label>
                        <select name="visibility" id="visibility" class="form-select" required>
                            <option value="public">Public</option>
                            <option value="private">Private</option>
                        </select>
                    </div>

                    <!-- File Upload Section -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold" id="file-label">ZIP File (GeoJSON)</label>
                        <div id="file-input-container">
                            <input type="file" name="file" id="file-input" class="form-control" accept=".zip" required>
                            <small class="text-muted" id="file-hint">Upload a valid .zip file containing .json file inside</small>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        <button type="submit" class="btn btn-danger" id="submit-btn">Upload GeoJSON</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Default locations data from server
            const defaultLocationsData = @json($defaultLoc ?? []);
            
            // DOM Elements
            const useDefaultCheckbox = document.getElementById('use-default-location');
            const manualLocationSection = document.getElementById('manual-location-section');
            const defaultLocationSection = document.getElementById('default-location-section');
            const fileInput = document.getElementById('file-input');
            const fileLabel = document.getElementById('file-label');
            const fileHint = document.getElementById('file-hint');
            const submitBtn = document.getElementById('submit-btn');
            
            // Default location dropdowns
            const defaultDistrict = document.getElementById('default-district');
            const defaultMunicity = document.getElementById('default-municity');
            const defaultBrgy = document.getElementById('default-brgy');
            const defaultLocationIdInput = document.getElementById('default-location-id-input');
            const defaultDistrictInput = document.getElementById('default-district-input');
            const defaultMunicityInput = document.getElementById('default-municity-input');
            const defaultBrgyInput = document.getElementById('default-brgy-input');
            
            // Toggle between manual and default location
            useDefaultCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    // Switch to default location mode
                    manualLocationSection.style.display = 'none';
                    defaultLocationSection.style.display = 'block';
                    
                    // Change file input to accept CSV
                    fileInput.accept = '.csv';
                    fileInput.placeholder = 'Select CSV file';
                    fileLabel.textContent = 'CSV File (Metadata Only)';
                    fileHint.textContent = 'Upload a CSV file with metadata (geometry will come from selected location)';
                    submitBtn.textContent = 'Upload CSV';
                    
                    // Remove required from manual fields
                    document.getElementById('district').removeAttribute('required');
                    document.getElementById('municity').removeAttribute('required');
                    
                } else {
                    // Switch to manual location mode
                    manualLocationSection.style.display = 'block';
                    defaultLocationSection.style.display = 'none';
                    
                    // Change file input back to ZIP
                    fileInput.accept = '.zip';
                    fileLabel.textContent = 'ZIP File (GeoJSON)';
                    fileHint.textContent = 'Upload a valid .zip file containing .json file inside';
                    submitBtn.textContent = 'Upload GeoJSON';
                    
                    // Add required back to manual fields
                    document.getElementById('district').setAttribute('required', 'required');
                    document.getElementById('municity').setAttribute('required', 'required');
                    
                    // Reset default location fields
                    defaultDistrict.value = '';
                    defaultMunicity.value = '';
                    defaultMunicity.disabled = true;
                    defaultBrgy.value = '';
                    defaultBrgy.disabled = true;
                    defaultLocationIdInput.value = '';
                    defaultDistrictInput.value = '';
                    defaultMunicityInput.value = '';
                    defaultBrgyInput.value = '';
                }
            });
            
            // Function to set default location ID and hidden inputs
            function setDefaultLocationId() {
                const district = defaultDistrict.value;
                const municity = defaultMunicity.value;
                const brgy = defaultBrgy.value;
                
                // Set the hidden inputs
                defaultDistrictInput.value = district;
                defaultMunicityInput.value = municity;
                defaultBrgyInput.value = brgy;
                
                if (!district || !municity) {
                    defaultLocationIdInput.value = '';
                    return;
                }
                
                if (defaultLocationsData.length > 0) {
                    let matchedLocation;
                    
                    if (brgy) {
                        // Specific barangay selected
                        matchedLocation = defaultLocationsData.find(loc => 
                            loc.district === district && 
                            loc.municity === municity && 
                            loc.brgy === brgy
                        );
                    } else {
                        // No barangay selected - use first matching municipality
                        matchedLocation = defaultLocationsData.find(loc => 
                            loc.district === district && 
                            loc.municity === municity
                        );
                    }
                    
                    if (matchedLocation) {
                        defaultLocationIdInput.value = matchedLocation.id;
                    } else {
                        defaultLocationIdInput.value = '';
                        console.warn('No matching location found for:', { district, municity, brgy });
                    }
                }
            }
            
            // Default location cascading dropdowns
            defaultDistrict.addEventListener('change', function() {
                const district = this.value;
                defaultMunicity.value = '';
                defaultMunicity.disabled = !district;
                defaultBrgy.value = '';
                defaultBrgy.disabled = true;
                
                // Update hidden inputs
                defaultDistrictInput.value = district;
                defaultMunicityInput.value = '';
                defaultBrgyInput.value = '';
                defaultLocationIdInput.value = '';
                
                if (district && defaultLocationsData.length > 0) {
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
                }
            });
            
            defaultMunicity.addEventListener('change', function() {
                const district = defaultDistrict.value;
                const municity = this.value;
                
                defaultBrgy.value = '';
                defaultBrgy.disabled = !municity;
                
                if (district && municity && defaultLocationsData.length > 0) {
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
                    
                    // SET LOCATION ID IMMEDIATELY at municipality level
                    setDefaultLocationId();
                } else {
                    defaultLocationIdInput.value = '';
                    defaultMunicityInput.value = municity;
                    defaultBrgyInput.value = '';
                }
            });
            
            defaultBrgy.addEventListener('change', function() {
                // Update location ID when barangay changes
                setDefaultLocationId();
            });
            
            // Manual location dropdowns
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
            
            // Form validation
            document.getElementById('upload-form').addEventListener('submit', function(e) {
                if (useDefaultCheckbox.checked) {
                    // Validate default location is selected
                    if (!defaultLocationIdInput.value) {
                        e.preventDefault();
                        alert('Please select a default location (District and Municipality required)');
                        return false;
                    }
                    
                    // Validate CSV file
                    const fileName = fileInput.value;
                    if (!fileName.toLowerCase().endsWith('.csv')) {
                        e.preventDefault();
                        alert('Please select a CSV file');
                        return false;
                    }
                } else {
                    // Validate manual location
                    const district = document.getElementById('district').value;
                    const municity = document.getElementById('municity').value;
                    
                    if (!district || !municity) {
                        e.preventDefault();
                        alert('Please select district and municipality/city');
                        return false;
                    }
                    
                    // Validate ZIP file
                    const fileName = fileInput.value;
                    if (!fileName.toLowerCase().endsWith('.zip')) {
                        e.preventDefault();
                        alert('Please select a ZIP file');
                        return false;
                    }
                }
            });
        });
    </script>
@endsection