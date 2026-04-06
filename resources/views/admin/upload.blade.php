@extends('layouts.app')

@section('page_title', $page['pageTitle'])

@section('content')
    <div class="container">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold" style="color:#b71c1c;">{{ $page['pageName'] }}</h3>
                <p class="text-muted mb-0">Upload GeoJSON or create new GIS shapefiles</p>
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
                <form action="{{ route('admin.geojson.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Category -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category</label>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">
                                {{ ucfirst(str_replace('_', ' ', $cat->name)) }}
                            </option>
                        @endforeach
                        <label class="form-label fw-semibold mt-2">Classification</label>
                        <select name="classification_id" id="classification_id" class="form-select" required>
                            <option value="">--Select Classification--</option>
                            @foreach ($classifications as $c)
                                <option value="{{ $c->id }}" data-color="{{ $c->color }}">
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        <!-- Survey Date -->
                        <label class="form-label fw-semibold mt-2">Date Collected</label>
                        <input type="date" name="survey_date" class="form-control form-control-sm mb-2" required>

                        <label class= "form-label fw-semibold mt-2">District</label>
                        <select name="district" id="district" class="form-select">
                            <option value="">--Select District--</option>
                            @foreach ($district as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                        <label class= "form-label fw-semibold mt-2">Municipality/City</label>
                        <select id="municity" name="municity" class="form-select">
                            <option value="">Select Municipality/City</option>
                        </select>
                        <label class= "form-label fw-semibold mt-2">Barangay</label>
                        <select id="brgy" name="brgy" class="form-select">
                            <option value="">Select Barangay</option>
                        </select>
                        <!-- Description -->
                        <label class="form-label fw-semibold mt-2">Description</label>
                        <textarea name="description" rows="3" class="form-control form-control-sm" placeholder="Enter description..."
                            required></textarea>
                    </div>

                    <!-- File Upload -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">ZIP File</label>
                        <input type="file" name="file" class="form-control" accept=".zip" required>
                        <small class="text-muted">Upload a valid .zip file containing .json file inside</small>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        <button type="submit" class="btn btn-danger">Upload GeoJSON</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('district').addEventListener('change', function() {
            let district = this.value;
            fetch(`/admin/get-municity/${district}`)
                .then(res => res.json())
                .then(data => {
                    let municityDropdown = document.getElementById('municity');
                    municityDropdown.innerHTML = '<option value="">Select Municipality</option>';

                    data.forEach(item => {
                        municityDropdown.innerHTML += `<option value="${item}">${item}</option>`;
                    });

                    // Reset brgy
                    document.getElementById('brgy').innerHTML = '<option>Select Barangay</option>';
                });
        });


        document.getElementById('municity').addEventListener('change', function() {
            let municity = this.value;

            fetch(`/admin/get-brgy/${municity}`)
                .then(res => res.json())
                .then(data => {
                    let brgyDropdown = document.getElementById('brgy');
                    brgyDropdown.innerHTML = '<option value="">Select Barangay</option>';

                    data.forEach(item => {
                        brgyDropdown.innerHTML += `<option value="${item}">${item}</option>`;
                    });
                });
        });
    </script>
@endsection
