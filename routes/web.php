<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Auth\AdminController;
use App\Http\Controllers\Auth\PasienController;
use App\Http\Controllers\Auth\DokterController;
use App\Http\Controllers\Auth\PasienDashboardController;

// Landing Page
Route::get('/', [LandingPageController::class, 'index'])->name('landing');

Route::middleware('auth:pasien')->get('/dashboard/pasien', [PasienDashboardController::class, 'index']);


// Route lain yang mungkin ada
Route::get('/table', function () {
    return Inertia::render('PrimevueTable');
});

Route::get('/media', function () {
    return Inertia::render('PrimevueMedia');
});

Route::get('/chart', function () {
    return Inertia::render('PrimevueChart');
});

require __DIR__.'/auth.php';
