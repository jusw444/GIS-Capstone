<?php

namespace App\Http\Controllers;

use App\Models\DefaultLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DefaultLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page = [
            'pageTitle' => 'Upload Default Location',
            'pageName'  => 'Upload GeoJSON for Default Location',
        ];
        return view('superadmin.upload', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
        DB::transaction(function () use ($geoArray, $request) {

            // 1️⃣ Create shapefile

            if (isset($geoArray['features'])) {

                foreach ($geoArray['features'] as $index => $feature) {

                    $properties = $feature['properties'] ?? [];

                    // 2️⃣ Save each feature (ONE ROW PER FEATURE)

                    $properties = array_change_key_case($feature['properties'] ?? [], CASE_LOWER);

                    DefaultLocation::create([
                        'geometry' => DB::raw("ST_GeomFromGeoJSON('" . addslashes(json_encode($feature['geometry'])) . "')"),
                        'district' => $properties['district'] ?? null,
                        'municity' => $properties['location'] ?? null,
                        'brgy'     => $properties['brgy'] ?? null,
                    ]);
                }
            }
        });
        // 5️⃣ Cleanup extracted files
        $this->deleteDirectory($extractPath);

        return redirect()->route('superadmin.dashboard')->with('success', 'GeoJSON ZIP uploaded successfully!');
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
        public function getMunicity($district)
    {
        $municity = DefaultLocation::where('district', $district)
            ->select('municity')
            ->distinct()
            ->orderBy('municity')
            ->pluck('municity');

        return response()->json($municity);
    }

    public function getBrgy($municity)
    {
        $brgy = DefaultLocation::where('municity', $municity)
            ->select('brgy')
            ->distinct()
            ->orderBy('brgy')
            ->pluck('brgy');

        return response()->json($brgy);
    }

    /**
     * Display the specified resource.
     */
    public function show(DefaultLocation $defaultLocation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DefaultLocation $defaultLocation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DefaultLocation $defaultLocation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DefaultLocation $defaultLocation)
    {
        //
    }
}
