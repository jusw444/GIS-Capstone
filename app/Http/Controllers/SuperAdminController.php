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


    //
    //
    // Create classifications
    //
    //
    public function createClassifications()
    {
    $categories = Category::orderBy('name')->get();
    $classifications = Classification::withTrashed()
        ->with('category')
        ->orderBy('category_id')
        ->get();
    $trashedClassifications = Classification::onlyTrashed()->with('category')->get();
    return view('superadmin.classifications', compact('categories', 'classifications', 'trashedClassifications'));
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
     // Show form to edit a classification
    public function editClassification(Classification $classification)
    {
        $page = [
            'pageTitle' => 'Edit Classification',
        ];
        $categories = Category::orderBy('name')->get();

        return view('superadmin.edit_classifications', compact('classification', 'categories', 'page'));
    }

    // Update the classification
    public function updateClassification(Request $request, Classification $classification)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'color' => 'nullable|string',
        ]);

        $classification->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'color' => $request->color,
        ]);

        return redirect()->route('superadmin.classifications')
                        ->with('success', 'Classification updated successfully.');
    }
    // Soft delete
    public function destroyClassification(Classification $classification)
    {
        $classification->delete();

        return redirect()->route('superadmin.classifications')
                        ->with('success', 'Classification moved to trash.');
    }

    // Restore soft deleted
    public function restoreClassification($id)
    {
        $classification = Classification::withTrashed()->findOrFail($id);
        $classification->restore();

        return redirect()->route('superadmin.classifications')
                        ->with('success', 'Classification restored successfully.');
    }

    // Permanent delete
    public function forceDeleteClassification($id)
    {
        $classification = Classification::withTrashed()->findOrFail($id);
        $classification->forceDelete();

        return redirect()->route('superadmin.classifications')
                        ->with('success', 'Classification permanently deleted.');
    }
    //
    //
    // Store a new category dynamically
    //
    //
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
    
    //
    //
    // List all users/admins (excluding super admin)
    //
    //

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
    
   
    public function allUsers()
    {
        $page = [
            'pageTitle' => 'Manage Accounts',
        ];
        $users = User::withTrashed()
        ->where('role', '!=', 'super_admin')
        ->get();

        return view('superadmin.users', compact('page','users'));
    }
    public function editUser(User $user)
    {
        $categories = Category::orderBy('name')->get();
        $page = [
            'pageTitle' => 'SuperAdmin Dashboard',
            'pageName'  => 'Super Admin Dashboard',
        ];

        return view('superadmin.edit_create', compact('user', 'categories'));
    }
    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'role' => 'required',
            'category' => 'nullable',
            'password' => 'nullable|confirmed|min:6',
        ]);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        } else {
            unset($data['password']);
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'category_id' => $data['role'] === 'admin' ? $data['category'] : null,
            ...($request->filled('password') ? ['password' => $data['password']] : [])
        ]);

        return back()->with('success', 'User updated successfully.');
    }
    // Soft delete
    public function destroyUser(User $user)
    {
        $user->delete();

        return back()->with('success', 'User moved to trash.');
    }

    // Restore
    public function restoreUser($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return back()->with('success', 'User restored.');
    }

    // Permanent delete
    public function forceDeleteUser($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->forceDelete();

        return back()->with('success', 'User permanently deleted.');
    }
}
