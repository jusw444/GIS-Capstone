<?php

namespace App\Http\Controllers;

use App\Models\DefaultLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DefaultLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     * Now shows boundary management instead of upload
     */
    // public function index()
    // {
    //     $page = [
    //         'pageTitle' => 'Manage Laguna Boundaries',
    //         'pageName'  => 'Province Boundaries Management',
    //     ];
        
    //     $stats = [
    //         'total' => DefaultLocation::count(),
    //         'districts' => DefaultLocation::whereNotNull('district')->distinct('district')->count('district'),
    //         'municities' => DefaultLocation::whereNotNull('municity')->distinct('municity')->count('municity'),
    //         'barangays' => DefaultLocation::whereNotNull('brgy')->distinct('brgy')->count('brgy'),
    //     ];
        
    //     return view('superadmin.boundaries', compact('page', 'stats'));
    // }

    /**
     * Get boundary statistics via API
     */
    public function getStats()
    {
        return response()->json([
            'total' => DefaultLocation::count(),
            'districts' => DefaultLocation::whereNotNull('district')->distinct('district')->count('district'),
            'municities' => DefaultLocation::whereNotNull('municity')->distinct('municity')->count('municity'),
            'barangays' => DefaultLocation::whereNotNull('brgy')->distinct('brgy')->count('brgy'),
        ]);
    }

    /**
     * Get all boundaries as GeoJSON
     */
    public function getGeoJson()
    {
        $boundaries = DefaultLocation::all();
        
        $features = [];
        foreach ($boundaries as $boundary) {
            // Convert MySQL geometry back to GeoJSON
            $geometry = DB::selectOne("SELECT ST_AsGeoJSON(geometry) as geojson FROM default_locations WHERE id = ?", [$boundary->id]);
            
            $features[] = [
                'type' => 'Feature',
                'geometry' => json_decode($geometry->geojson),
                'properties' => [
                    'id' => $boundary->id,
                    'district' => $boundary->district,
                    'municity' => $boundary->municity,
                    'brgy' => $boundary->brgy,
                ]
            ];
        }
        
        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features
        ]);
    }

    /**
     * Get districts list for filters
     */
    public function getDistricts()
    {
        $districts = DefaultLocation::whereNotNull('district')
            ->select('district')
            ->distinct()
            ->orderBy('district')
            ->pluck('district');
            
        return response()->json($districts);
    }

    /**
     * Get municipalities by district
     */
    public function getMunicity($district)
{
    $municities = DefaultLocation::where('district', $district)
        ->whereNotNull('municity')
        ->select('municity')
        ->distinct()
        ->orderBy('municity')
        ->pluck('municity');

    return response()->json($municities);
}

public function getBrgy($municity)
{
    $barangays = DefaultLocation::where('municity', $municity)
        ->whereNotNull('brgy')
        ->select('brgy')
        ->distinct()
        ->orderBy('brgy')
        ->pluck('brgy');

    return response()->json($barangays);
}

    /**
     * Check if boundaries exist (for API health check)
     */
    public function checkStatus()
    {
        $count = DefaultLocation::count();
        
        return response()->json([
            'boundaries_loaded' => $count > 0,
            'total_features' => $count,
            'status' => $count > 0 ? 'ready' : 'missing'
        ]);
    }
}