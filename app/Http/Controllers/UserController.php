<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Classification;
use App\Models\DefaultLocation;
use App\Models\Shapefile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $page = [
            'pageTitle' => 'User Dashboard',
            'pageName'  => 'User Dashboard',
        ];

        return view('user.dashboard', compact('page'));
    }

public function mapview(Request $request)
{
    // ── LOAD STATIC DATA ────────────────────────────────────────────
    $categories      = Category::all();
    $classifications = Classification::all();

    // ── DEFAULT LOCATION — always load ALL ──────────────────────────
    $defaultLocRaw = DefaultLocation::select(
        'id',
        'district',
        'municity',
        'brgy',
        DB::raw('ST_AsGeoJSON(geometry) as geometry')
    )->get();

    $defaultLoc = $defaultLocRaw->map(function ($loc) {
        return [
            'id'       => $loc->id,
            'district' => $loc->district ?? '',
            'municity' => $loc->municity ?? '',
            'brgy'     => $loc->brgy     ?? '',
            'geometry' => $loc->geometry
                ? json_decode($loc->geometry, true)
                : null,
        ];
    });

    // ── PAGE INFO ───────────────────────────────────────────────────
    $adminCategory = auth()->user()->category->name ?? null;

    $page = [
        'pageTitle' => 'Shapefile Map View',
        'pageName'  => 'GIS Map Viewer',
    ];

    // ── FEATURE DATA — ALWAYS LOAD ALL FEATURES (no filtering) ──────
    // Remove the WHERE clause that filters by locationIds
    $shapefiles = Shapefile::with([
        'category',
        'features' => function ($q) {
            $q->whereNull('deleted_at');
            $q->select(
                'id',
                'shapefile_id',
                'feature_no',
                'classification_id',
                'survey_date',
                'description',
                'location',
                'default_location_id',
                DB::raw('ST_AsGeoJSON(geometry) as geometry')
            )->with('metadata', 'classification');
        },
    ])
    ->get()
    ->filter(fn($s) => $s->features->isNotEmpty());

    // ── FLATTEN FEATURES (always all features) ──────────────────────
    $geojson = $shapefiles->flatMap(function ($shapefile) {
        return $shapefile->features->map(function ($feature) use ($shapefile) {
            return [
                'feature_id'           => $feature->id,
                'shapefile_id'         => $shapefile->id,
                'category_id'          => $shapefile->category_id,
                'category'             => $shapefile->category->name ?? 'N/A',
                'classification_id'    => $feature->classification_id,
                'classification'       => $feature->classification->name  ?? 'No Classification',
                'classification_color' => $feature->classification->color ?? '#6c757d',
                'survey_date'          => $feature->survey_date,
                'description'          => $feature->description,
                'location'             => $feature->location,
                'default_location_id'  => $feature->default_location_id,
                'geometry'             => $feature->geometry
                    ? json_decode($feature->geometry, true)
                    : null,
                'metadata'             => $feature->metadata->map(fn($m) => [
                    'meta_key'   => $m->meta_key,
                    'meta_value' => $m->meta_value,
                ])->values()->toArray(),
            ];
        });
    })->values()->toArray();

    // ── CATEGORY COUNTS (from full dataset) ─────────────────────────
    $categoryCounts = collect($geojson)
        ->groupBy('category')
        ->map(fn($items) => $items->count());

    $categoryColors = collect($geojson)
        ->groupBy('category')
        ->map(fn($items) => $items->first()['classification_color'] ?? '#dc3545');

    $categoryLegend = $categories->map(function ($cat) use ($categoryCounts, $categoryColors) {
        return [
            'name'  => $cat->name,
            'count' => $categoryCounts[$cat->name] ?? 0,
            'color' => $categoryColors[$cat->name] ?? '#b71c1c',
        ];
    });

    // ── RETURN VIEW ─────────────────────────────────────────────────
    return view('admin.map', compact(
        'geojson',
        'page',
        'classifications',
        'categories',
        'adminCategory',
        'categoryCounts',
        'categoryColors',
        'categoryLegend',
        'defaultLoc'
    ));
}

    public function mapHome(Request $request)
    {
        $categories      = Category::all();
        $classifications = Classification::all();

        $adminCategory = auth()->user()->category->name ?? null;

        $page = [
            'pageTitle' => 'Shapefile Map View',
            'pageName'  => 'GIS Map Viewer',
        ];

        // ── Load shapefiles with features + metadata ──────────────────────────
        $shapefiles = Shapefile::with([
            'category',
            'features' => function ($q) {
                $q->whereNull('deleted_at')
                    ->select(
                        'id',
                        'shapefile_id',
                        'feature_no',
                        'classification_id',
                        'survey_date',          // ✅ NEW
                        'description',          // ✅ NEW
                        'location',             // ✅ NEW
                        DB::raw('ST_AsGeoJSON(geometry) as geometry')
                    )
                    ->with('metadata', 'classification');
            }
        ])->get()->filter(fn($s) => $s->features->count() > 0);

        // ── Flatten features → GeoJSON array ─────────────────────────────────
        $geojson = $shapefiles->flatMap(function ($shapefile) {
            return $shapefile->features->map(function ($feature) use ($shapefile) {
                return [
                    'feature_id'           => $feature->id,
                    'shapefile_id'         => $shapefile->id,
                    'category_id'          => $shapefile->category_id,
                    'category'             => $shapefile->category->name ?? 'N/A',
                    'classification_id'    => $feature->classification_id,
                    'classification'       => $feature->classification->name  ?? 'No Classification',
                    'classification_color' => $feature->classification->color ?? '#6c757d',
                    'survey_date'          => $feature->survey_date
                        ? \Carbon\Carbon::parse($feature->survey_date)->format('Y-m-d')
                        : null,          // ✅ NEW
                    'description'          => $feature->description ?? '',  // ✅ NEW
                    'location'             => $feature->location    ?? '',  // ✅ NEW
                    'geometry'             => $feature->geometry
                        ? json_decode($feature->geometry, true)
                        : null,
                    'metadata'             => $feature->metadata->map(fn($m) => [
                        'meta_key'   => $m->meta_key,
                        'meta_value' => $m->meta_value,
                    ])->values()->toArray(),
                ];
            });
        })->values()->toArray();

        // ── Category counts ───────────────────────────────────────────────────
        $categoryCounts = collect($geojson)
            ->groupBy('category')
            ->map(fn($items) => $items->count());

        // ── Category colours (first feature's classification colour) ──────────
        $categoryColors = $shapefiles
            ->flatMap(fn($s) => $s->features)
            ->groupBy(fn($f) => $f->shapefile->category->name ?? 'N/A')
            ->map(fn($features) => $features->first()->classification->color ?? '#dc3545');

        // ── Legend ────────────────────────────────────────────────────────────
        $categoryLegend = $categories->map(function ($cat) use ($categoryCounts, $categoryColors) {
            return [
                'name'  => $cat->name,
                'count' => $categoryCounts[$cat->name] ?? 0,
                'color' => $categoryColors[$cat->name]  ?? '#b71c1c',
            ];
        });

        return view('welcome', compact(
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
    //
    //
    // Create classifications
    //
    //
    public function createClassifications()
    {
    
        $user = auth()->user();
        $adminCategoryId = $user->category_id;
        if ($user->role === 'super_admin') {
            $adminCategory = null;
            $categories = Category::orderBy('name')->get();
            $classifications = Classification::withTrashed()
                ->with('category')
                ->orderBy('category_id')
                ->get();
        } else {
            $adminCategory = $user->category->name ?? null;
            $categories = Category::where('id', $adminCategoryId)->get();
            $classifications = Classification::where('category_id', $adminCategoryId)->get();
        };
        
    $trashedClassifications = Classification::onlyTrashed()->with('category')->get();
    return view('superadmin.classifications', compact('categories', 'classifications', 'trashedClassifications','adminCategory'));
    }

    // Store Classification (per category)
    public function storeClassification(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'color' => 'nullable|string'
        ]);

        Classification::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'color' => $request->color,
            'created_by' => auth()-id(),
        ]);

        return redirect()->back()->with('success', 'Classification added successfully.');
    }
     // Show form to edit a classification
    public function editClassification(Classification $classification)
    {
        $page = [
            'pageTitle' => 'Edit Classification',
        ];
        $user = auth()->user();
        $adminCategoryId = $user->category_id;
        if ($user->role === 'super_admin') {
            $adminCategory = null;
            $categories = Category::orderBy('name')->get();
        } else {
            $adminCategory = $user->category->name ?? null;
            $categories = Category::where('id', $adminCategoryId)->get();
            
        };
    

        return view('superadmin.edit_classifications', compact('classification', 'categories', 'page','adminCategory'));
    }

    // Update the classification
    public function updateClassification(Request $request, Classification $classification)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'color' => 'nullable|string',
        ]);

        $classification->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'color' => $request->color,
        ]);

        return redirect()->route('superadmin.classifications')
                        ->with('success', 'Classification updated successfully.');
    }
    // Soft delete
    public function destroyClassification(Classification $classification)
    {
        $classification->delete();

        return redirect()->route('superadmin.classifications')
                        ->with('success', 'Classification moved to trash.');
    }

    // Restore soft deleted
    public function restoreClassification($id)
    {
        $classification = Classification::withTrashed()->findOrFail($id);
        $classification->restore();

        return redirect()->route('superadmin.classifications')
                        ->with('success', 'Classification restored successfully.');
    }

    // Permanent delete
    public function forceDeleteClassification($id)
    {
        $classification = Classification::withTrashed()->findOrFail($id);
        $classification->forceDelete();

        return redirect()->route('superadmin.classifications')
                        ->with('success', 'Classification permanently deleted.');
    }

    // public function uploadGeoJson()
    // {
    //     $page = [
    //         'pageTitle' => 'Upload JSON',
    //         'pageName'  => 'Upload GeoJSON or JSON File',
    //     ];

    //     $adminCategoryId = auth()->user()->category_id;

    //     if ($adminCategoryId) {

    //         $adminCategory = auth()->user()->category->name ?? null;

    //         $categories = Category::with('classifications')
    //             ->where('id', $adminCategoryId)
    //             ->get();

    //     } else {

    //         $adminCategory = null;

    //         $categories = Category::with('classifications')->get();
    //     }

    //     return view('admin.upload', compact('page', 'categories', 'adminCategory'));
    // }
    // public function storeGeoJson(Request $request)
    // {
    //    $request->validate([

    //         'file'     => 'required|file|mimes:zip|max:30720', // Accept ZIP file, max 20MB
    //     ]);

    //     $file = $request->file('file');

    //     // 1️⃣ Extract the ZIP
    //     $zip = new \ZipArchive;
    //     if ($zip->open($file->getRealPath()) !== true) {
    //         return back()->withErrors(['file' => 'Cannot open ZIP file.']);
    //     }

    //     $extractPath = storage_path('app/public/geojsons/tmp/' . uniqid());
    //     mkdir($extractPath, 0777, true);
    //     $zip->extractTo($extractPath);
    //     $zip->close();

    //     // 2️⃣ Find the .geojson file inside
    //     $geoFile = glob($extractPath . '/*.json')[0] ?? null;
    //     if (!$geoFile) {
    //         return back()->withErrors(['file' => 'No .geojson file found in ZIP.']);
    //     }

    //     // 3️⃣ Read and parse the GeoJSON
    //     $contents = file_get_contents($geoFile);
    //     $geoArray = json_decode($contents, true);

    //     if (!$geoArray || !isset($geoArray['type'])) {
    //         return back()->withErrors(['file' => 'Invalid GeoJSON file.']);
    //     }
    //     $adminCategoryId = auth()->user()->category_id;
    //     // 4️⃣ Store in database
    //     DB::transaction(function () use ($geoArray, $request, $adminCategoryId) {

    //         // 1️⃣ Create shapefile
    //         $shapefile = Shapefile::create([
    //             'category_id' => $adminCategoryId,
    //             'user_id'  => auth()->id(),
    //         ]);

    //         if (isset($geoArray['features'])) {

    //             foreach ($geoArray['features'] as $index => $feature) {

    //                 // 2️⃣ Save each feature (ONE ROW PER FEATURE)
    //                 $featureModel = $shapefile->features()->create([
    //                     'geometry'   => DB::raw("ST_GeomFromGeoJSON('" . addslashes(json_encode($feature['geometry'])) . "')"),
    //                     'feature_no' => $index,
    //                 ]);


    //                 // 3️⃣ Save metadata for that feature
    //                 if (isset($feature['properties'])) {
    //                     foreach ($feature['properties'] as $key => $value) {
    //                         $featureModel->metadata()->create([
    //                             'meta_key'   => $key,
    //                             'meta_value' => $value,
    //                         ]);
    //                     }
    //                 }
    //             }
    //         }
    //     });
    //     $user = Auth::user()->role;

    //         if ($user === 'super_admin') {
    //             $user = 'superadmin';
    //         }
    //     // 5️⃣ Cleanup extracted files
    //     $this->deleteDirectory($extractPath);

    //     return redirect()->route($user . '.dashboard')->with('success', 'GeoJSON ZIP uploaded successfully!');

    // }
    // private function deleteDirectory($dir)
    // {
    //     if (!is_dir($dir)) return;
    //     $files = array_diff(scandir($dir), ['.', '..']);
    //     foreach ($files as $file) {
    //         (is_dir("$dir/$file")) ? $this->deleteDirectory("$dir/$file") : unlink("$dir/$file");
    //     }
    //     rmdir($dir);
    // }
}
