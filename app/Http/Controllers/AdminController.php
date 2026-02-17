<?php

namespace App\Http\Controllers;

use App\Models\OfficeModule;
use App\Models\Shapefile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
{
    $page = [
        'pageTitle' => 'Admin Dashboard',
        'pageName'  => 'Admin Dashboard',
    ];

    // Total users (non-admin)
    $totalUsers = User::where('role', 'user')->count();

    // Total shapefiles (including archived)
    $totalShapefiles = Shapefile::withTrashed()->count();

    // Category counts (GLOBAL, not paginated)
    $categoryCounts = Shapefile::selectRaw('category, COUNT(*) as total')
        ->groupBy('category')
        ->pluck('total', 'category');

    // Paginated shapefiles (TABLE ONLY)
    $shapefiles = Shapefile::withTrashed()
        ->with([
            'user:id,name',
            'metadata:id,shapefile_id'
        ])
        ->latest()
        ->paginate(10);

    return view('admin.dashboard', compact(
        'page',
        'totalUsers',
        'totalShapefiles',
        'categoryCounts',
        'shapefiles'
    ));
}

    public function mapview(Request $request)
    {
        $page = [
            'pageTitle' => 'Shapefile Map View',
            'pageName' => 'Laguna GIS Viewer',
        ];

        $category = $request->category;

        $query = Shapefile::with('metadata')->select('id', 'category', 'user_id');

        if ($category) {
            $query->where('category', $category);
        }

        $shapefiles = $query->get();

        $geojson = $shapefiles->map(function ($item) {
            return [
                'id' => $item->id,
                'category' => $item->category,
                'metadata' => $item->metadata->map(fn($m) => [
                    'meta_key' => $m->meta_key,
                    'meta_value' => $m->meta_value
                ]),
                'geometry' => $item->geometry,
            ];
        });

        $countJson = $geojson->count();

        return view('admin.map', compact('geojson', 'category', 'page', 'countJson'));
    }

    public function create()
    {

        $page = [
            'pageTitle' => 'Create Shapefile',
            'pageName' => 'Create Shapefile',
        ];

        $categories = ['disaster', 'health', 'land_use'];

        return view('admin.create', compact('page', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'geometry' => 'required|json',
            'category' => 'required|in:disaster,health,land_use',
            'metadata.*.key' => 'required|string',
            'metadata.*.value' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $shapefile = Shapefile::create([
                'category' => $request->category,
                'user_id' => auth()->id(),
            ]);

            // Save geometry
            $shapefile->setGeometryRaw($request->geometry);

            // Save metadata
            if ($request->filled('metadata')) {
                foreach ($request->metadata as $meta) {
                    $shapefile->metadata()->create([
                        'meta_key' => $meta['key'],
                        'meta_value' => $meta['value'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('admin.dashboard')->with('success', 'Shapefile created successfully.');
    }

    public function edit($id)
{

    $page = [
        'pageTitle' => 'Edit Shapefile',
        'pageName'  => 'Edit Shapefile',
    ];

    $shapefile = Shapefile::with('metadata')->findOrFail($id);
    $categories = ['disaster', 'health', 'land_use'];

    // Use geometry as-is (no json_decode)
    $geoJson = $shapefile->geometry;

    return view('admin.edit', compact('shapefile', 'categories', 'geoJson', 'page'));
}

    public function update(Request $request, $id)
{
    $request->validate([
        'geometry' => 'required|json',
        'category' => 'required|in:disaster,health,land_use',
        'metadata.*.key' => 'required|string',
        'metadata.*.value' => 'nullable|string',
    ]);

    DB::transaction(function () use ($request, $id) {
    $shapefile = Shapefile::findOrFail($id);

    $geoArray = json_decode($request->geometry, true);

    // Handle FeatureCollection or single geometry
    if (isset($geoArray['type']) && $geoArray['type'] === 'FeatureCollection') {
        if (empty($geoArray['features'])) {
            throw new \Exception("FeatureCollection is empty.");
        }

        // For simplicity, take the first feature's geometry
        $geometryToStore = $geoArray['features'][0]['geometry'];
        if (!in_array($geometryToStore['type'], ['Polygon', 'MultiPolygon'])) {
            throw new \Exception("Invalid geometry inside FeatureCollection.");
        }

        // Store as FeatureCollection for consistency
        $featureCollection = $geoArray;
    } elseif (isset($geoArray['type']) && in_array($geoArray['type'], ['Polygon', 'MultiPolygon'])) {
        // Wrap single geometry in FeatureCollection
        $featureCollection = [
            'type' => 'FeatureCollection',
            'features' => [
                ['type' => 'Feature', 'geometry' => $geoArray, 'properties' => []]
            ]
        ];
    } else {
        throw new \Exception("Invalid geometry. Must be Polygon, MultiPolygon, or FeatureCollection.");
    }

    // Update category
    $shapefile->update(['category' => $request->category]);

    // Save geometry as FeatureCollection
    $shapefile->setGeometryRaw(json_encode($featureCollection));

    // Update metadata
    $shapefile->metadata()->delete();
    if ($request->filled('metadata')) {
        foreach ($request->metadata as $meta) {
            $shapefile->metadata()->create([
                'meta_key' => $meta['key'],
                'meta_value' => $meta['value'] ?? null,
            ]);
        }
    }
});

    return redirect()->route('admin.dashboard')->with('success', 'Shapefile updated successfully.');
}

    // Upload GeoJSON file
    public function uploadGeoJson()
    {
        $page = [
            'pageTitle' => 'Upload GeoJSON',
            'pageName'  => 'Upload GeoJSON File',
        ];

        return view('admin.upload', compact('page'));
    }

    public function storeGeoJson(Request $request)
    {
        $request->validate([
            'category' => 'required|in:disaster,health,land_use',
            'file'     => 'required|file|mimes:zip|max:20480', // Accept ZIP file, max 20MB
        ]);

        $file = $request->file('file');

        // 1️⃣ Extract the ZIP
        $zip = new \ZipArchive;
        if ($zip->open($file->getRealPath()) !== true) {
            return back()->withErrors(['file' => 'Cannot open ZIP file.']);
        }

        $extractPath = storage_path('app/public/geojsons/tmp/' . uniqid());
        mkdir($extractPath, 0777, true);
        $zip->extractTo($extractPath);
        $zip->close();

        // 2️⃣ Find the .geojson file inside
        $geoFile = glob($extractPath . '/*.geojson')[0] ?? null;
        if (!$geoFile) {
            return back()->withErrors(['file' => 'No .geojson file found in ZIP.']);
        }

        // 3️⃣ Read and parse the GeoJSON
        $contents = file_get_contents($geoFile);
        $geoArray = json_decode($contents, true);
        if (!$geoArray || !isset($geoArray['type'])) {
            return back()->withErrors(['file' => 'Invalid GeoJSON file.']);
        }

        // 4️⃣ Store in database
        DB::transaction(function () use ($geoArray, $request, $file) {

            $shapefile = Shapefile::create([
                'category' => $request->category,
                'user_id'  => auth()->id(),
            ]);

            // Save geometry
            $shapefile->setGeometryRaw(json_encode($geoArray));

            // Save metadata (if features have properties)
            if (isset($geoArray['features'])) {
                foreach ($geoArray['features'] as $feature) {
                    if (!empty($feature['properties'])) {
                        foreach ($feature['properties'] as $key => $value) {
                            $shapefile->metadata()->create([
                                'meta_key'   => $key,
                                'meta_value' => $value,
                            ]);
                        }
                    }
                }
            }

            // Optionally save the original ZIP in OfficeModule
            OfficeModule::create([
                'category'     => $request->category,
                'user_id'      => auth()->id(),
                'shapefile_id' => $shapefile->id,
                'file'         => $file->store('geojsons', 'public'),
            ]);
        });

        // 5️⃣ Cleanup extracted files
        $this->deleteDirectory($extractPath);

        return redirect()->route('admin.dashboard')->with('success', 'GeoJSON ZIP uploaded successfully!');
    }

    /**
     * Helper to recursively delete a directory after processing
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->deleteDirectory("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }

    public function destroy($id)
    {
        $shapefile = Shapefile::findOrFail($id);
        $shapefile->delete();
        return back()->with('success', 'Shapefile deleted successfully.');
    }

    public function restore($id)
    {
        $shapefile = Shapefile::withTrashed()->findOrFail($id);
        $shapefile->restore();
        return back()->with('success', 'Shapefile restored successfully.');
    }
}
