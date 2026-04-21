<?php

namespace App\Http\Controllers;

use App\Models\OfficeModule;
use App\Models\Shapefile;
use App\Models\User;
use App\Models\Category;
use App\Models\Classification;
use App\Models\DefaultLocation;
use App\Models\FeatureModel;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
    $totalUsers = User::where('role', 'user')->count();

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

    //
    $features = FeatureModel::withTrashed([
        'shapefile.user', 
        'shapefile.category', 
        'classification',
        'defaultLocation',
    ])
        ->whereHas('shapefile', function ($q) use ($adminCategoryId) {
            $q->where('category_id', $adminCategoryId);
        })
        ->latest('updated_at')
        ->paginate($perPage)
        ->withQueryString();

    $features->getCollection()->transform(function ($feature) {
        // Build location string
        $location = 'N/A';
        if ($feature->defaultLocation) {
            $parts = array_filter([
                $feature->defaultLocation->district,
                $feature->defaultLocation->municity,
                $feature->defaultLocation->brgy
            ]);
            $location = implode(', ', $parts);
        }
        
        $feature->category_name = $feature->shapefile->category->name ?? 'No Category';
        $feature->classification_name = $feature->classification->name ?? 'No Classification';
        $feature->classification_color = $feature->classification->color ?? '#6c757d';
        $feature->location = $location;
        $feature->description = Str::limit($feature->description, 15);
        $feature->visibility = $feature->visibility ?? 'private'; // ✅ Add visibility
        $feature->user_name = $feature->shapefile->user->name ?? 'Unknown'; // ✅ Add user name
        $feature->created_at_formatted = $feature->survey_date ? Carbon::parse($feature->survey_date)->format('M d, Y') : 'No Date';
        $feature->properties = $feature->metadata->mapWithKeys(function ($meta) {
            return [$meta->meta_key => $meta->meta_value];
        });
        return $feature;
    });
    
    // recent activities
    $recentActivities = FeatureModel::with([
        'creator',
        'updater',
        'shapefile.category',
        'classification',
        'defaultLocation'
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
            // Build location string
            $location = 'N/A';
            if ($feature->defaultLocation) {
                $parts = array_filter([
                    $feature->defaultLocation->district,
                    $feature->defaultLocation->municity,
                    $feature->defaultLocation->brgy
                ]);
                $location = implode(', ', $parts);
            }
            
            if ($feature->trashed()) {
                $action = 'Deleted';
                $user   = $feature->updater ?? $feature->creator;
                $actionColor = '#dc3545';
            } elseif ($feature->created_at->eq($feature->updated_at)) {
                $action = 'Created';
                $user   = $feature->creator;
                $actionColor = '#198754';
            } else {
                $action = 'Updated';
                $user   = $feature->updater;
                $actionColor = '#0d6efd';
            }

            return (object)[
                'classification_name' => $feature->classification->name ?? 'No Classification',
                'classification_color' => $feature->classification->color ?? '#6c757d',
                'category_name'       => $feature->shapefile->category->name ?? 'No Category',
                'action'              => $action,
                'user_name'           => $user->name ?? 'Unknown',
                'category_color'      => $feature->classification->color ?? '#6c757d',
                'updated_at'          => $feature->updated_at,
                'location'            => $location,
                'action_color'        => $actionColor,
                'trashed'             => $feature->trashed(),
                'id'                  => $feature->id,
                'created_at'          => $feature->survey_date ? Carbon::parse($feature->survey_date)->format('M d, Y') : 'No Date',
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
        $defaultLocRaw = DefaultLocation::select(
            'id',
            'district',
            'municity',
            'brgy',
            DB::raw('ST_AsGeoJSON(geometry) as geometry')
        )->get();

        $defaultLoc = $defaultLocRaw->map(function ($loc) {
            // Decode the GeoJSON string from MySQL
            $geometry = null;
            if ($loc->geometry) {
                $geoArray = json_decode($loc->geometry, true);
                // Ensure it's a valid GeoJSON geometry (has 'type' and 'coordinates')
                if (is_array($geoArray) && isset($geoArray['type'], $geoArray['coordinates'])) {
                    $geometry = $geoArray;
                } else {
                    \Log::warning('Invalid GeoJSON for default location ID ' . $loc->id);
                }
            }
            return [
                'id'       => $loc->id,
                'district' => $loc->district ?? '',
                'municity' => $loc->municity ?? '',
                'brgy'     => $loc->brgy ?? '',
                'geometry' => $geometry,
            ];
        })->filter(function ($item) {
            // Only keep records with valid geometry
            return !is_null($item['geometry']);
        })->values();

        $defaultDistricts = DefaultLocation::select('district')
        ->whereNotNull('district')
        ->distinct()
        ->orderBy('district', 'asc')
        ->pluck('district');
        $page = [
            'pageTitle' => 'Create Spatial Data',
            'pageName'  => 'Create Spatial Data',
        ];

        $adminCategoryId = auth()->user()->category_id;
        $classifications = Classification::where('category_id', $adminCategoryId)->get();
        $district = DefaultLocation::select('district')->whereNotNull('district')->distinct()->orderBy('district', 'asc')->pluck('district');
        
        // Only allow admin category
        $categories = Category::where('id', $adminCategoryId)->get();

        return view('admin.create', compact('page', 'categories', 'classifications', 'district', 'defaultLoc' , 'defaultDistricts'));
    }

    /**
     * Store shapefile
     */
    public function store(Request $request)
{
    $adminCategoryId = auth()->user()->category_id;

    $rules = [
        'geometry' => 'required|json',
        'classification_id' => 'required|exists:classifications,id',
        'survey_date' => 'required|date',
        'description' => 'required|string',
        'visibility' => 'required|in:public,private',
        'metadata.*.key' => 'nullable|string',
        'metadata.*.value' => 'nullable|string',
    ];
    
    // If not using default location, require manual fields (brgy optional)
    if (!$request->has('default_location_id') || !$request->default_location_id) {
        $rules['district'] = 'required|string';
        $rules['municity'] = 'required|string';
        $rules['brgy'] = 'nullable|string';
    }
    
    $request->validate($rules);

    DB::transaction(function () use ($request, $adminCategoryId) {
        $user = auth()->id();

        // Determine location ID
        if ($request->has('default_location_id') && $request->default_location_id) {
            // Use existing default location
            $defaultLocationId = $request->default_location_id;
        } else {
            // Find or create default location from manual inputs
            $defaultLocation = DefaultLocation::firstOrCreate([
                'district' => $request->district,
                'municity' => $request->municity,
                'brgy' => $request->brgy ?? null,
            ]);
            $defaultLocationId = $defaultLocation->id;
        }

        // CREATE SHAPEFILE
        $shapefile = Shapefile::create([
            'category_id' => $adminCategoryId,
            'user_id'     => $user,
            'created_by'  => $user,
        ]);

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
                'default_location_id' => $defaultLocationId, // ✅ Use the variable
                'visibility' => $request->visibility,
                'created_by' => $user,
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
        ->with('success', 'Spatial Data created successfully.');
}

    /**
     * Edit shapefile
     */
    public function edit($id)
{
    $feature = FeatureModel::with(['metadata', 'shapefile', 'defaultLocation'])->findOrFail($id);
    
    // Authorization check
    if ($feature->shapefile->category_id !== auth()->user()->category_id) {
        abort(403, 'Unauthorized action.');
    }

    // Get districts for dropdown
    $districts = DefaultLocation::select('district')->distinct()->orderBy('district', 'asc')->pluck('district');
    
    // Get municities and brgy based on existing location
    $existingDistrict = $feature->defaultLocation->district ?? null;
    $existingMunicity = $feature->defaultLocation->municity ?? null;
    $existingBrgy = $feature->defaultLocation->brgy ?? null;
    
    $municities = [];
    $barangays = [];
    
    if ($existingDistrict) {
        $municities = DefaultLocation::where('district', $existingDistrict)
            ->select('municity')
            ->distinct()
            ->orderBy('municity', 'asc')
            ->pluck('municity')
            ->toArray();
    }
    
    if ($existingMunicity) {
        $barangays = DefaultLocation::where('municity', $existingMunicity)
            ->select('brgy')
            ->distinct()
            ->orderBy('brgy', 'asc')
            ->pluck('brgy')
            ->toArray();
    }

    $page = [
        'pageTitle' => 'Edit Spatial Data',
        'pageName'  => 'Edit Spatial Data',
    ];

    $categories = Category::where('id', auth()->user()->category_id)->get();

    // Get geometry as GeoJSON
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
        'feature',
        'categories',
        'geoJson',
        'page',
        'adminCategory',
        'user',
        'classifications',
        'districts',
        'municities',
        'barangays',
        'existingDistrict',
        'existingMunicity',
        'existingBrgy'
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
        'district' => 'required|string',
        'municity' => 'required|string',
        'brgy' => 'nullable|string',
        'metadata' => 'nullable|array',
        'metadata.*.key' => 'nullable|string',
        'metadata.*.value' => 'nullable|string',
    ]);

    DB::transaction(function () use ($request, $feature) {
        $user = auth()->id();

        // Find or create default location
        $defaultLocation = DefaultLocation::firstOrCreate([
            'district' => $request->district,
            'municity' => $request->municity,
            'brgy' => $request->brgy,
        ]);

        $geoArray = json_decode($request->geometry, true);
        
        if (!isset($geoArray['features']) || empty($geoArray['features'])) {
            throw new \Exception("Invalid GeoJSON structure.");
        }
        
        $geometry = $geoArray['features'][0]['geometry'];

        // Update the feature
        $feature->update([
            'geometry' => DB::raw("ST_GeomFromGeoJSON('" . addslashes(json_encode($geometry)) . "')"),
            'classification_id' => $request->classification_id,
            'survey_date' => $request->survey_date,
            'description' => $request->description,
            'default_location_id' => $defaultLocation->id, // ✅ Use ID instead of string
            'visibility' => $request->visibility,
            'updated_by' => $user,
        ]);

        // Update the shapefile visibility
        $feature->shapefile->update([
            'updated_by' => $user,
        ]);

        // Update metadata
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
        ->with('success', 'Spatial Data updated successfully.');
}

    /**
     * Upload Office Module
     */
    public function uploadGeoJson()
    {
        $page = [
            'pageTitle' => 'Upload Spatial Data',
            'pageName'  => 'Upload Spatial Data',
        ];
         $defaultLocRaw = DefaultLocation::select(
            'id',
            'district',
            'municity',
            'brgy',
            DB::raw('ST_AsGeoJSON(geometry) as geometry')
        )->get();

        $defaultLoc = $defaultLocRaw->map(function ($loc) {
            // Decode the GeoJSON string from MySQL
            $geometry = null;
            if ($loc->geometry) {
                $geoArray = json_decode($loc->geometry, true);
                // Ensure it's a valid GeoJSON geometry (has 'type' and 'coordinates')
                if (is_array($geoArray) && isset($geoArray['type'], $geoArray['coordinates'])) {
                    $geometry = $geoArray;
                } else {
                    \Log::warning('Invalid GeoJSON for default location ID ' . $loc->id);
                }
            }
            return [
                'id'       => $loc->id,
                'district' => $loc->district ?? '',
                'municity' => $loc->municity ?? '',
                'brgy'     => $loc->brgy ?? '',
                'geometry' => $geometry,
            ];
        })->filter(function ($item) {
            // Only keep records with valid geometry
            return !is_null($item['geometry']);
        })->values();
         $defaultDistricts = DefaultLocation::select('district')
        ->whereNotNull('district')
        ->distinct()
        ->orderBy('district', 'asc')
        ->pluck('district');

        $adminCategoryId = auth()->user()->category_id;
        $adminCategory = auth()->user()->category->name ?? null;

        
        $categories = Category::with('classifications')->where('id', $adminCategoryId)->get();
        $classifications = Classification::where('category_id', $adminCategoryId)->get();

        return view('admin.upload', compact('page', 'categories', 'adminCategory', 'classifications', 'defaultDistricts','defaultLoc'));
    }

    public function storeGeoJson(Request $request)
{
    $adminCategoryId = auth()->user()->category_id;
    
    // Base validation rules
    $rules = [
        'visibility' => 'required|in:public,private',
        'classification_id' => 'required|exists:classifications,id',
        'survey_date' => 'required|date',
        'description' => 'required|string',
        'file' => 'required|file',
    ];
    
    $file = $request->file('file');
    $extension = strtolower($file->getClientOriginalExtension());
    
    // Determine upload type based on file extension
    if ($extension === 'csv' || $extension === 'txt') {
        // CSV upload mode - location comes from inside the CSV
        $rules['file'] = 'required|file|mimes:csv,txt|max:10240';
        $request->validate($rules);
        return $this->handleCsvUpload($request, $adminCategoryId);
        
    } elseif ($extension === 'zip') {
        // ZIP upload mode - requires manual location input
        $rules['file'] = 'required|file|mimes:zip|max:30720';
        $rules['district'] = 'required|string';
        $rules['municity'] = 'required|string';
        $rules['brgy'] = 'nullable|string';
        $request->validate($rules);
        return $this->handleZipUpload($request, $adminCategoryId);
        
    } else {
        return back()->withErrors(['file' => 'Invalid file type. Please upload a CSV or ZIP file.']);
    }
}
/**
 * Handle ZIP file upload (GeoJSON/KML)
 */
private function handleZipUpload($request, $adminCategoryId)
{
    $file = $request->file('file');
    $zip = new \ZipArchive;
    if ($zip->open($file->getRealPath()) !== true) {
        return back()->withErrors(['file' => 'Cannot open ZIP file.']);
    }

    $extractPath = storage_path('app/public/geojsons/tmp/' . uniqid());
    mkdir($extractPath, 0777, true);
    $zip->extractTo($extractPath);  
    $zip->close();

    $jsonFile = glob($extractPath . '/*.{json,geojson}', GLOB_BRACE);
    $kmlFile = glob($extractPath . '/*.kml');

    $geoFile = $jsonFile[0] ?? $kmlFile[0] ?? null;
    if (!$geoFile) {
        $this->deleteDirectory($extractPath);
        return back()->withErrors(['file' => 'No .geojson or .kml file found in ZIP.']);
    }
    
    $extension = pathinfo($geoFile, PATHINFO_EXTENSION);

    if ($extension === 'json' || $extension === 'geojson') {
        $contents = file_get_contents($geoFile);
        $geoArray = json_decode($contents, true);

        if (!$geoArray || !isset($geoArray['type'])) {
            $this->deleteDirectory($extractPath);
            return back()->withErrors(['file' => 'Invalid GeoJSON file.']);
        }

    } elseif ($extension === 'kml') {
        $convertedPath = $geoFile . '.geojson';

        // Convert KML → GeoJSON
        exec("ogr2ogr -f GeoJSON \"$convertedPath\" \"$geoFile\" -skipfailures -dim 2", $output, $returnVar);

        // Check if conversion failed
        if ($returnVar !== 0 || !file_exists($convertedPath)) {
            $this->deleteDirectory($extractPath);
            return back()->withErrors(['file' => 'KML conversion failed.']);
        }

        // Read converted file
        $contents = file_get_contents($convertedPath);
        $geoArray = json_decode($contents, true);

        // Validate GeoJSON structure
        if (!$geoArray || !isset($geoArray['type']) || !isset($geoArray['features']) || empty($geoArray['features'])) {
            $this->deleteDirectory($extractPath);
            return back()->withErrors(['file' => 'Invalid or empty converted GeoJSON.']);
        }
    } else {
        $this->deleteDirectory($extractPath);
        return back()->withErrors(['file' => 'Unsupported file type.']);
    }

    DB::transaction(function () use ($geoArray, $request, $adminCategoryId) {
        $user = auth()->id();

        // Find or create default location from manual inputs
        $defaultLocation = DefaultLocation::firstOrCreate([
            'district' => $request->district,
            'municity' => $request->municity,
            'brgy' => $request->brgy ?? null,
        ]);

        $shapefile = Shapefile::create([
            'category_id' => $adminCategoryId,
            'user_id'     => $user,
            'created_by'  => $user,
        ]);

        if (isset($geoArray['features'])) {
            foreach ($geoArray['features'] as $index => $feature) {
                if (!isset($feature['geometry']) || !$feature['geometry']) {
                    continue;
                }
                
                $featureModel = $shapefile->features()->create([
                    'geometry' => DB::raw("ST_GeomFromGeoJSON('" . addslashes(json_encode($feature['geometry'])) . "')"),
                    'feature_no' => $index,
                    'classification_id' => $request->classification_id,
                    'survey_date' => $request->survey_date,
                    'description' => $request->description,
                    'default_location_id' => $defaultLocation->id,
                    'visibility' => $request->visibility,
                    'created_by' => $user,
                ]);

                if (isset($feature['properties'])) {
                    foreach ($feature['properties'] as $key => $value) {
                        if (!empty($key)) {
                            $featureModel->metadata()->create([
                                'meta_key'   => $key,
                                'meta_value' => is_array($value) ? json_encode($value) : (string)$value,
                            ]);
                        }
                    }
                }
            }
        }
    });

    $this->deleteDirectory($extractPath);

    return redirect()->route('admin.dashboard')->with('success', 'GeoJSON ZIP uploaded successfully!');
}


/**
 * Handle CSV file upload (using default location)
 * Groups by location and aggregates data with header-specific rules
 */
/**
 * Handle CSV file upload (using default location)
 * Groups by location and updates existing features or creates new ones
 */
private function handleCsvUpload($request, $adminCategoryId)
{
    $file = $request->file('file');
    
    // Parse CSV file
    $csvData = array_map('str_getcsv', file($file->getRealPath()));
    
    if (empty($csvData)) {
        return back()->withErrors(['file' => 'CSV file is empty.']);
    }
    
    // First row as headers
    $headers = array_map('trim', $csvData[0]);
    unset($csvData[0]);
    $csvData = array_values($csvData);
    
    // Find location column indices
    $districtCol = array_search('DistrictName', $headers);
    $municityCol = array_search('MunName', $headers);
    $brgyCol = array_search('BrgyName', $headers);
    
    // Group rows by location
    $groupedData = [];
    
    foreach ($csvData as $row) {
        // Skip empty rows
        if (empty(array_filter($row))) {
            continue;
        }
        
        // Get location values
        $district = $districtCol !== false && isset($row[$districtCol]) ? trim($row[$districtCol]) : null;
        $municity = $municityCol !== false && isset($row[$municityCol]) ? trim($row[$municityCol]) : null;
        $brgy = $brgyCol !== false && isset($row[$brgyCol]) ? trim($row[$brgyCol]) : null;
        
        // Create location key for grouping
        $locationKey = implode('|', [$district ?? '', $municity ?? '', $brgy ?? '']);
        
        if (!isset($groupedData[$locationKey])) {
            $groupedData[$locationKey] = [
                'district' => $district,
                'municity' => $municity,
                'brgy' => $brgy,
                'rows' => [],
            ];
        }
        
        // Store row data (excluding location columns)
        $rowData = [];
        foreach ($headers as $colIndex => $header) {
            if (empty($header)) continue;
            if ($header === 'DistrictName' || $header === 'MunName' || $header === 'BrgyName') continue;
            
            $value = isset($row[$colIndex]) ? trim($row[$colIndex]) : '';
            $rowData[$header] = $value;
        }
        
        $groupedData[$locationKey]['rows'][] = $rowData;
    }
    
    $user = auth()->id();
    $updatedCount = 0;
    $createdCount = 0;
    
    DB::transaction(function () use ($request, $adminCategoryId, $groupedData, $user, &$updatedCount, &$createdCount) {
        
        foreach ($groupedData as $locationData) {
            $district = $locationData['district'];
            $municity = $locationData['municity'];
            $brgy = $locationData['brgy'];
            $rows = $locationData['rows'];
            
            // Find matching location
            $location = $this->findMatchingLocation($district, $municity, $brgy);
            
            if (!$location) {
                Log::warning("No location found for: district={$district}, municity={$municity}, brgy={$brgy}");
                continue;
            }
            
            // Aggregate data for this location with custom rules
            $aggregatedMetadata = $this->aggregateWithCustomRules($rows, $request->survey_date);
            
            // Check if feature already exists for this location and classification
            $existingFeature = $this->findExistingFeature(
                $location->id,
                $request->classification_id,
                $adminCategoryId
            );
            
            if ($existingFeature) {
                // UPDATE EXISTING FEATURE
                $this->updateExistingFeature($existingFeature, $aggregatedMetadata, $request);
                $updatedCount++;
            } else {
                // CREATE NEW FEATURE
                $this->createNewFeature($request, $adminCategoryId, $location, $aggregatedMetadata, $user, $createdCount);
                $createdCount++;
            }
        }
    });
    
    $locationCount = count($groupedData);
    return redirect()->route('admin.dashboard')->with('success', 
        "CSV processed successfully! Created {$createdCount} new features, updated {$updatedCount} existing features."
    );
}

/**
 * Find existing feature by location, classification, and category
 */
private function findExistingFeature($defaultLocationId, $classificationId, $categoryId)
{
    return FeatureModel::where('default_location_id', $defaultLocationId)
        ->where('classification_id', $classificationId)
        ->whereHas('shapefile', function ($query) use ($categoryId) {
            $query->where('category_id', $categoryId);
        })
        ->first();
}

/**
 * Update existing feature with new aggregated data
 */
private function updateExistingFeature($feature, $newMetadata, $request)
{
    // Update feature basic info
    $feature->update([
        'survey_date' => $request->survey_date,
        'description' => $request->description,
    ]);
    
    // Update metadata - ADD to existing values
    foreach ($newMetadata as $key => $value) {
        if (empty($key) || $value === null || $value === '') continue;
        
        $existingMeta = $feature->metadata()->where('meta_key', $key)->first();
        
        if ($existingMeta) {
            // Check if it's a numeric value (sum it)
            if (is_numeric($value) && is_numeric($existingMeta->meta_value)) {
                $newValue = $existingMeta->meta_value + $value;
            } else {
                // For non-numeric, replace or append based on key type
                if (str_contains($key, '_count') || str_contains($key, 'total_')) {
                    $newValue = $existingMeta->meta_value + $value;
                } else {
                    $newValue = $value; // Replace
                }
            }
            
            $existingMeta->update(['meta_value' => (string)$newValue]);
        } else {
            // Create new metadata
            $feature->metadata()->create([
                'meta_key' => $key,
                'meta_value' => is_array($value) ? json_encode($value) : (string)$value,
            ]);
        }
    }
    
    Log::info("Updated feature ID: {$feature->id} at location ID: {$feature->default_location_id}");
}

/**
 * Create new feature
 */
private function createNewFeature($request, $adminCategoryId, $location, $aggregatedMetadata, $user, &$featureIndex)
{
    // Find or create shapefile for this category
    $shapefile = Shapefile::firstOrCreate(
        [
            'category_id' => $adminCategoryId,
            
        ],
        [
            'user_id' => $user,
            'created_by' => $user,
        ]
    );
    
    // Create new feature
    $featureModel = $shapefile->features()->create([
        'geometry' => $location->geometry,
        'feature_no' => $shapefile->features()->count(),
        'classification_id' => $request->classification_id,
        'survey_date' => $request->survey_date,
        'description' => $request->description,
        'default_location_id' => $location->id,
        'created_by' => $user,
        'visibility' => $request->visibility,
    ]);
    
    // Store aggregated metadata
    foreach ($aggregatedMetadata as $key => $value) {
        if (!empty($key) && $value !== null && $value !== '') {
            $featureModel->metadata()->create([
                'meta_key'   => $key,
                'meta_value' => is_array($value) ? json_encode($value) : (string)$value,
            ]);
        }
    }
    
    Log::info("Created new feature ID: {$featureModel->id} at location ID: {$location->id}");
    
    return $featureModel;
}
/**
 * Aggregate rows with header-specific rules
 */
private function aggregateWithCustomRules($rows, $surveyDate)
{
    if (empty($rows)) {
        return [];
    }
    
    $result = [];
    
    
    // WHITELIST: Headers with CUSTOM rules
    $customRules = [
        'sex' => [
            'type' => 'categorize',
            'values' => ['M', 'F'],
        ],
        'date_of_birth' => [
            'type' => 'age_bracket',
            'survey_date' => $surveyDate,
            'brackets' => [
                '0-17' => [0, 17],
                '18-25' => [18, 25],
                '26-35' => [26, 35],
                '36-45' => [36, 45],
                '46-60' => [46, 60],
                '60+' => [61, 999],
            ],
        ],
        'course_abbr' => [
            'type' => 'categorize',
            'values' => null,
        ],
        'school_abbr' => [
            'type' => 'categorize',
            'values' => null,
        ],
    ];
    
    // WHITELIST: Headers with DEFAULT processing
    $defaultHeaders = [
        'Medical Assistance',
        'Burial Assistance',
        'Financial Assistance',
        'Others Assistance',
        
        
    ];
    
    // Get all unique headers from rows
    $allHeaders = [];
    foreach ($rows as $row) {
        $allHeaders = array_merge($allHeaders, array_keys($row));
    }
    $allHeaders = array_unique($allHeaders);
    
    foreach ($allHeaders as $header) {
        if (empty($header)) continue;
        
        // Check if it's in custom rules
        if (isset($customRules[$header])) {
            $rule = $customRules[$header];
            
            switch ($rule['type']) {
                case 'categorize':
                    $result = array_merge($result, $this->aggregateCategorize($rows, $header, $rule['values']));
                    break;
                    
                case 'age_bracket':
                    $result = array_merge($result, $this->aggregateAgeBracket($rows, $header, $rule['survey_date'], $rule['brackets']));
                    break;
            }
        }
        // Check if it's in default whitelist
        elseif (in_array($header, $defaultHeaders)) {
            // ✅ DEFAULT PROCESSING
            $aggregated = $this->aggregateSmart($rows, $header);
            if ($aggregated !== null) {
                $result[$header] = $aggregated;
            }
        }
        // Anything else is IGNORED
    }
    
    return $result;
}

/**
 * Aggregate categorical data (count occurrences of each value)
 */
private function aggregateCategorize($rows, $header, $allowedValues = null)
{
    $counts = [];
    
    foreach ($rows as $row) {
        $value = $row[$header] ?? '';
        if ($value === '') continue;
        
        // Normalize value
        $value = ucfirst(strtolower(trim($value)));
        
        if ($allowedValues !== null) {
            // Only count if in allowed values
            if (in_array($value, $allowedValues)) {
                $counts[$value] = ($counts[$value] ?? 0) + 1;
            }
        } else {
            // Count all values
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }
    }
    
    $result = [];
    foreach ($counts as $category => $count) {
        $result[$header . '_' . str_replace([' ', '-'], '_', $category)] = $count;
    }
    
    return $result;
}

/**
 * Aggregate age data into brackets
 */
private function aggregateAgeBracket($rows, $header, $surveyDate, $brackets)
{
    $surveyTimestamp = strtotime($surveyDate);
    $bracketCounts = array_fill_keys(array_keys($brackets), 0);
    
    foreach ($rows as $row) {
        $dob = $row[$header] ?? '';
        if ($dob === '') continue;
        
        $age = $this->calculateAge($dob, $surveyDate);
        
        if ($age === null) continue;
        
        // Find which bracket the age falls into
        foreach ($brackets as $bracketName => $range) {
            if ($age >= $range[0] && $age <= $range[1]) {
                $bracketCounts[$bracketName]++;
                break;
            }
        }
    }
    
    $result = [];
    foreach ($bracketCounts as $bracket => $count) {
        $bracketKey = str_replace(['-', '+'], ['_to_', '_plus'], $bracket);
        $result['Age_Bracket_' . $bracketKey] = $count;
    }
    
    return $result;
}

/**
 * Calculate age from date of birth to survey date
 */
private function calculateAge($dob, $surveyDate)
{
    try {
        $birthDate = new DateTime($dob);
        $survey = new DateTime($surveyDate);
        $age = $birthDate->diff($survey)->y;
        return $age;
    } catch (Exception $e) {
        // Try different date formats
        $formats = ['Y-m-d', 'm/d/Y', 'd/m/Y', 'Y/m/d', 'M j, Y', 'F j, Y'];
        
        foreach ($formats as $format) {
            $birthDate = DateTime::createFromFormat($format, $dob);
            if ($birthDate !== false) {
                $survey = new DateTime($surveyDate);
                return $birthDate->diff($survey)->y;
            }
        }
        
        return null;
    }
}

/**
 * Default smart aggregation (sum for numeric, count for text)
 */
private function aggregateSmart($rows, $header)
{
    $numericCount = 0;
    $textCount = 0;
    $sum = 0;
    
    // Sample to determine type
    $sampleSize = min(10, count($rows));
    
    for ($i = 0; $i < $sampleSize; $i++) {
        $value = $rows[$i][$header] ?? '';
        if ($value === '') continue;
        
        $numericValue = $this->parseNumericValue($value);
        if ($numericValue !== false) {
            $numericCount++;
        } else {
            $textCount++;
        }
    }
    
    // If mostly numeric, sum all
    if ($numericCount > $textCount) {
        foreach ($rows as $row) {
            $value = $row[$header] ?? '';
            if ($value === '') continue;
            
            $numericValue = $this->parseNumericValue($value);
            if ($numericValue !== false) {
                $sum += $numericValue;
            }
        }
        return $sum;
    }
    
    // Otherwise, just count non-empty
    $count = 0;
    foreach ($rows as $row) {
        $value = $row[$header] ?? '';
        if ($value !== '') {
            $count++;
        }
    }
    
    return $count;
}

/**
 * Parse a string value to detect if it's numeric/currency
 */
private function parseNumericValue($value)
{
    $cleaned = preg_replace('/[₱$,\s]/', '', $value);
    
    if (is_numeric($cleaned)) {
        return (float)$cleaned;
    }
    
    return false;
}

/**
 * Find matching default location based on hierarchy
 */
private function findMatchingLocation($district, $municity, $brgy)
{
    if (empty($district) && !empty($municity)) {
        $district = $this->findDistrictByMunicity($municity);
    }
    
    if (empty($district) && empty($municity)) {
        return null;
    }
    
    // Try exact match with barangay
    if (!empty($brgy) && !empty($municity) && !empty($district)) {
        $location = DefaultLocation::where('district', $district)
            ->where('municity', $municity)
            ->where('brgy', $brgy)
            ->whereNotNull('geometry')
            ->first();
        
        if ($location) {
            return $location;
        }
    }
    
    // Try municipal/city boundary
    if (!empty($municity) && !empty($district)) {
        $location = DefaultLocation::where('district', $district)
            ->where('municity', $municity)
            ->whereIn('boundary_type', ['municipality', 'city'])
            ->whereNotNull('geometry')
            ->first();
        
        if ($location) {
            return $location;
        }
    }
    
    return null;
}

/**
 * Find district by municipality name
 */
private function findDistrictByMunicity($municity)
{
    $location = DefaultLocation::where('municity', $municity)
        ->whereNotNull('district')
        ->first();
    
    return $location ? $location->district : null;
}

/**
 * Helper function to delete directory
 */
private function deleteDirectory($path)
{
    if (!is_dir($path)) {
        return;
    }
    
    $files = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
        \RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ($files as $file) {
        if ($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }
    
    rmdir($path);
}
    public function destroy($id)
    {
        $feature = FeatureModel::findOrFail($id);



        $feature->delete();

        return back()->with('success', 'Spatial deleted successfully.');
    }

    /**
     * Restore shapefile
     */
    public function restore($id)
    {
        $feature = FeatureModel::withTrashed()->findOrFail($id);



        $feature->restore();

        return back()->with('success', 'Spatial restored successfully.');
    }
}
