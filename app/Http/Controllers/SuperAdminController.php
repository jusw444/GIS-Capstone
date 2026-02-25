<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\OfficeModule;
use App\Models\Shapefile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    public function dashboard(Request $request)
{
    $totalAdmins = User::where('role', 'admin')->count();
    $totalUsers = User::where('role', 'user')->count();
    $totalShapefiles = Shapefile::count();
    $totalUploadedShapefiles = OfficeModule::count();

    // SAME QUERY
    $query = Shapefile::with('features.metadata')
                ->select('id', 'category')
                ->with(['features' => function ($q) {
                    $q->select('id', 'shapefile_id', 'feature_no', DB::raw('ST_AsGeoJSON(geometry) as geometry'));
                }]);
    $shapefiles = $query->get();
    // SAME STRUCTURE
    $geojson = $shapefiles->flatMap(function ($shapefile) {
            return $shapefile->features->map(function ($feature) use ($shapefile) {
                return [
                    'shapefile_id' => $shapefile->id,
                    'category'     => $shapefile->category,
                    'geometry' => json_decode($feature->geometry), // convert GeoJSON string to JS object
                    'metadata'     => $feature->metadata->map(fn($m) => [
                        'meta_key'   => $m->meta_key,
                        'meta_value' => $m->meta_value,
                    ]),
                ];
            });
        });

    return view('superadmin.dashboard', compact(
        'totalAdmins',
        'totalUsers',
        'totalShapefiles',
        'totalUploadedShapefiles',
        'geojson'
    ));
}

    // Show create admin form
    public function createAccount()
    {
        return view('superadmin.create');
    }

    // Handle storing new admin
    public function storeAccount(StoreUserRequest $request)
    {
        DB::transaction(function () use ($request) {

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'category'=> $request->category,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);
        });

        return redirect()->route('superadmin.admins.create')->with('success', 'Account created successfully!');
    }

    // Show all users/admins
    public function allUsers()
    {
        $users = User::where('role', '!=', 'super_admin')->get();
        return view('superadmin.users', compact('users'));
    }
}
