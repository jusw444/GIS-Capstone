<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Classification;
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
