<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\Category;
use App\Models\Classification;
use App\Models\OfficeModule;
use App\Models\Shapefile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    // SuperAdmin Dashboard
    public function dashboard(Request $request)
    {
        $page = [
            'pageTitle' => 'SuperAdmin Dashboard',
            'pageName'  => 'Super Admin Dashboard',
        ];

        $totalAdmins = User::where('role', 'admin')->count();
        $totalUsers = User::where('role', 'user')->count();
        $totalShapefiles = Shapefile::count();
        $totalUploadedShapefiles = OfficeModule::count();

        // Load shapefiles with features and metadata
        $shapefiles = Shapefile::with(['features.metadata'])->get();

        // Build GeoJSON-ready structure
        $geojson = $shapefiles->flatMap(function ($shapefile) {
            return $shapefile->features->map(fn($feature) => [
                'shapefile_id' => $shapefile->id,
                'category'     => $shapefile->category,
                'feature_no'   => $feature->feature_no,
                'metadata'     => $feature->metadata,
                'geometry'     => $feature->geometry, // Auto accessor
            ]);
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

    // Show form to create admin/user
    public function createAccount()
    {
        $page = [
            'pageTitle' => 'Create Account',
            'pageName'  => 'Create Admin/User Account',
        ];

        // Get all categories from DB
        $categories = Category::orderBy('name')->get();

        return view('superadmin.create', compact('page', 'categories'));
    }

    // Store new admin/user
    public function storeAccount(StoreUserRequest $request)
    {
        DB::transaction(function () use ($request) {
            User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => $request->role,
                'category_id' => $request->role === 'admin' ? $request->category : null,
            ]);
        });

        return redirect()
            ->route('superadmin.admins.create')
            ->with('success', 'Account created successfully!');
    }

    // Create classifications
    public function createClassifications()
{
    $categories = Category::orderBy('name')->get();
    $classifications = Classification::with('category')->orderBy('category_id')->get();

    return view('superadmin.classifications', compact('categories', 'classifications'));
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
            'color' => $request->color
        ]);

        return redirect()->back()->with('success', 'Classification added successfully.');
    }

    // Store a new category dynamically
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name',
        ]);

        $category = Category::create([
            'name' => $request->name,
        ]);

        // Return JSON response for AJAX
        return response()->json([
            'success' => true,
            'category' => $category->name,
            'id' => $category->id,
        ]);
    }

    // List all users/admins (excluding super admin)
    public function allUsers()
    {
        $users = User::where('role', '!=', 'super_admin')->get();
        return view('superadmin.users', compact('users'));
    }
}
