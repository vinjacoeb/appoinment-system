<?php

use App\Http\Controllers\Auth\AdminController;
use App\Http\Controllers\Auth\PasienController;
use App\Http\Controllers\Auth\DokterController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\PasienDashboardController;
use App\Http\Controllers\DaftarPoliController;

use Illuminate\Support\Facades\Route;

// Auth untuk Admin
Route::middleware('guest')->group(function () {
    Route::get('login/dokter', [DokterController::class, 'showLoginForm'])->name('dokter.login');
    Route::post('login/dokter', [DokterController::class, 'login']);
});

// Auth untuk Pasien
Route::middleware('guest')->group(function () {
    // Login Pasien
    Route::get('login/pasien', [PasienController::class, 'showLoginForm'])->name('pasien.login');
    Route::post('login/pasien', [PasienController::class, 'loginpasien']);

    // Register Pasien
    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);
});

// Auth untuk Dokter
Route::middleware('guest')->group(function () {
    Route::get('login/dokter', [DokterController::class, 'showLoginForm'])->name('dokter.login');
    Route::post('login/dokter', [DokterController::class, 'login']);
});

// Redirect untuk setelah login
Route::middleware('auth:pasien')->group(function () {
    Route::get('/dashboard/pasien', [PasienDashboardController::class, 'index'])->name('dashboard.pasien');
    Route::get('/dashboard/daftar-poli', [DaftarPoliController::class, 'index'])->name('daftar-poli.index');
    Route::post('/dashboard/daftar-poli', [DaftarPoliController::class, 'store'])->name('daftar-poli.store');
    Route::get('/daftar-poli/jadwal/{id_poli}', [DaftarPoliController::class, 'getJadwalByPoli'])->name('daftar-poli.jadwal');


});

// Password Reset Routes
Route::middleware('guest')->group(function () {
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// Authenticated Routes for all roles
Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
