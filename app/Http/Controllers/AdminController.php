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

    $features = FeatureModel::withTrashed([
        'shapefile.user', 
        'shapefile.category', 
        'classification',
        'defaultLocation'
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
        $feature->visibility = $feature->shapefile->visibility ?? 'private'; // ✅ Add visibility
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
            'pageTitle' => 'Create Shapefile',
            'pageName'  => 'Create Shapefile',
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
            'visibility'  => $request->visibility,
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
        ->with('success', 'Shapefile created successfully.');
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
        'pageTitle' => 'Edit Shapefile',
        'pageName'  => 'Edit Shapefile',
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
        'brgy' => 'required|string',
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
            'updated_by' => $user,
        ]);

        // Update the shapefile visibility
        $feature->shapefile->update([
            'visibility' => $request->visibility,
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
    ];
    
    // Check if using default location
    $isUsingDefaultLocation = $request->has('default_location_id') && $request->default_location_id;
    
    if ($isUsingDefaultLocation) {
        // CSV upload mode
        $rules['file'] = 'required|file|mimes:csv,txt|max:10240';
        $rules['default_district'] = 'required|string';
        $rules['default_municity'] = 'required|string';
        $rules['default_brgy'] = 'nullable|string';
    } else {
        // ZIP upload mode with manual location
        $rules['file'] = 'required|file|mimes:zip|max:30720';
        $rules['district'] = 'required|string';
        $rules['municity'] = 'required|string';
        $rules['brgy'] = 'nullable|string';
    }
    
    $request->validate($rules);
    
    $file = $request->file('file');
    
    if ($isUsingDefaultLocation) {
        return $this->handleCsvUpload($request, $adminCategoryId);
    } else {
        return $this->handleZipUpload($request, $adminCategoryId);
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
            'visibility'  => $request->visibility,
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
 * Supports both Municipal/City-level and Barangay-level uploads
 */
/**
 * Handle CSV file upload (using default location)
 * Aggregates CSV data into a single feature with summarized metadata
 */
/**
 * Handle CSV file upload (using default location)
 * Aggregates CSV data into a single feature with count (text) or sum (numeric) metadata
 */
private function handleCsvUpload($request, $adminCategoryId)
{
    $file = $request->file('file');
    $district = $request->default_district ?? null;
    $municity = $request->default_municity ?? null;
    $brgy = $request->default_brgy ?? null;
    
    // Parse CSV file
    $csvData = array_map('str_getcsv', file($file->getRealPath()));
    
    if (empty($csvData)) {
        return back()->withErrors(['file' => 'CSV file is empty.']);
    }
    
    // First row as headers
    $headers = array_map('trim', $csvData[0]);
    unset($csvData[0]);
     $csvData = array_values($csvData); // ✅ REINDEX ARRAY
    // Determine if this is municipal/city-level or barangay-level
    $isMunicipalOrCityLevel = empty($brgy);
    
    if ($isMunicipalOrCityLevel) {
        $boundaryLocation = DefaultLocation::where('district', $district)
            ->where('municity', $municity)
            ->whereIn('boundary_type', ['municipality', 'city'])
            ->whereNotNull('geometry')
            ->first();
        
        if (!$boundaryLocation) {
            return back()->withErrors(['file' => 'No municipal/city boundary found for this location.']);
        }
        
        $defaultLocationId = $boundaryLocation->id;
        $location = $boundaryLocation;
        $level = $boundaryLocation->boundary_type;
        
    } else {
        $barangayLocation = DefaultLocation::where('district', $district)
            ->where('municity', $municity)
            ->where('brgy', $brgy)
            ->where('boundary_type', 'barangay')
            ->whereNotNull('geometry')
            ->first();
        
        if (!$barangayLocation) {
            return back()->withErrors(['file' => 'No barangay boundary found for this location.']);
        }
        
        $defaultLocationId = $barangayLocation->id;
        $location = $barangayLocation;
        $level = 'barangay';
    }
    
    // Aggregate the CSV data
    $aggregatedMetadata = $this->aggregateCsvDataSmart($csvData, $headers);
    
    DB::transaction(function () use ($request, $adminCategoryId, $defaultLocationId, $location, $aggregatedMetadata) {
        $user = auth()->id();
        
        $shapefile = Shapefile::create([
            'category_id' => $adminCategoryId,
            'user_id'     => $user,
            'created_by'  => $user,
            'visibility'  => $request->visibility,
        ]);
        
        // Create SINGLE feature with the geometry from the found location
        $featureModel = $shapefile->features()->create([
            'geometry' => $location->geometry,
            'feature_no' => 0,
            'classification_id' => $request->classification_id,
            'survey_date' => $request->survey_date,
            'description' => $request->description,
            'default_location_id' => $defaultLocationId,
            'created_by' => $user,
        ]);
        
        // Store aggregated metadata
        foreach ($aggregatedMetadata as $key => $value) {
            if (!empty($key)) {
                $featureModel->metadata()->create([
                    'meta_key'   => $key,
                    'meta_value' => (string)$value,
                ]);
            }
        }
    });
    
    return redirect()->route('admin.dashboard')->with('success', "CSV file aggregated successfully at {$level} level!");
}

/**
 * Aggregate CSV data smartly
 * - Text columns → Count of non-empty values
 * - Numeric columns → Sum of values
 */
private function aggregateCsvDataSmart($csvData, $headers)
{
    $result = [];
    $columnTypes = [];
    
    // First pass: determine column types
    foreach ($headers as $header) {
        if (empty($header)) continue;
        $columnTypes[$header] = 'text'; // Default to text
        $result[$header] = 0;
    }
    
    // Analyze first few rows to detect numeric columns
    $sampleSize = min(10, count($csvData));
    $numericCounts = [];
    
    foreach ($headers as $header) {
        $numericCounts[$header] = 0;
    }
    
    for ($i = 0; $i < $sampleSize; $i++) {
        $row = $csvData[$i];
        foreach ($headers as $index => $header) {
            if (empty($header)) continue;
            
            $value = isset($row[$index]) ? trim($row[$index]) : '';
            
            if ($value !== '') {
                $numericValue = $this->parseNumericValue($value);
                if ($numericValue !== false) {
                    $numericCounts[$header]++;
                }
            }
        }
    }
    
    // Set column type based on sample
    foreach ($headers as $header) {
        if (empty($header)) continue;
        
        // If more than 50% of sample values are numeric, treat as numeric column
        if ($numericCounts[$header] > 0 && $numericCounts[$header] >= ($sampleSize * 0.5)) {
            $columnTypes[$header] = 'numeric';
        }
    }
    
    // Second pass: aggregate values
    foreach ($csvData as $row) {
        foreach ($headers as $index => $header) {
            if (empty($header)) continue;
            
            $value = isset($row[$index]) ? trim($row[$index]) : '';
            
            if ($value === '') continue;
            
            if ($columnTypes[$header] === 'numeric') {
                $numericValue = $this->parseNumericValue($value);
                if ($numericValue !== false) {
                    $result[$header] += $numericValue;
                }
            } else {
                // Text column - just count
                $result[$header]++;
            }
        }
    }
    
    return $result;
}

/**
 * Parse a string value to detect if it's numeric/currency
 * Returns float value if numeric, false otherwise
 */
private function parseNumericValue($value)
{
    // Remove currency symbols, commas, spaces
    $cleaned = preg_replace('/[₱$,\s]/', '', $value);
    
    // Check if it's a valid number
    if (is_numeric($cleaned)) {
        return (float)$cleaned;
    }
    
    return false;
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
