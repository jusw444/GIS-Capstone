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
    $page=[
        'pageTitle' => 'Dashboard',
        'pageName' => 'Super Admin Dashboard',
    ];

    $totalAdmins = User::where('role', 'admin')->count();
    $totalUsers = User::where('role', 'user')->count();
    $totalShapefiles = Shapefile::count();
    $totalUploadedShapefiles = OfficeModule::count();

    // SAME QUERY
    $shapefiles = Shapefile::with('metadata')
        ->select('id', 'category', 'user_id')
        ->selectRaw('ST_AsGeoJSON(geometry, 6) AS geometry')
        ->get();

    // SAME STRUCTURE
    $geojson = $shapefiles->map(function ($item) {
        return [
            'id' => $item->id,
            'category' => $item->category,
            'metadata' => $item->metadata->map(fn ($m) => [
                'meta_key' => $m->meta_key,
                'meta_value' => $m->meta_value
            ]),
            'geometry' => $item->geometry,
        ];
    });

    return view('superadmin.dashboard', compact(
        'totalAdmins',
        'totalUsers',
        'totalShapefiles',
        'totalUploadedShapefiles',
        'geojson',
        'page'
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
