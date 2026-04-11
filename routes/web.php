<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DefaultLocationController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// ------------------------
// Public / Welcome
// ------------------------
// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [UserController::class, 'mapHome']);

Auth::routes();

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->name('logout');

Route::get('/api/districts', [DefaultLocationController::class, 'getDistricts']);
Route::get('/api/municities/{district?}', [DefaultLocationController::class, 'getMunicity']);
Route::get('/api/barangays/{municity?}', [DefaultLocationController::class, 'getBrgy']);

// ------------------------
// Super Admin Routes
// ------------------------
Route::middleware(['auth', 'prevent-back-history', 'role:super_admin'])->prefix('superadmin')->group(function () {

    // Dashboard
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');

    // Create Admin/User accounts
    Route::get('/admins/create', [SuperAdminController::class, 'createAccount'])->name('superadmin.admins.create');
    Route::post('/admins/store', [SuperAdminController::class, 'storeAccount'])->name('superadmin.admins.store');
    Route::post('/categories/store-ajax', [SuperAdminController::class, 'storeCategory'])->name('superadmin.categories.store.ajax');

    // Upload Default Location
    Route::get('/uploads/default', [DefaultLocationController::class, 'index'])->name('superadmin.upload');
    Route::post('/uploads/default', [DefaultLocationController::class, 'store'])->name('superadmin.store');

    // List all Users/Admins
    Route::get('/users', [SuperAdminController::class, 'allUsers'])->name('superadmin.users');

    Route::get('/superadmin/users/{user}/edit', [SuperAdminController::class, 'editUser'])
        ->name('superadmin.users.edit');

    Route::put('/superadmin/users/{user}', [SuperAdminController::class, 'updateUser'])
        ->name('superadmin.users.update');

    Route::delete('/superadmin/users/{user}', [SuperAdminController::class, 'destroyUser'])
        ->name('superadmin.users.destroy');

    Route::put('/superadmin/users/{id}/restore', [SuperAdminController::class, 'restoreUser'])
        ->name('superadmin.users.restore');

    Route::delete('/superadmin/users/{id}/force-delete', [SuperAdminController::class, 'forceDeleteUser'])
        ->name('superadmin.users.forceDelete');
});


Route::middleware(['auth', 'prevent-back-history', 'role:super_admin,admin'])->group(function () {

    // Map view
    Route::get('/map', [UserController::class, 'mapview'])->name('admin.view');

    // Boundary management
    Route::get('/api/boundaries/stats', [DefaultLocationController::class, 'getStats']);
    Route::get('/api/boundaries/geojson', [DefaultLocationController::class, 'getGeoJson']);
    Route::get('/api/boundaries/check', [DefaultLocationController::class, 'checkStatus']);

    // Classification management
    Route::get('/classifications', [UserController::class, 'createClassifications'])->name('classifications');
    Route::post('/classifications/store', [UserController::class, 'storeClassification'])->name('classifications.store');
    // Edit classification
    Route::get('/classifications/{classification}/edit', [UserController::class, 'editClassification'])->name('classifications.edit');
    // Update classification
    Route::put('/classifications/{classification}', [UserController::class, 'updateClassification'])->name('classifications.update');
    // Soft delete
    Route::delete('/classifications/{classification}', [UserController::class, 'destroyClassification'])->name('classifications.destroy');
    // Restore
    Route::put('/classifications/{id}/restore', [UserController::class, 'restoreClassification'])->name('classifications.restore');
    // Force delete
    Route::delete('/classifications/{id}/force-delete', [UserController::class, 'forceDeleteClassification'])->name('classifications.forceDelete');
});


// ------------------------
// Admin Routes
// ------------------------
Route::middleware(['auth', 'prevent-back-history', 'role:admin'])->prefix('admin')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Profile
    Route::put('/admin/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])
        ->name('password.update');


    // Upload shapefile
    Route::get('/uploads/shapefile', [AdminController::class, 'uploadGeoJson'])->name('admin.shapefile.upload');
    Route::post('/uploads/shapefile', [AdminController::class, 'storeGeoJson'])->name('admin.geojson.store');

    // CRUD Shapefiles
    Route::get('/shapefiles/create', [AdminController::class, 'create'])->name('shapefiles.create');
    Route::post('/shapefiles', [AdminController::class, 'store'])->name('shapefiles.store');
    Route::get('/shapefiles/{id}/edit', [AdminController::class, 'edit'])->name('shapefiles.edit');
    Route::put('/shapefiles/{id}', [AdminController::class, 'update'])->name('shapefiles.update');

    // Soft delete / restore
    Route::delete('/features/{id}', [AdminController::class, 'destroy'])->name('shapefiles.destroy');
    Route::post('/features/{id}/restore', [AdminController::class, 'restore'])->name('shapefiles.restore');
    Route::get('/get-municity/{district}', [DefaultLocationController::class, 'getMunicity']);
    Route::get('/get-brgy/{municity}', [DefaultLocationController::class, 'getBrgy']);
});

// ------------------------
// User Routes
// ------------------------
Route::middleware(['auth', 'prevent-back-history', 'role:user'])->prefix('user')->group(function () {
    Route::get('/dashboard', [UserController::class, 'index'])->name('user.dashboard');
});
