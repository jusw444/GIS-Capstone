<?php

namespace App\Http\Controllers;

use App\Models\OfficeModule;
use App\Models\Shapefile;
use App\Models\User;
use App\Models\Category;
use App\Models\Classification;
use App\Models\DefaultLocation;
use App\Models\FeatureModel;
use Illuminate\Support\Facades\Validator;
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
 * Enterprise-grade CSV Import Handler
 * Handles ANY CSV structure dynamically
 * Only requires MunName and BrgyName columns
 * All other columns are automatically treated as metadata
 */
private function handleCsvUpload($request, $adminCategoryId)
{
    try {
        // Validate request
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:51200',
            'classification_id' => 'required|exists:classifications,id',
            'survey_date' => 'required|date',
            'description' => 'nullable|string|max:1000',
            'visibility' => 'required|in:public,private,internal',
            'aggregation_method' => 'nullable|in:sum,count,concat,last,first,unique' // Allow user to choose aggregation method
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $file = $request->file('file');
        
        // Parse CSV with intelligent delimiter detection
        $csvData = $this->parseCsvIntelligent($file);
        
        // Detect location columns (case-insensitive)
        $locationConfig = $this->detectLocationColumns($csvData['headers']);
        
        if (!$locationConfig['has_required']) {
            throw new \InvalidArgumentException(
                'CSV must contain MunName and BrgyName columns (case-insensitive). ' .
                'Found columns: ' . implode(', ', $csvData['headers'])
            );
        }
        
        // Process CSV with dynamic metadata handling
        $result = $this->processCsvUniversal($csvData, $request, $adminCategoryId, $locationConfig);
        
        $message = sprintf(
            '✅ CSV Import Successful! | Created: %d | Updated: %d | Locations: %d | Metadata Fields: %d | Rows Processed: %d',
            $result['created'],
            $result['updated'],
            $result['locations_processed'],
            $result['metadata_fields_count'],
            $result['total_rows_processed']
        );
        
        Log::info($message, ['user_id' => auth()->id(), 'category_id' => $adminCategoryId]);
        
        return redirect()->route('admin.dashboard')->with('success', $message);
        
    } catch (\Exception $e) {
        Log::error('CSV Import Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->id(),
            'category_id' => $adminCategoryId
        ]);
        
        return back()->withErrors(['file' => 'Import Failed: ' . $e->getMessage()]);
    }
}

/**
 * Intelligent CSV parser with auto-detection of delimiter, encoding, and header format
 */
private function parseCsvIntelligent($file): array
{
    // Detect file encoding
    $content = file_get_contents($file->getRealPath());
    $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
    
    if ($encoding !== 'UTF-8') {
        $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        file_put_contents($file->getRealPath(), $content);
    }
    
    $handle = fopen($file->getRealPath(), 'r');
    if ($handle === false) {
        throw new \RuntimeException('Unable to open CSV file');
    }
    
    // Read first few lines to detect delimiter
    $firstLines = [];
    for ($i = 0; $i < 5; $i++) {
        $line = fgets($handle);
        if ($line !== false) {
            $firstLines[] = $line;
        }
    }
    rewind($handle);
    
    $delimiter = $this->detectDelimiter(implode('', $firstLines));
    
    // Read headers
    $headers = fgetcsv($handle, 0, $delimiter, '"', '\\');
    if ($headers === false) {
        fclose($handle);
        throw new \RuntimeException('CSV file is empty or has invalid format');
    }
    
    // Clean headers: trim, remove BOM, normalize
    $headers = array_map(function($header) {
        $header = trim($header);
        // Remove BOM if present
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
        return $header;
    }, $headers);
    
    // Remove empty headers and remember their indices
    $validHeaders = [];
    $validIndices = [];
    foreach ($headers as $index => $header) {
        if (!empty($header)) {
            $validHeaders[] = $header;
            $validIndices[] = $index;
        }
    }
    
    if (empty($validHeaders)) {
        fclose($handle);
        throw new \RuntimeException('No valid headers found in CSV');
    }
    
    // Read data rows (memory-efficient streaming)
    $data = [];
    $rowCount = 0;
    $maxRows = 100000;
    $emptyRowCount = 0;
    
    while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
        if ($rowCount >= $maxRows) {
            throw new \RuntimeException("CSV exceeds maximum limit of {$maxRows} rows");
        }
        
        // Filter row to only include valid header indices
        $filteredRow = [];
        foreach ($validIndices as $idx => $headerIndex) {
            $filteredRow[$validHeaders[$idx]] = isset($row[$headerIndex]) ? trim($row[$headerIndex]) : '';
        }
        
        // Check if row is empty
        $isEmpty = true;
        foreach ($filteredRow as $value) {
            if ($value !== '') {
                $isEmpty = false;
                break;
            }
        }
        
        if ($isEmpty) {
            $emptyRowCount++;
            continue;
        }
        
        $data[] = $filteredRow;
        $rowCount++;
    }
    
    fclose($handle);
    
    if (empty($data)) {
        throw new \RuntimeException("No valid data rows found in CSV. Empty rows skipped: {$emptyRowCount}");
    }
    
    return [
        'headers' => $validHeaders,
        'rows' => $data,
        'total_rows' => $rowCount,
        'delimiter' => $delimiter,
        'encoding' => $encoding,
        'empty_rows_skipped' => $emptyRowCount
    ];
}

/**
 * Detect CSV delimiter by analyzing sample content
 */
private function detectDelimiter(string $sample): string
{
    $delimiters = [',', "\t", ';', '|', ':'];
    $results = [];
    
    foreach ($delimiters as $delimiter) {
        $lines = explode("\n", $sample);
        $count = 0;
        
        foreach ($lines as $line) {
            $count += substr_count($line, $delimiter);
        }
        
        $results[$delimiter] = $count;
    }
    
    // Return delimiter with highest count, default to comma
    $maxCount = max($results);
    if ($maxCount > 0) {
        return array_search($maxCount, $results);
    }
    
    return ',';
}

/**
 * Detect location columns with case-insensitive matching
 */
private function detectLocationColumns(array $headers): array
{
    $locationMappings = [
        'munname' => ['munname', 'municity', 'municipality', 'city', 'city_name', 'municipality_name', 'mun_city'],
        'brgyname' => ['brgyname', 'barangay', 'brgy', 'barangay_name', 'village', 'district'],
        'districtname' => ['districtname', 'district', 'district_name', 'province_district']
    ];
    
    $detected = [
        'munname' => null,
        'brgyname' => null,
        'districtname' => null,
        'has_required' => false
    ];
    
    foreach ($headers as $header) {
        $headerLower = strtolower(trim($header));
        
        foreach ($locationMappings as $key => $patterns) {
            if ($detected[$key] === null && in_array($headerLower, $patterns)) {
                $detected[$key] = $header;
            }
        }
    }
    
    // Check if required columns exist
    $detected['has_required'] = ($detected['munname'] !== null && $detected['brgyname'] !== null);
    
    return $detected;
}

/**
 * Universal CSV processor - handles ANY CSV structure
 */
private function processCsvUniversal(array $csvData, $request, int $adminCategoryId, array $locationConfig): array
{
    // Get metadata columns (all non-location columns)
    $locationColumns = array_filter($locationConfig);
    unset($locationColumns['has_required']);
    
    $metadataColumns = array_values(array_diff($csvData['headers'], array_values($locationColumns)));
    
    // Analyze metadata columns to determine best aggregation strategy
    $columnAnalysis = $this->analyzeMetadataColumns($csvData['rows'], $metadataColumns);
    
    // Group rows by location
    $groupedData = $this->groupRowsByLocationUniversal($csvData['rows'], $locationColumns);
    
    $stats = [
        'created' => 0,
        'updated' => 0,
        'locations_processed' => 0,
        'metadata_fields_count' => count($metadataColumns),
        'total_rows_processed' => count($csvData['rows'])
    ];
    
    DB::transaction(function () use ($groupedData, $request, $adminCategoryId, $metadataColumns, $columnAnalysis, &$stats) {
        foreach ($groupedData as $locationKey => $locationGroup) {
            // Find location using available hierarchy
            $location = $this->findLocationUniversal(
                $locationGroup['district'] ?? null,
                $locationGroup['municity'],
                $locationGroup['brgy']
            );
            
            if (!$location) {
                Log::warning("Location not found in database: {$locationKey}");
                continue;
            }
            
            // Aggregate metadata intelligently
            $aggregatedMetadata = $this->aggregateMetadataUniversal(
                $locationGroup['rows'],
                $metadataColumns,
                $columnAnalysis,
                $request->get('aggregation_method', 'auto')
            );
            
            // Create or update feature
            $this->upsertFeatureUniversal(
                $location,
                $request,
                $adminCategoryId,
                $aggregatedMetadata,
                $stats
            );
            
            $stats['locations_processed']++;
        }
    });
    
    return $stats;
}

/**
 * Analyze metadata columns to determine data types and aggregation strategies
 */
private function analyzeMetadataColumns(array $rows, array $metadataColumns): array
{
    $analysis = [];
    
    foreach ($metadataColumns as $column) {
        $sampleValues = [];
        $valueCount = 0;
        
        // Collect sample values
        foreach ($rows as $row) {
            if (isset($row[$column]) && $row[$column] !== '') {
                $value = trim($row[$column]);
                $sampleValues[] = $value;
                $valueCount++;
                
                if (count($sampleValues) >= 50) break; // Sample up to 50 values
            }
        }
        
        if (empty($sampleValues)) {
            $analysis[$column] = ['type' => 'empty', 'strategy' => 'ignore'];
            continue;
        }
        
        // Detect data type
        $type = $this->detectDataTypeUniversal($sampleValues);
        
        // Determine best aggregation strategy
        $strategy = $this->determineAggregationStrategy($sampleValues, $type);
        
        // Check for uniqueness
        $uniqueValues = array_unique($sampleValues);
        $isHighlyUnique = (count($uniqueValues) / count($sampleValues)) > 0.8;
        
        $analysis[$column] = [
            'type' => $type,
            'strategy' => $strategy,
            'is_highly_unique' => $isHighlyUnique,
            'unique_count' => count($uniqueValues),
            'sample_count' => count($sampleValues),
            'total_count' => $valueCount
        ];
    }
    
    return $analysis;
}

/**
 * Detect data type from sample values
 */
private function detectDataTypeUniversal(array $samples): string
{
    $numericCount = 0;
    $dateCount = 0;
    $booleanCount = 0;
    $emailCount = 0;
    $urlCount = 0;
    
    foreach ($samples as $value) {
        // Check numeric (including currency)
        $cleaned = preg_replace('/[₱$€£,\s]/', '', $value);
        if (is_numeric($cleaned)) {
            $numericCount++;
            continue;
        }
        
        // Check date
        if (strtotime($value) !== false) {
            $dateCount++;
            continue;
        }
        
        // Check boolean
        if (in_array(strtolower($value), ['yes', 'no', 'true', 'false', '1', '0', 'on', 'off'])) {
            $booleanCount++;
            continue;
        }
        
        // Check email
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $emailCount++;
            continue;
        }
        
        // Check URL
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $urlCount++;
            continue;
        }
    }
    
    $total = count($samples);
    
    if ($numericCount / $total > 0.7) return 'numeric';
    if ($dateCount / $total > 0.7) return 'date';
    if ($booleanCount / $total > 0.7) return 'boolean';
    if ($emailCount / $total > 0.7) return 'email';
    if ($urlCount / $total > 0.7) return 'url';
    
    return 'text';
}

/**
 * Determine best aggregation strategy based on data analysis
 */
private function determineAggregationStrategy(array $samples, string $type): string
{
    $uniqueRatio = count(array_unique($samples)) / count($samples);
    
    switch ($type) {
        case 'numeric':
            return 'sum';
            
        case 'date':
            return 'range'; // Store min and max dates
            
        case 'boolean':
            return 'count_boolean';
            
        case 'email':
        case 'url':
            return $uniqueRatio > 0.9 ? 'collect_unique' : 'collect_all';
            
        case 'text':
            if ($uniqueRatio > 0.9) {
                return 'collect_unique'; // Store as JSON array
            } elseif ($uniqueRatio > 0.5) {
                return 'count_unique'; // Store count of unique values
            } else {
                return 'categorize'; // Count occurrences of each value
            }
            
        default:
            return 'count';
    }
}

/**
 * Group rows by location with flexible column mapping
 */
private function groupRowsByLocationUniversal(array $rows, array $locationColumns): array
{
    $grouped = [];
    
    foreach ($rows as $rowIndex => $row) {
        // Extract location values with fallbacks
        $municity = $this->extractLocationValue($row, $locationColumns['munname'] ?? null);
        $brgy = $this->extractLocationValue($row, $locationColumns['brgyname'] ?? null);
        $district = $this->extractLocationValue($row, $locationColumns['districtname'] ?? null);
        
        // Skip rows missing required location data
        if (empty($municity) || empty($brgy)) {
            Log::warning("Row {$rowIndex} skipped: missing MunName or BrgyName");
            continue;
        }
        
        // Normalize location names
        $municity = $this->normalizeLocationName($municity);
        $brgy = $this->normalizeLocationName($brgy);
        $district = $district ? $this->normalizeLocationName($district) : null;
        
        // Create location key
        $locationKey = implode('|', array_filter([$district, $municity, $brgy]));
        
        if (!isset($grouped[$locationKey])) {
            $grouped[$locationKey] = [
                'district' => $district,
                'municity' => $municity,
                'brgy' => $brgy,
                'rows' => []
            ];
        }
        
        // Store metadata row (exclude location columns)
        $metadataRow = [];
        foreach ($row as $header => $value) {
            if (!in_array($header, array_values($locationColumns))) {
                $metadataRow[$header] = $value;
            }
        }
        
        $grouped[$locationKey]['rows'][] = $metadataRow;
    }
    
    return $grouped;
}

/**
 * Extract location value with null safety
 */
private function extractLocationValue(array $row, ?string $column): ?string
{
    if ($column === null) return null;
    
    $value = $row[$column] ?? null;
    return !empty($value) ? trim($value) : null;
}

/**
 * Normalize location names for consistent matching
 */
private function normalizeLocationName(string $name): string
{
    // Remove extra spaces
    $name = preg_replace('/\s+/', ' ', trim($name));
    
    // Capitalize properly
    $name = ucwords(strtolower($name));
    
    // Remove common suffixes for better matching
    $name = preg_replace('/\s+(Barangay|Brgy\.?|Purok|Sitio)$/i', '', $name);
    $name = preg_replace('/^(Barangay|Brgy\.?)\s+/i', '', $name);
    
    return $name;
}

/**
 * Find location using flexible matching hierarchy
 */
private function findLocationUniversal(?string $district, string $municity, string $brgy): ?DefaultLocation
{
    $query = DefaultLocation::where('municity', $municity)
        ->where('brgy', $brgy)
        ->whereNotNull('geometry');
    
    // Try with district first
    if (!empty($district)) {
        $location = (clone $query)->where('district', $district)->first();
        if ($location) return $location;
    }
    
    // Try without district
    $location = $query->first();
    if ($location) return $location;
    
    // Try fuzzy matching as last resort
    $location = DefaultLocation::where('municity', 'LIKE', '%' . $municity . '%')
        ->where('brgy', 'LIKE', '%' . $brgy . '%')
        ->whereNotNull('geometry')
        ->first();
    
    return $location;
}

/**
 * Universal metadata aggregator - handles any data type
 */
private function aggregateMetadataUniversal(array $rows, array $metadataColumns, array $columnAnalysis, string $globalStrategy = 'auto'): array
{
    if (empty($rows) || empty($metadataColumns)) {
        return [];
    }
    
    $result = [];
    
    foreach ($metadataColumns as $column) {
        // Collect all values for this column
        $values = [];
        foreach ($rows as $row) {
            if (isset($row[$column]) && $row[$column] !== '' && $row[$column] !== null) {
                $values[] = trim($row[$column]);
            }
        }
        
        if (empty($values)) {
            continue;
        }
        
        // Get analysis or use default
        $analysis = $columnAnalysis[$column] ?? ['type' => 'text', 'strategy' => 'count'];
        $strategy = $globalStrategy !== 'auto' ? $globalStrategy : $analysis['strategy'];
        
        // Apply aggregation strategy
        $aggregated = $this->applyAggregationStrategy($values, $column, $strategy, $analysis);
        
        if (!empty($aggregated)) {
            $result = array_merge($result, $aggregated);
        }
    }
    
    return $result;
}

/**
 * Apply specific aggregation strategy
 */
private function applyAggregationStrategy(array $values, string $column, string $strategy, array $analysis): array
{
    switch ($strategy) {
        case 'sum':
            $sum = 0;
            foreach ($values as $value) {
                $cleaned = preg_replace('/[₱$€£,\s]/', '', $value);
                if (is_numeric($cleaned)) {
                    $sum += (float)$cleaned;
                }
            }
            return [$column => $sum];
            
        case 'count':
            return [$column => count($values)];
            
        case 'count_boolean':
            $trueCount = 0;
            $falseCount = 0;
            foreach ($values as $value) {
                $lower = strtolower($value);
                if (in_array($lower, ['yes', 'true', '1', 'on'])) {
                    $trueCount++;
                } else {
                    $falseCount++;
                }
            }
            return [
                $column . '_true' => $trueCount,
                $column . '_false' => $falseCount
            ];
            
        case 'range':
            $dates = array_filter($values, function($v) {
                return strtotime($v) !== false;
            });
            if (empty($dates)) return [$column . '_count' => count($values)];
            
            $min = min($dates);
            $max = max($dates);
            return [
                $column . '_min' => $min,
                $column . '_max' => $max,
                $column . '_count' => count($values)
            ];
            
        case 'collect_unique':
            $unique = array_unique($values);
            if (count($unique) <= 20) {
                // Store as JSON for smaller datasets
                return [$column => json_encode(array_values($unique))];
            } else {
                // Just store count for large datasets
                return [$column . '_unique_count' => count($unique)];
            }
            
        case 'collect_all':
            if (count($values) <= 50) {
                return [$column => json_encode($values)];
            } else {
                return [$column . '_count' => count($values)];
            }
            
        case 'count_unique':
            return [$column . '_unique_count' => count(array_unique($values))];
            
        case 'categorize':
            $counts = array_count_values($values);
            $result = [];
            foreach ($counts as $category => $count) {
                if ($count > 0) {
                    $key = $column . '_' . strtolower(preg_replace('/[^a-z0-9]/i', '_', $category));
                    $key = substr($key, 0, 64); // Limit key length
                    $result[$key] = $count;
                }
            }
            return $result;
            
        case 'concat':
            $unique = array_unique($values);
            if (count($unique) <= 10) {
                return [$column => implode(', ', $unique)];
            } else {
                return [$column . '_count' => count($values)];
            }
            
        case 'last':
            return [$column => end($values)];
            
        case 'first':
            return [$column => reset($values)];
            
        default:
            return [$column => count($values)];
    }
}

/**
 * Create or update feature with metadata
 */
private function upsertFeatureUniversal(
    DefaultLocation $location,
    $request,
    int $adminCategoryId,
    array $aggregatedMetadata,
    array &$stats
): void {
    // Find existing feature
    $existingFeature = FeatureModel::where('default_location_id', $location->id)
        ->where('classification_id', $request->classification_id)
        ->whereHas('shapefile', function ($query) use ($adminCategoryId) {
            $query->where('category_id', $adminCategoryId);
        })
        ->first();
    
    if ($existingFeature) {
        $this->updateFeatureUniversal($existingFeature, $request, $aggregatedMetadata);
        $stats['updated']++;
    } else {
        $this->createFeatureUniversal($location, $request, $adminCategoryId, $aggregatedMetadata);
        $stats['created']++;
    }
}

/**
 * Create new feature with metadata
 */
private function createFeatureUniversal(
    DefaultLocation $location,
    $request,
    int $adminCategoryId,
    array $aggregatedMetadata
): FeatureModel {
    $userId = auth()->id();
    
    // Find or create shapefile
    $shapefile = Shapefile::firstOrCreate(
        ['category_id' => $adminCategoryId],
        [
            'user_id' => $userId,
            'created_by' => $userId,
            'name' => 'Imported Data - ' . now()->format('Y-m-d H:i:s')
        ]
    );
    
    // Create feature
    $feature = $shapefile->features()->create([
        'geometry' => $location->geometry,
        'feature_no' => $shapefile->features()->count() + 1,
        'classification_id' => $request->classification_id,
        'survey_date' => $request->survey_date,
        'description' => $request->description,
        'default_location_id' => $location->id,
        'created_by' => $userId,
        'visibility' => $request->visibility,
    ]);
    
    // Store metadata
    foreach ($aggregatedMetadata as $key => $value) {
        if (!empty($key) && $value !== null && $value !== '' && $value !== 0) {
            $feature->metadata()->create([
                'meta_key' => $key,
                'meta_value' => is_array($value) ? json_encode($value) : (string)$value,
            ]);
        }
    }
    
    Log::info("Created feature ID: {$feature->id} at location: {$location->id}");
    
    return $feature;
}

/**
 * Update existing feature with metadata
 */
private function updateFeatureUniversal(
    FeatureModel $feature,
    $request,
    array $aggregatedMetadata
): void {
    // Update basic info
    $feature->update([
        'survey_date' => $request->survey_date,
        'description' => $request->description,
    ]);
    
    // Update metadata (merge intelligent)
    foreach ($aggregatedMetadata as $key => $value) {
        if (empty($key) || $value === null || $value === '') {
            continue;
        }
        
        $existingMeta = $feature->metadata()->where('meta_key', $key)->first();
        
        if ($existingMeta) {
            // Intelligent merging based on value type
            $existingValue = $existingMeta->meta_value;
            
            // Try to decode JSON
            $existingDecoded = json_decode($existingValue, true);
            $isJson = ($existingDecoded !== null && is_array($existingDecoded));
            
            if ($isJson && is_array($value)) {
                // Merge arrays
                $merged = array_unique(array_merge($existingDecoded, $value));
                $newValue = json_encode($merged);
            } elseif (is_numeric($value) && is_numeric($existingValue)) {
                // Sum numeric values
                $newValue = (string)((float)$existingValue + (float)$value);
            } else {
                // Replace with new value
                $newValue = is_array($value) ? json_encode($value) : (string)$value;
            }
            
            $existingMeta->update(['meta_value' => $newValue]);
        } else {
            $feature->metadata()->create([
                'meta_key' => $key,
                'meta_value' => is_array($value) ? json_encode($value) : (string)$value,
            ]);
        }
    }
    
    Log::info("Updated feature ID: {$feature->id}");
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
