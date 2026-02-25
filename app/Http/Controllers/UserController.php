<?php

namespace App\Http\Controllers;

use App\Models\Shapefile;
use App\Models\User;
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
    public function create(){

        $page = [
            'pageTitle' => 'Create Shapefile',
            'pageName' => 'Create Shapefile',
        ];

        $categories = User::select('category')
                            ->distinct()
                            ->pluck('category');

        $user = auth()->user();

        $availableCategories = $user->role === 'super_admin' 
            ? $categories->toArray() 
            : [$user->category];
        
        return view('admin.create', compact('availableCategories', 'page'));
    }
    public function store(Request $request){
        $request->validate([
            'geometry' => 'required|json',
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

    public function uploadGeoJson()
        {
            $page = [
                'pageTitle' => 'Upload GeoJSON',
                'pageName'  => 'Upload GeoJSON File',
            ];
            $categories = User::select('category')
                            ->distinct()
                            ->pluck('category');

            $user = auth()->user();

            $availableCategories = $user->role === 'super_admin' 
                ? $categories->toArray() 
                : [$user->category];
            return view('admin.upload', compact('page', 'availableCategories'));
        }

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
        $user = Auth::user()->role;

            if ($user === 'super_admin') {
                $user = 'superadmin';
            }
        // 5️⃣ Cleanup extracted files
        $this->deleteDirectory($extractPath);

        return redirect()->route($user . '.dashboard')->with('success', 'GeoJSON ZIP uploaded successfully!');
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
}
