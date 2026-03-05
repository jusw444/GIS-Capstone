<?php

namespace App\Http\Controllers;

use App\Models\OfficeModule;
use App\Models\Shapefile;
use App\Models\User;
use App\Models\Category;
use App\Models\Classification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Admin dashboard
     */
    public function index()
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
    $totalShapefiles = Shapefile::where('category_id', $adminCategoryId)
        ->withTrashed()
        ->count();

    // Category count (only one category for this admin)
    $categoryCounts = [
        $adminCategoryId => $totalShapefiles
    ];

    // Paginated shapefiles for this category
    $shapefiles = Shapefile::withTrashed()
    ->where('category_id', $adminCategoryId)
    ->with([
        'user:id,name',
        'category:id,name',
        'metadata'
    ])
    ->latest()
    ->paginate(10);

    return view('admin.dashboard', compact(
        'page',
        'totalUsers',
        'totalShapefiles',
        'categoryCounts',
        'shapefiles',
        'adminCategory',
    ));
}

    /**
     * Map view
     */
    public function mapview(Request $request)
{
    // Get all categories
    $categories = Category::all();

    $adminCategory = auth()->user()->category->name ?? null;

    // Get all classifications (optional: you can also fetch per category dynamically)
    $classifications = Classification::all();

    $page = [
        'pageTitle' => 'Shapefile Map View',
        'pageName'  => 'Laguna GIS Viewer',
    ];

    // Load shapefiles with features + metadata for ALL categories and all admins
    $shapefiles = Shapefile::with([
            'category',
            'features.metadata',
        ])
        ->with(['features' => function ($q) {
            $q->select(
                'id',
                'shapefile_id',
                'feature_no',
                DB::raw('ST_AsGeoJSON(geometry) as geometry')
            );
        }])
        ->get()
        ->filter(fn($s) => $s->features->count() > 0);

    // Flatten features into GeoJSON
    $geojson = $shapefiles->flatMap(function ($shapefile) {

        return $shapefile->features->map(function ($feature) use ($shapefile) {

            return [
                'shapefile_id'      => $shapefile->id,
                'category_id'       => $shapefile->category_id,
                'category'          => $shapefile->category->name ?? 'N/A',
                'classification_id' => $shapefile->classification_id,
                'geometry'          => $feature->geometry
                    ? json_decode($feature->geometry)
                    : null,
                'metadata'          => $feature->metadata->map(fn($m) => [
                    'meta_key'   => $m->meta_key,
                    'meta_value' => $m->meta_value,
                ]),
            ];
        });
    })->values();

    return view('admin.map', compact(
        'geojson',
        'page',
        'classifications',
        'categories',
        'adminCategory',
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
        $shapefile = Shapefile::with('metadata')->findOrFail($id);

        // Check admin category
        if ($shapefile->category_id !== auth()->user()->category_id) {
            abort(403, 'Unauthorized action.');
        }

        $page = [
            'pageTitle' => 'Edit Shapefile',
            'pageName'  => 'Edit Shapefile',
        ];

        $categories = Category::where('id', auth()->user()->category_id)->get();

        $geoJson = $shapefile->geometry;

        $user = auth()->user();
        $adminCategory = $user->category->name ?? null;
        $classifications = Classification::where('category_id', auth()->user()->category_id)->get();

        return view('admin.edit', compact('shapefile', 'categories', 'geoJson', 'page', 'adminCategory', 'user', 'classifications'));
    }

    /**
     * Update shapefile
     */
    public function update(Request $request, $id)
{
    $shapefile = Shapefile::findOrFail($id);

    // ✅ Authorization: only allow admin category
    if ($shapefile->category_id !== auth()->user()->category_id) {
        abort(403, 'Unauthorized action.');
    }

    // ✅ Validate input
    $request->validate([
        'geometry' => 'required|json',
        'classification_id' => 'required|exists:classifications,id',
        'metadata' => 'nullable|array',
        'metadata.*.key' => 'required|string',
        'metadata.*.value' => 'nullable|string',
    ]);

    DB::transaction(function () use ($request, $shapefile) {

    $geoArray = json_decode($request->geometry, true);

    $shapefile->update([
    'classification_id' => $request->classification_id
]);

    // Delete old features and their metadata
    foreach ($shapefile->features as $feature) {
        $feature->metadata()->delete();
        $feature->delete();
    }

    // Re-create features
    foreach ($geoArray['features'] as $index => $feature) {
        $featureModel = $shapefile->features()->create([
            'geometry'   => DB::raw("ST_GeomFromGeoJSON('" . addslashes(json_encode($feature['geometry'])) . "')"),
            'feature_no' => $index,
        ]);

        // Attach metadata
        if ($request->filled('metadata')) {
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
        ->with('success', 'Shapefile updated successfully.');
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
        $categories = Category::where('id', $adminCategoryId)->get();
        $classifications = Classification::where('category_id', $adminCategoryId)->get();

        // Only allow admin category
        $categories = Category::where('id', $adminCategoryId)->get();

        return view('admin.upload', compact('page', 'categories', 'classifications', 'adminCategory'));
    }

    /**
     * Store Office Module
     */
    public function storeGeoJson(Request $request)
    {
        $adminCategoryId = auth()->user()->category_id;

        $request->validate([
            'file' => 'required|file',
        ]);

        $file = $request->file('file');
        $filePath = $file->store('office_modules', 'public');

        OfficeModule::create([
            'file'        => $filePath,
            'category_id' => $adminCategoryId,
            'user_id'     => auth()->id(),
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Office Module uploaded successfully.');
    }

    /**
     * Delete shapefile
     */
    public function destroy($id)
    {
        $shapefile = Shapefile::findOrFail($id);

        if ($shapefile->category_id !== auth()->user()->category_id) {
            abort(403, 'Unauthorized action.');
        }

        $shapefile->delete();

        return back()->with('success', 'Shapefile deleted successfully.');
    }

    /**
     * Restore shapefile
     */
    public function restore($id)
    {
        $shapefile = Shapefile::withTrashed()->findOrFail($id);

        if ($shapefile->category_id !== auth()->user()->category_id) {
            abort(403, 'Unauthorized action.');
        }

        $shapefile->restore();

        return back()->with('success', 'Shapefile restored successfully.');
    }
}