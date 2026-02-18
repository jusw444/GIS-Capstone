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

    // Total shapefiles (including trashed)
    $totalShapefiles = Shapefile::withTrashed()->count();

    // Category counts (GLOBAL, not paginated)
    $categoryCounts = Shapefile::selectRaw('category, COUNT(*) as total')
        ->groupBy('category')
        ->pluck('total', 'category');

    // Paginated shapefiles with user, features, and feature metadata
    $shapefiles = Shapefile::withTrashed()
        ->with([
            'user:id,name',
            'features.metadata'  // Load features and their metadata
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
            'pageName'  => 'Laguna GIS Viewer',
        ];

        $category = $request->category;

        $query = Shapefile::with('features.metadata')
                ->select('id', 'category')
                ->with(['features' => function ($q) {
                    $q->select('id', 'shapefile_id', 'feature_no', DB::raw('ST_AsGeoJSON(geometry) as geometry'));
                }]);

        if ($category) {
            $query->where('category', $category);
        }

        $shapefiles = $query->get();

        // Convert to GeoJSON-like structure
        $geojson = $shapefiles->flatMap(function ($shapefile) {
            return $shapefile->features->map(function ($feature) use ($shapefile) {
                return [
                    'shapefile_id' => $shapefile->id,
                    'category'     => $shapefile->category,
                    'geometry' => json_decode($feature->geometry), // convert GeoJSON string to JS object
                    'metadata'     => $feature->metadata->map(fn($m) => [
                        'meta_key'   => $m->meta_key,
                        'meta_value' => $m->meta_value,
                    ]),
                ];
            });
        });

        return view('admin.map', compact('geojson', 'category', 'page'));
    }



    public function create()
    {

        $page = [
            'pageTitle' => 'Create Shapefile',
            'pageName' => 'Create Shapefile',
        ];

        $categories = User::select('category')
                            ->distinct()
                            ->pluck('category');

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
                'user_id'  => auth()->id(),
            ]);

            $geoArray = json_decode($request->geometry, true);

            if (isset($geoArray['features'])) {

                foreach ($geoArray['features'] as $index => $feature) {

                   $featureModel = $shapefile->features()->create([
                        'geometry'   => DB::raw("ST_GeomFromGeoJSON('" . addslashes(json_encode($feature['geometry'])) . "')"),
                        'feature_no' => $index,
                    ]);

                    if (isset($feature['properties'])) {
                        foreach ($feature['properties'] as $key => $value) {
                            $featureModel->metadata()->create([
                                'meta_key'   => $key,
                                'meta_value' => $value,
                            ]);
                        }
                    }
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
        ]);

        DB::transaction(function () use ($request, $id) {

            $shapefile = Shapefile::findOrFail($id);

            // Decode incoming GeoJSON
            $geoArray = json_decode($request->geometry, true);

            if (!$geoArray || !isset($geoArray['type'])) {
                throw new \Exception("Invalid GeoJSON format.");
            }

            // Handle single geometry (Polygon / MultiPolygon)
            if (in_array($geoArray['type'], ['Polygon', 'MultiPolygon'])) {
                $geoArray = [
                    'type' => 'FeatureCollection',
                    'features' => [
                        [
                            'type' => 'Feature',
                            'geometry' => $geoArray,
                            'properties' => []
                        ]
                    ]
                ];
            }

            if ($geoArray['type'] !== 'FeatureCollection' || empty($geoArray['features'])) {
                throw new \Exception("GeoJSON must be a FeatureCollection with features.");
            }

            // 1️⃣ Update category
            $shapefile->update([
                'category' => $request->category,
            ]);

            // 2️⃣ Delete old features (metadata will delete if cascade is set)
            $shapefile->features()->delete();

            // 3️⃣ Insert new features
            foreach ($geoArray['features'] as $index => $feature) {

                if (!isset($feature['geometry'])) {
                    continue;
                }

               $featureModel = $shapefile->features()->create([
                    'geometry'   => DB::raw("ST_GeomFromGeoJSON('" . addslashes(json_encode($feature['geometry'])) . "')"),
                    'feature_no' => $index,
                ]);


                // 4️⃣ Save metadata for this feature
                if (isset($feature['properties']) && is_array($feature['properties'])) {
                    foreach ($feature['properties'] as $key => $value) {
                        $featureModel->metadata()->create([
                            'meta_key'   => $key,
                            'meta_value' => is_scalar($value) ? $value : json_encode($value),
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Shapefile updated successfully.');
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
            'file'     => 'required|file|mimes:zip|max:30720', // Accept ZIP file, max 20MB
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
        $geoFile = glob($extractPath . '/*.json')[0] ?? null;
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
        DB::transaction(function () use ($geoArray, $request) {

            // 1️⃣ Create shapefile
            $shapefile = Shapefile::create([
                'category' => $request->category,
                'user_id'  => auth()->id(),
            ]);

            if (isset($geoArray['features'])) {

                foreach ($geoArray['features'] as $index => $feature) {

                    // 2️⃣ Save each feature (ONE ROW PER FEATURE)
                    $featureModel = $shapefile->features()->create([
                        'geometry'   => DB::raw("ST_GeomFromGeoJSON('" . addslashes(json_encode($feature['geometry'])) . "')"),
                        'feature_no' => $index,
                    ]);


                    // 3️⃣ Save metadata for that feature
                    if (isset($feature['properties'])) {
                        foreach ($feature['properties'] as $key => $value) {
                            $featureModel->metadata()->create([
                                'meta_key'   => $key,
                                'meta_value' => $value,
                            ]);
                        }
                    }
                }
            }
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
