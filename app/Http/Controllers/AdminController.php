<?php

namespace App\Http\Controllers;

use App\Models\OfficeModule;
use App\Models\Shapefile;
use App\Models\User;
use App\Models\Category;
use App\Models\Classification;
use App\Models\DefaultLocation;
use App\Models\FeatureModel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            ->latest('updated_at')
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
           $recentActivities = FeatureModel::with([
                'creator',
                'updater',
                'shapefile.category',
                'classification'
            ])
                ->withTrashed()
                ->whereHas('shapefile', function ($q) use ($adminCategoryId) {
                    $q->where('category_id', $adminCategoryId);
                })
                ->where('updated_at', '>=', Carbon::now()->subDays(7))
                ->latest('updated_at')
                ->take(25)
                ->get()
                ->map(function ($feature) {

                    if ($feature->trashed()) {
                        $action = 'Deleted';
                        $user   = $feature->updater ?? $feature->creator;
                        $actionColor = 'red';
                    } elseif ($feature->created_at->eq($feature->updated_at)) {
                        $action = 'Created';
                        $user   = $feature->creator;
                        $actionColor = 'green';
                    } else {
                        $action = 'Updated';
                        $user   = $feature->updater;
                        $actionColor = 'blue';
                    }

                    return (object)[
                        'classification_name' => $feature->classification->name ?? 'No Classification',
                        'classification_color' =>$feature->classification->color ?? '#6c757d' ,
                        'category_name'       => $feature->shapefile->category->name ?? 'No Category',
                        'action'              => $action,
                        'user_name'           => $user->name ?? 'Unknown',
                        'category_color'      => $feature->classification->color ?? '#6c757d',
                        'updated_at'          => $feature->updated_at,
                        'location'            => $feature->location,
                        'action_color'         => $actionColor,
                        'trashed'             => $feature->trashed(),
                        'id'                  => $feature->id,
                        'created_at'          => $feature->survey_date ? Carbon::parse($feature->survey_date)->format('F j, Y') : 'No Date',
                    ];
                });

        return view('admin.dashboard', compact(
            'page',
            'totalUsers',
            'totalShapefiles',
            'categoryCounts',
            'features',
            'adminCategory',
            'recentActivities',
            'totalClassifications',
        ));
    }

    /**
     * Map view
     */
    

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
    $district = DefaultLocation::select('district')->distinct()->orderBy('district','asc')->pluck('district');  

    // Only allow admin category
    $categories = Category::where('id', $adminCategoryId)->get();

    return view('admin.create', compact('page', 'categories', 'classifications','district'));
}

/**
 * Store shapefile
 */
public function store(Request $request)
{
    $adminCategoryId = auth()->user()->category_id;

    // ✅ VALIDATION
    $request->validate([
        'geometry' => 'required|json',
        'classification_id' => 'required|exists:classifications,id',
        'survey_date' => 'required|date',
        'description' => 'required|string',
        'visibility' => 'required|in:public,private',
        'metadata.*.key' => 'required|string',
        'metadata.*.value' => 'nullable|string',
    ]);

    DB::transaction(function () use ($request, $adminCategoryId) {
        $user = auth()->id();

        // CREATE SHAPEFILE
        $shapefile = Shapefile::create([
            'category_id' => $adminCategoryId,
            'user_id'     => $user,
            'created_by'  => $user,
            'visibility'  => $request->visibility,
        ]);
        $location = $request->district . ', ' . $request->municity . ', ' . $request->brgy;
        $geoArray = json_decode($request->geometry, true);

        if (!isset($geoArray['features'])) {
            throw new \Exception("Invalid GeoJSON structure.");
        }

        foreach ($geoArray['features'] as $index => $feature) {
            $featureModel = $shapefile->features()->create([
                'geometry'   => DB::raw(
                    "ST_GeomFromGeoJSON('" . addslashes(json_encode($feature['geometry'])) . "')"
                ),
                'feature_no' => $index,
                'classification_id' => $request->classification_id,
                'survey_date' => $request->survey_date,
                'description' => $request->description,
                'location'    => $location,
                'created_by' => $user,
                'visibility' => $request->visibility,
            ]);

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
    $district = DefaultLocation::select('district')->distinct()->orderBy('district','asc')->pluck('district');
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
    
    return view('admin.edit', compact(
        'feature', 'categories', 'geoJson', 'page', 'adminCategory', 'user', 'classifications','district'
    ));
}

/**
 * Update shapefile
 */
public function update(Request $request, $id)
{
    $feature = FeatureModel::with('shapefile')->findOrFail($id);

    if (!$feature->shapefile || $feature->shapefile->category_id !== auth()->user()->category_id) {
        abort(403, 'Unauthorized action.');
    }

    $request->validate([
        'geometry' => 'required|json',
        'classification_id' => 'required|exists:classifications,id',
        'survey_date' => 'required|date',
        'description' => 'required|string',
        'visibility' => 'required|in:public,private',
        'metadata' => 'nullable|array',
        'metadata.*.key' => 'required|string',
        'metadata.*.value' => 'nullable|string',
    ]);

    DB::transaction(function () use ($request, $feature) {

    $geoArray = json_decode($request->geometry, true);
    $geometry = $geoArray['features'][0]['geometry'];

    $location = $request->district . ', ' . $request->municity . ', ' . $request->brgy;

    $feature->update([
        'geometry' => DB::raw("ST_GeomFromGeoJSON('" . addslashes(json_encode($geometry)) . "')"),
        'classification_id' => $request->classification_id,
        'survey_date' => $request->survey_date,
        'description' => $request->description,
        'location' => $location,
        'visibility' => $request->visibility,
        'updated_by' => auth()->id(),
    ]);

    // Metadata
    if ($request->has('metadata')) {
        $feature->metadata()->delete();

        foreach ($request->metadata as $meta) {
            if (!empty($meta['key'])) {
                $feature->metadata()->create([
                    'meta_key' => $meta['key'],
                    'meta_value' => $meta['value'] ?? null
                ]);
            }
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

    $district = DefaultLocation::select('district')->distinct()->orderBy('district','asc')->pluck('district');  
    $categories = Category::with('classifications')->where('id', $adminCategoryId)->get();
    $classifications = Classification::where('category_id', $adminCategoryId)->get();

    return view('admin.upload', compact('page', 'categories', 'adminCategory', 'classifications','district'));
}

/**
 * Store Office Module (GeoJSON Upload)
 */
public function storeGeoJson(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:zip|max:30720',
        'visibility' => 'required|in:public,private',
    ]);

    $file = $request->file('file');
    $zip = new \ZipArchive;
    if ($zip->open($file->getRealPath()) !== true) {
        return back()->withErrors(['file' => 'Cannot open ZIP file.']);
    }

    $extractPath = storage_path('app/public/geojsons/tmp/' . uniqid());
    mkdir($extractPath, 0777, true);
    $zip->extractTo($extractPath);
    $zip->close();

    $geoFile = glob($extractPath . '/*.json')[0] ?? null;
    if (!$geoFile) {
        return back()->withErrors(['file' => 'No .geojson file found in ZIP.']);
    }

    $contents = file_get_contents($geoFile);
    $geoArray = json_decode($contents, true);

    if (!$geoArray || !isset($geoArray['type'])) {
        return back()->withErrors(['file' => 'Invalid GeoJSON file.']);
    }

    $adminCategoryId = auth()->user()->category_id;

    DB::transaction(function () use ($geoArray, $request, $adminCategoryId) {
        $user = auth()->id();

        $shapefile = Shapefile::create([
            'category_id' => $adminCategoryId,
            'user_id'     => $user,
            'created_by'  => $user,
            'visibility'  => $request->visibility,
        ]);

        $location = $request->district . ', ' . $request->municity . ', ' . $request->brgy;

        if (isset($geoArray['features'])) {
            foreach ($geoArray['features'] as $index => $feature) {
                $featureModel = $shapefile->features()->create([
                    'geometry' => DB::raw("ST_GeomFromGeoJSON('" . addslashes(json_encode($feature['geometry'])) . "')"),
                    'feature_no' => $index,
                    'classification_id' => $request->classification_id,
                    'survey_date' => $request->survey_date,
                    'description' => $request->description,
                    'location' => $location,
                    'created_by' => $user,
                    'visibility' => $request->visibility, 
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

    $this->deleteDirectory($extractPath);

    return redirect()->route('admin.dashboard')->with('success', 'GeoJSON ZIP uploaded successfully!');
}

    /**
     * Delete directory helper
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
        $feature = FeatureModel::findOrFail($id);

        

        $feature->delete();

        return back()->with('success', 'Shapefile deleted successfully.');
    }

    /**
     * Restore shapefile
     */
    public function restore($id)
    {
        $feature = FeatureModel::withTrashed()->findOrFail($id);

        

        $feature->restore();

        return back()->with('success', 'Shapefile restored successfully.');
    }
}

