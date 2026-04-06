<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\Category;
use App\Models\Classification;
use App\Models\FeatureModel;
use App\Models\OfficeModule;
use App\Models\Shapefile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        $recentActivities = FeatureModel::with([
                'creator',
                'updater',
                'shapefile.category',
                'classification'
            ])
                ->withTrashed()
                ->where('updated_at', '>=', Carbon::now()->subDays(7))
                ->latest('updated_at')
                ->take(25)
                ->get()
                ->map(function ($feature) {

                    if ($feature->trashed()) {
                        $action = 'Deleted';
                        $user   = $feature->updater ?? $feature->creator;
                        $actionColor = 'red';
                    } elseif ($feature->created_at->eq($feature->updated_at)) {
                        $action = 'Created';
                        $user   = $feature->creator;
                        $actionColor = 'green';
                    } else {
                        $action = 'Updated';
                        $user   = $feature->updater;
                        $actionColor = 'blue';
                    }

                    return (object)[
                        'classification_name' => $feature->classification->name ?? 'No Classification',
                        'category_name'       => $feature->shapefile->category->name ?? 'No Category',
                        'action'              => $action,
                        'user_name'           => $user->name ?? 'Unknown',
                        'category_color'      => $feature->classification->color ?? '#6c757d',
                        'created_at'          => $feature->updated_at,
                        'location'            => $feature->location,
                        'action_color'         => $actionColor,
                    ];
                });
        
        return view('superadmin.dashboard', compact(
            'totalAdmins',
            'totalUsers',
            'totalShapefiles',
            'totalUploadedShapefiles',
            'geojson',
            'page',
            'recentActivities'
        ));
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

    public function storeDefault()
    {

    }
}
