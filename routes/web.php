<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware('auth', 'role:super_admin')->group(function () {
    Route::get('/superadmin/dashboard', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');

    // Create Admin
    Route::get('/superadmin/admins/create', [SuperAdminController::class, 'createAdmin'])->name('superadmin.admins.create');
    Route::post('/superadmin/admins/store', [SuperAdminController::class, 'storeAdmin'])->name('superadmin.admins.store');

    // All Users/Admins
    Route::get('/superadmin/users', [SuperAdminController::class, 'allUsers'])->name('superadmin.users');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    Route::get('admin/map', [AdminController::class, 'mapview'])->name('admin.view');

    // Create shapefile
    Route::get('/shapefiles/create', [AdminController::class, 'create'])->name('shapefiles.create');

    // Store shapefile + dynamic metadata
    Route::post('/shapefiles', [AdminController::class, 'store'])->name('shapefiles.store');

    Route::get('/admin/shapefiles/{id}/edit', [AdminController::class, 'edit'])->name('shapefiles.edit');
    Route::put('/admin/shapefiles/{id}', [AdminController::class, 'update'])->name('shapefiles.update');


    // Soft delete shapefile
    Route::delete('/shapefiles/{id}', [AdminController::class, 'destroy'])->name('shapefiles.destroy');

    // Restore soft-deleted shapefile
    Route::post('/shapefiles/{id}/restore', [AdminController::class, 'restore'])->name('shapefiles.restore');
});

