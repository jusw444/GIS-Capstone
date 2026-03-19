<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ------------------------
// Public / Welcome
// ------------------------
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// ------------------------
// Super Admin Routes
// ------------------------
Route::middleware(['auth', 'role:super_admin'])->prefix('superadmin')->group(function () {

    // Dashboard
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');

    // Create Admin/User accounts
    Route::get('/admins/create', [SuperAdminController::class, 'createAccount'])->name('superadmin.admins.create');
    Route::post('/admins/store', [SuperAdminController::class, 'storeAccount'])->name('superadmin.admins.store');
    Route::post('/categories/store-ajax', [SuperAdminController::class, 'storeCategory'])->name('superadmin.categories.store.ajax');

    // Classification management
    Route::get('/classifications', [SuperAdminController::class, 'createClassifications'])->name('superadmin.classifications');
    Route::post('/classifications/store', [SuperAdminController::class, 'storeClassification'])->name('superadmin.classifications.store');
    Route::post('/classifications/{id}/update', [SuperAdminController::class, 'updateClassification'])->name('superadmin.classifications.update');
    Route::delete('/classifications/{id}/delete', [SuperAdminController::class, 'deleteClassification'])->name('superadmin.classifications.delete');

    // List all Users/Admins
    Route::get('/users', [SuperAdminController::class, 'allUsers'])->name('superadmin.users');
});


Route::middleware(['auth', 'role:super_admin,admin'])->group(function () {});


// ------------------------
// Admin Routes
// ------------------------
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Map view
    Route::get('/map', [AdminController::class, 'mapview'])->name('admin.view');

    // Upload shapefile
    Route::get('/uploads/shapefile', [AdminController::class, 'uploadGeoJson'])->name('admin.shapefile.upload');
    Route::post('/uploads/shapefile', [AdminController::class, 'storeGeoJson'])->name('admin.geojson.store');

    // CRUD Shapefiles
    Route::get('/shapefiles/create', [AdminController::class, 'create'])->name('shapefiles.create');
    Route::post('/shapefiles', [AdminController::class, 'store'])->name('shapefiles.store');
    Route::get('/features/{id}/edit', [AdminController::class, 'edit'])->name('shapefiles.edit');
    Route::put('/features/{id}', [AdminController::class, 'update'])->name('shapefiles.update');

    // Soft delete / restore
    Route::delete('/features/{id}', [AdminController::class, 'destroy'])->name('shapefiles.destroy');
    Route::post('/features/{id}/restore', [AdminController::class, 'restore'])->name('shapefiles.restore');
});

// ------------------------
// User Routes
// ------------------------
Route::middleware(['auth', 'role:user'])->prefix('user')->group(function () {
    Route::get('/dashboard', [UserController::class, 'index'])->name('user.dashboard');
});
