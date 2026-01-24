@extends('layouts.app')

@section('content')
<div class="container-fluid py-0" style="font-family: 'Nunito', sans-serif;">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0" style="color:#b71c1c;">Welcome, Super Admin!</h1>
        <span>Welcome, <strong>{{ auth()->user()->name }}</strong></span>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm" style="border-left:5px solid #b71c1c; padding:20px;">
                <h5>Total Admins</h5>
                <h3>{{ $totalAdmins ?? 0 }}</h3>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm" style="border-left:5px solid #b71c1c; padding:20px;">
                <h5>Total Users</h5>
                <h3>{{ $totalUsers ?? 0 }}</h3>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm" style="border-left:5px solid #b71c1c; padding:20px;">
                <h5>Total Created Shapefiles</h5>
                <h3>{{ $totalShapefiles ?? 0 }}</h3>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm" style="border-left:5px solid #b71c1c; padding:20px;">
                <h5>Total Uploaded Shapefiles</h5>
                <h3>{{ $totalUploadedShapefiles ?? 0 }}</h3>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card shadow-sm mb-4">
        <div class="card-header text-danger">
            <h5 class="mb-0">Recent Activity</h5>
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                @forelse($recentActivity ?? [] as $activity)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $activity->description }}
                        <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                    </li>
                @empty
                    <li class="list-group-item text-center text-muted">No recent activity.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Map Preview -->
    <div class="card shadow-sm">
        <div class="card-header text-danger">
            <h5 class="mb-0">Map Preview</h5>
        </div>
        <div class="card-body p-0">
            <div id="mapPreview" style="width:100%; height:400px;"></div>
        </div>
    </div>

</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    let shapefiles = @json($geojson);

    let map = L.map('mapPreview', {
        center: [14.28, 121.40],
        zoom: 10,
        minZoom: 9,
        maxZoom: 20
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 20,
    }).addTo(map);

    let allLayers = L.featureGroup();

    shapefiles.forEach(item => {

        if (!item.geometry || !item.geometry.type || !item.geometry.coordinates) {
            console.warn('Invalid geometry for ID:', item.id);
            return;
        }

        let layer = L.geoJSON(item.geometry, {
            style: {
                color: item.category === 'disaster' ? 'red' :
                       item.category === 'health' ? 'green' : 'blue',
                weight: 2,
                fillOpacity: 0.4
            }
        });

        let metaHtml = '';
        if (item.metadata && item.metadata.length > 0) {
            item.metadata.forEach(m => {
                metaHtml += `<strong>${m.meta_key}:</strong> ${m.meta_value}<br>`;
            });
        } else {
            metaHtml = '<em>No metadata available</em>';
        }

        layer.bindPopup(`
            <strong>Category:</strong> ${item.category}<br>
            ${metaHtml}
        `);

        layer.addTo(allLayers);
    });

    allLayers.addTo(map);

    if (allLayers.getLayers().length > 0) {
        map.fitBounds(allLayers.getBounds(), { padding: [20, 20] });
    }
});
</script>
@endpush

<style>
    .card {
        border-radius: 10px;
        background: white;
    }
    .card-header {
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
</style>
@endsection
