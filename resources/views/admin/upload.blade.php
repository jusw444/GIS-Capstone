@extends('layouts.app')

@section('page_title', $page['pageTitle'])

@section('content')
    <div class="container">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold" style="color:#b71c1c;">{{ $page['pageName'] }}</h3>
                <p class="text-muted mb-0">Upload GeoJSON ZIP or CSV with location data</p>
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

                    <!-- File Type Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">File Type</label>
                        <select id="file-type" class="form-select">
                            <option value="csv">CSV File (with location columns)</option>
                            <option value="zip">ZIP File (GeoJSON/KML with manual location)</option>
                        </select>
                    </div>

                    <!-- Manual Location Section (for ZIP files) -->
                    <div id="manual-location-section" style="display: none;">
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

                    <!-- CSV Info Section -->
                    <div id="csv-info-section" class="alert alert-info">
                        <strong>CSV Format Requirements:</strong>
                        <ul class="mb-0 mt-2">
                            <li><code>DistrictName</code> - District name (optional if MunName provided)</li>
                            <li><code>MunName</code> - Municipality/City name (required)</li>
                            <li><code>BrgyName</code> - Barangay name (optional)</li>
                            <li>Other columns will be aggregated automatically</li>
                        </ul>
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

                    <!-- File Upload -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold" id="file-label">CSV File</label>
                        <input type="file" name="file" id="file-input" class="form-control" accept=".csv" required>
                        <small class="text-muted" id="file-hint">Upload a CSV file with DistrictName, MunName, BrgyName columns</small>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        <button type="submit" class="btn btn-danger" id="submit-btn">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            const fileTypeSelect = document.getElementById('file-type');
            const manualLocationSection = document.getElementById('manual-location-section');
            const csvInfoSection = document.getElementById('csv-info-section');
            const fileInput = document.getElementById('file-input');
            const fileLabel = document.getElementById('file-label');
            const fileHint = document.getElementById('file-hint');
            
            // Toggle based on file type selection
            fileTypeSelect.addEventListener('change', function() {
                if (this.value === 'csv') {
                    manualLocationSection.style.display = 'none';
                    csvInfoSection.style.display = 'block';
                    fileInput.accept = '.csv,.txt';
                    fileLabel.textContent = 'CSV File';
                    fileHint.textContent = 'Upload a CSV file with DistrictName, MunName, BrgyName columns';
                    
                    // Remove required from location fields
                    document.getElementById('district')?.removeAttribute('required');
                    document.getElementById('municity')?.removeAttribute('required');
                    
                } else {
                    manualLocationSection.style.display = 'block';
                    csvInfoSection.style.display = 'none';
                    fileInput.accept = '.zip';
                    fileLabel.textContent = 'ZIP File (GeoJSON/KML)';
                    fileHint.textContent = 'Upload a valid .zip file containing .json or .kml file inside';
                    
                    // Add required to location fields
                    document.getElementById('district')?.setAttribute('required', 'required');
                    document.getElementById('municity')?.setAttribute('required', 'required');
                }
            });
            
            // Manual location dropdowns
            document.getElementById('district')?.addEventListener('change', function() {
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

            document.getElementById('municity')?.addEventListener('change', function() {
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
                const fileType = fileTypeSelect.value;
                const fileName = fileInput.value;
                
                if (fileType === 'csv') {
                    if (!fileName.toLowerCase().endsWith('.csv') && !fileName.toLowerCase().endsWith('.txt')) {
                        e.preventDefault();
                        alert('Please select a CSV file');
                        return false;
                    }
                } else {
                    if (!fileName.toLowerCase().endsWith('.zip')) {
                        e.preventDefault();
                        alert('Please select a ZIP file');
                        return false;
                    }
                    
                    const district = document.getElementById('district').value;
                    const municity = document.getElementById('municity').value;
                    
                    if (!district || !municity) {
                        e.preventDefault();
                        alert('Please select district and municipality/city');
                        return false;
                    }
                }
            });
        });
    </script>
@endsection