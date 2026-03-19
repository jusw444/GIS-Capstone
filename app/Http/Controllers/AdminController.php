<?php

namespace App\Http\Controllers;

use App\Models\OfficeModule;
use App\Models\Shapefile;
use App\Models\User;
use App\Models\Category;
use App\Models\Classification;
use App\Models\FeatureModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Admin dashboard
     */
    public function dashboard()
    {
        $adminCategoryId = auth()->user()->category_id;
        $adminCategory = auth()->user()->category->name ?? null;

        $page = [
            'pageTitle' => 'Admin Dashboard',
            'pageName'  => 'Admin Dashboard',
        ];

        // Total users ONLY for this admin category
        $totalUsers = User::where('role', 'user')
            ->where('category_id', $adminCategoryId)
            ->count();

        // Total shapefiles ONLY for this category
        $totalShapefiles = FeatureModel::whereHas('shapefile', function ($q) use ($adminCategoryId) {
            $q->where('category_id', $adminCategoryId);
        })
            ->withTrashed()
            ->count();

        // Category count (only one category for this admin)
        $categoryCounts = Category::count();

        // Total classifications for this category
        $totalClassifications = Classification::where('category_id', $adminCategoryId)->count();

        // Paginated shapefiles for this category
        $perPage = request('perPage', 10);

        $features = FeatureModel::withTrashed(['shapefile.user', 'shapefile.category', 'classification'])
            ->whereHas('shapefile', function ($q) use ($adminCategoryId) {
                $q->where('category_id', $adminCategoryId);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $features->getCollection()->transform(function ($feature) {
            $feature->category_name = $feature->shapefile->category->name ?? 'No Category';
            $feature->classification_name = $feature->classification->name ?? 'No Classification';
            $feature->classification_color = $feature->classification->color ?? '#6c757d';
            $feature->properties = $feature->metadata->mapWithKeys(function ($meta) {
                return [$meta->meta_key => $meta->meta_value];
            });
            return $feature;
        });

        // recent activities (created/updated/deleted features) for this category
        $recentActivities = FeatureModel::with(['shapefile.category', 'classification'])
            ->withTrashed()
            ->whereHas('shapefile', function ($q) use ($adminCategoryId) {
                $q->where('category_id', $adminCategoryId);
            })
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(function ($feature) {
                $action = $feature->trashed() ? 'Deleted' : ($feature->created_at->eq($feature->updated_at) ? 'Created' : 'Updated');

                return (object)[
                    'classification_name' => $feature->classification->name ?? 'No Classification',
                    'category_name'       => $feature->shapefile->category->name ?? 'No Category',
                    'action'              => $action,
                    'category_color'      => $feature->classification->color ?? '#6c757d',
                    'created_at'          => $feature->updated_at,
                ];
            });

        return view('admin.dashboard', compact(
            'page',
            'totalUsers',
            'totalShapefiles',
            'categoryCounts',
            // 'shapefiles',
            'features',
            'adminCategory',
            'recentActivities',
            'totalClassifications',
        ));
    }

    /**
     * Map view
     */
    public function mapview(Request $request)
    {
        $categories = Category::all();
        $classifications = Classification::all();

        $adminCategory = auth()->user()->category->name ?? null;

        $page = [
            'pageTitle' => 'Shapefile Map View',
            'pageName'  => 'Laguna GIS Viewer',
        ];

        // Load shapefiles with features and metadata
        $shapefiles = Shapefile::with([
            'category',
            'features' => function ($q) {
                $q->whereNull('deleted_at') // 🔥 THIS LINE
                    ->select(
                        'id',
                        'shapefile_id',
                        'feature_no',
                        'classification_id',
                        DB::raw('ST_AsGeoJSON(geometry) as geometry')
                    )
                    ->with('metadata', 'classification');
            }
        ])->get()->filter(fn($s) => $s->features->count() > 0);

        // Flatten features into GeoJSON structure
        $geojson = $shapefiles->flatMap(function ($shapefile) {
            return $shapefile->features->map(function ($feature) use ($shapefile) {

                return [
                    'feature_id'        => $feature->id,
                    'shapefile_id'      => $shapefile->id,
                    'category_id'       => $shapefile->category_id,
                    'category'          => $shapefile->category->name ?? 'N/A',
                    'classification_id' => $feature->classification_id,
                    'classification'    => $feature->classification->name ?? 'No Classification',
                    'classification_color' => $feature->classification->color ?? '#6c757d',
                    'geometry' => $feature->geometry ? json_decode($feature->geometry, true) : null,
                    'metadata' => $feature->metadata->map(fn($m) => [
                        'meta_key'   => $m->meta_key,
                        'meta_value' => $m->meta_value
                    ])->values()->toArray(),
                ];
            });
        })->values()->toArray();

        // Count polygons per category
        $categoryCounts = collect($geojson)
            ->groupBy('category')
            ->map(fn($items) => $items->count());

        // Category color from first feature's classification
        $categoryColors = $shapefiles
            ->flatMap(fn($s) => $s->features) // get all features
            ->groupBy(fn($f) => $f->shapefile->category->name ?? 'N/A')
            ->map(fn($features) => $features->first()->classification->color ?? '#dc3545');

        // Build legend
        $categoryLegend = $categories->map(function ($cat) use ($categoryCounts, $categoryColors) {
            return [
                'name'  => $cat->name,
                'count' => $categoryCounts[$cat->name] ?? 0,
                'color' => $categoryColors[$cat->name] ?? '#b71c1c',
            ];
        });

        return view('admin.map', compact(
            'geojson',
            'page',
            'classifications',
            'categories',
            'adminCategory',
            'categoryCounts',
            'categoryColors',
            'categoryLegend'
        ));
    }

    /**
     * Create shapefile
     */
    public function create()
    {
        $page = [
            'pageTitle' => 'Create Shapefile',
            'pageName'  => 'Create Shapefile',
        ];

        $adminCategoryId = auth()->user()->category_id;
        $classifications = Classification::where('category_id', $adminCategoryId)->get();

        // Only allow admin category
        $categories = Category::where('id', $adminCategoryId)->get();

        return view('admin.create', compact('page', 'categories', 'classifications'));
    }

    /**
     * Store shapefile
     */
    public function store(Request $request)
    {
        $adminCategoryId = auth()->user()->category_id;

        $request->validate([
            'geometry' => 'required|json',
            'classification_id' => 'required|exists:classifications,id',
            'metadata.*.key' => 'required|string',
            'metadata.*.value' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $adminCategoryId) {

            $shapefile = Shapefile::create([
                'category_id' => $adminCategoryId,
                'user_id'     => auth()->id(),
                'classification_id' => $request->classification_id,
            ]);

            $geoArray = json_decode($request->geometry, true);

            if (!isset($geoArray['features'])) {
                throw new \Exception("Invalid GeoJSON structure.");
            }

            foreach ($geoArray['features'] as $index => $feature) {

                $featureModel = $shapefile->features()->create([
                    'geometry'   => DB::raw(
                        "ST_GeomFromGeoJSON('" .
                            addslashes(json_encode($feature['geometry'])) .
                            "')"
                    ),
                    'feature_no' => $index,
                    'classification_id' => $request->classification_id, // ✅ FIX
                ]);

                // 🔥 STORE METADATA FROM REQUEST (NOT GEOJSON)
                if ($request->has('metadata')) {
                    foreach ($request->metadata as $meta) {
                        if (!empty($meta['key'])) {
                            $featureModel->metadata()->create([
                                'meta_key'   => $meta['key'],
                                'meta_value' => $meta['value'] ?? null,
                            ]);
                        }
                    }
                }
            }
        });

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Shapefile created successfully.');
    }

    /**
     * Edit shapefile
     */
    public function edit($id)
    {
        $feature = FeatureModel::with('metadata', 'shapefile')->findOrFail($id);

        // Check admin category
        if ($feature->shapefile->category_id !== auth()->user()->category_id) {
            abort(403, 'Unauthorized action.');
        }

        $page = [
            'pageTitle' => 'Edit Shapefile',
            'pageName'  => 'Edit Shapefile',
        ];

        $categories = Category::where('id', auth()->user()->category_id)->get();

        $geometry = DB::selectOne("
        SELECT ST_AsGeoJSON(geometry) as geo
        FROM feature_models
        WHERE id = ?
        ", [$feature->id]);

        $geoJson = $geometry ? json_decode($geometry->geo, true) : null;

        $user = auth()->user();
        $adminCategory = $user->category->name ?? null;
        $classifications = Classification::where('category_id', $feature->shapefile->category_id)->get();

        return view('admin.edit', compact('feature', 'categories', 'geoJson', 'page', 'adminCategory', 'user', 'classifications'));
    }

    /**
     * Update shapefile
     */
    public function update(Request $request, $id)
    {
        $feature = FeatureModel::with('shapefile')->findOrFail($id);

        // Authorization
        if (!$feature->shapefile || $feature->shapefile->category_id !== auth()->user()->category_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'geometry' => 'required|json',
            'classification_id' => 'required|exists:classifications,id',
            'metadata' => 'nullable|array',
            'metadata.*.key' => 'required|string',
            'metadata.*.value' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $feature) {

            $geoArray = json_decode($request->geometry, true);

            $geometry = $geoArray['features'][0]['geometry'];

            // ✅ update ONLY this feature geometry
            $feature->update([
                'geometry' => DB::raw(
                    "ST_GeomFromGeoJSON('" . addslashes(json_encode($geometry)) . "')"
                ),
                'classification_id' => $request->classification_id,
            ]);

            // ✅ update metadata ONLY for this feature
            $feature->metadata()->delete();

            foreach ($request->metadata ?? [] as $meta) {
                if (!empty($meta['key'])) {
                    $feature->metadata()->create([
                        'meta_key' => $meta['key'],
                        'meta_value' => $meta['value'] ?? null
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Polygon updated successfully.');
    }

    /**
     * Upload Office Module
     */
    public function uploadGeoJson()
    {
        $page = [
            'pageTitle' => 'Upload JSON',
            'pageName'  => 'Upload GeoJSON or JSON File',
        ];

        $adminCategoryId = auth()->user()->category_id;

        $adminCategory = auth()->user()->category->name ?? null;

        $categories = Category::with('classifications')
            ->where('id', $adminCategoryId)
            ->get();
        $classifications = Classification::where('category_id', $adminCategoryId)->get();

        return view('admin.upload', compact('page', 'categories', 'adminCategory', 'classifications'));
    }

    /**
     * Store Office Module
     */
    public function storeGeoJson(Request $request)
    {
        $request->validate([

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
        $adminCategoryId = auth()->user()->category_id;
        // 4️⃣ Store in database
        DB::transaction(function () use ($geoArray, $request, $adminCategoryId) {

            // 1️⃣ Create shapefile
            $shapefile = Shapefile::create([
                'category_id' => $adminCategoryId,
                'user_id'  => auth()->id(),
            ]);

            if (isset($geoArray['features'])) {

                foreach ($geoArray['features'] as $index => $feature) {

                    // 2️⃣ Save each feature (ONE ROW PER FEATURE)
                    $featureModel = $shapefile->features()->create([
                        'geometry'   => DB::raw("ST_GeomFromGeoJSON('" . addslashes(json_encode($feature['geometry'])) . "')"),
                        'feature_no' => $index,
                        'classification_id' => $request->classification_id
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
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->deleteDirectory("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }

    /**
     * Delete shapefile
     */
    /**
     * Delete a single polygon (soft delete)
     */
    public function destroy($id)
    {
        $feature = FeatureModel::with('shapefile')->findOrFail($id);

        // Check if the feature belongs to the admin's category
        if ($feature->shapefile->category_id !== auth()->user()->category_id) {
            abort(403, 'Unauthorized action.');
        }

        $feature->delete(); // Soft delete the polygon

        return back()->with('success', 'Polygon deleted successfully.');
    }

    /**
     * Restore a single polygon
     */
    public function restore($id)
    {
        $feature = FeatureModel::withTrashed()->with('shapefile')->findOrFail($id);

        // Check if the feature belongs to the admin's category
        if ($feature->shapefile->category_id !== auth()->user()->category_id) {
            abort(403, 'Unauthorized action.');
        }

        $feature->restore(); // Restore the soft-deleted polygon

        return back()->with('success', 'Polygon restored successfully.');
    }
}
