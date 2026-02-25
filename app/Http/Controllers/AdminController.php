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

    $query = Shapefile::with('features.metadata')
                ->select('id', 'category')
                ->with(['features' => function ($q) {
                    $q->select('id', 'shapefile_id', 'feature_no', DB::raw('ST_AsGeoJSON(geometry) as geometry'));
                }]);

        $shapefilesall = $query->get();

        // Convert to GeoJSON-like structure
        $geojson = $shapefiles->flatMap(function ($shapefile) {
            return $shapefile->features->map(function ($feature) use ($shapefile) {
                return [
                    'feature_id'   => $feature->id,
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

    

    return view('admin.dashboard', compact(
        'page',
        'totalUsers',
        'totalShapefiles',
        'categoryCounts',
        'shapefiles',
        'geojson'
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
                    'feature_id'   => $feature->id,
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

        
    }

    public function store(Request $request)
    {
        
    }

    public function edit($id)
{

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
