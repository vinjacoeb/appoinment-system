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
use App\Http\Controllers\CRUDDokterController;
use App\Http\Controllers\CRUDPasienController;
use App\Http\Controllers\CRUDPoliController;
use App\Http\Controllers\CRUDObatController;
use App\Http\Controllers\CRUDJadwalPeriksaController;
use App\Http\Controllers\CRUDPeriksaController;
use App\Http\Controllers\RiwayatPasienController;
use App\Http\Controllers\Auth\DokterDashboardController;
use App\Http\Controllers\Auth\AdminDashboardController;
use App\Http\Controllers\ProfilDokterController;
use App\Http\Controllers\RiwayatController;

use Illuminate\Support\Facades\Route;

// Auth untuk Dokter
Route::middleware('guest')->group(function () {
    Route::get('/login/dokter', [DokterController::class, 'showLoginForm'])->name('dokter.login');
    Route::post('/login/dokter', [DokterController::class, 'loginDokter'])->name('dokter.login.process');
    
});

Route::middleware('guest')->group(function () {
    Route::get('/login/admin', [AdminController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login/admin', [AdminController::class, 'loginAdmin'])->name('admin.login.process');
    
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

// Redirect untuk setelah login
Route::middleware('auth:pasien')->group(function () {
    Route::get('/dashboard/pasien', [PasienDashboardController::class, 'index'])->name('dashboard.pasien');
    Route::post('/logout-pasien', [PasienController::class, 'logoutPasien'])->name('logoutPasien');
    Route::get('/dashboard/daftar-poli', [DaftarPoliController::class, 'index'])->name('daftar-poli.index');
    Route::post('/dashboard/daftar-poli', [DaftarPoliController::class, 'store'])->name('daftar-poli.store');
    Route::post('/get-jadwal-by-poli', [DaftarPoliController::class, 'getJadwalByPoli']);

    Route::get('/dashboard/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');
    Route::get('/show-riwayat', [RiwayatController::class, 'show']);
    Route::get('/riwayat/{id_daftar_poli}', [RiwayatController::class, 'getPeriksa']);



});

Route::middleware(['auth:dokter'])->group(function () {
    Route::get('/dashboard/dokter', [DokterDashboardController::class, 'index'])->name('dashboard.dokter');

    Route::get('/dashboard/jadwal-periksa', [CRUDJadwalPeriksaController::class, 'index'])->name('jadwal-periksa.index');
    Route::post('jadwal-periksa', [CRUDJadwalPeriksaController::class, 'store'])->name('jadwal-periksa.store');
    Route::post('jadwal-periksa/{id}', [CRUDJadwalPeriksaController::class, 'update'])->name('jadwal-periksa.update');
    Route::delete('jadwal-periksa/{id}', [CRUDJadwalPeriksaController::class, 'destroy'])->name('jadwal-periksa.destroy');
    Route::get('/jadwal-periksa', [CRUDJadwalPeriksaController::class, 'getJadwal']);

    Route::get('/dashboard/periksa-pasien', [CRUDPeriksaController::class, 'index'])->name('periksa.index');
    Route::get('periksa/{id}', [CRUDPeriksaController::class, 'update'])->name('periksa.update');
    Route::delete('periksa-pasien/{id}', [CRUDPeriksaController::class, 'destroy'])->name('periksa.destroy');
    Route::get('/periksa', [CRUDPeriksaController::class, 'getPeriksa']);
    Route::get('/obatt', [CRUDPeriksaController::class, 'getObat']);
    Route::post('/periksa', [CRUDPeriksaController::class, 'savePeriksa']);
    Route::get('/periksa/{id}/is-saved', [CRUDPeriksaController::class, 'isSaved']);

    Route::get('/dashboard/riwayat-pasien', [RiwayatPasienController::class, 'index'])->name('riwayat.index');
    Route::get('/riwayat-periksa', [RiwayatPasienController::class, 'getRiwayatPeriksa']);
    Route::put('/riwayat-periksa/{id}', [RiwayatPasienController::class, 'savePeriksa']);

    Route::get('/dashboard/profil-dokter', [ProfilDokterController::class, 'index'])->name('profil.index');
    Route::post('/update-profil', [ProfilDokterController::class, 'update']);
    Route::get('/profil-dokter', [ProfilDokterController::class, 'getProfil']);
    
});

Route::middleware(['auth:admin'])->group(function () {
    Route::get('/dashboard/admin', [AdminDashboardController::class, 'index'])->name('dashboard.admin');

    Route::get('/dashboard/crud-dokter', [CRUDDokterController::class, 'index'])->name('dokter.index');
    Route::post('/dashboard/crud-dokter', [CRUDDokterController::class, 'store'])->name('dokter.store');
    Route::put('/dashboard/crud-dokter/{dokter}', [CRUDDokterController::class, 'update'])->name('dokter.update');
    Route::delete('/dashboard/crud-dokter/{dokter}', [CRUDDokterController::class, 'destroy'])->name('dokter.destroy');
    Route::get('/dokter', [CRUDDokterController::class, 'getDokter']);
    Route::get('/poli', [CRUDDokterController::class, 'getPoli']);
    Route::post('/dokter', [CRUDDokterController::class, 'store']);
    Route::post('/hash-password', [CRUDDokterController::class, 'hashPassword']);
    Route::put('/dokter/{id}', [CRUDDokterController::class, 'update']);
    
    Route::get('/dashboard/crud-pasien', [CRUDPasienController::class, 'index'])->name('pasien.index');
    Route::delete('/dashboard/crud-pasien/{pasien}', [CRUDPasienController::class, 'destroy'])->name('pasien.destroy');
    Route::get('/pasien', [CRUDPasienController::class, 'getPasien']);
    Route::post('/pasien', [CRUDPasienController::class, 'store']); // To create a new pasien
    Route::put('/pasien/{id}', [CRUDPasienController::class, 'update']); // To update pasien by ID
    Route::post('/pasien/hash-password', [CRUDPasienController::class, 'hashPassword']); // To hash password"


    Route::get('/dashboard/crud-poli', [CRUDPoliController::class, 'index'])->name('poli.index');
    Route::post('/dashboard/crud-poli', [CRUDPoliController::class, 'store'])->name('poli.store');
    Route::put('/dashboard/crud-poli/{id}', [CRUDPoliController::class, 'update'])->name('poli.update');
    Route::delete('/dashboard/crud-poli/{id}', [CRUDPoliController::class, 'destroy'])->name('poli.destroy');
    Route::get('/poli', [CRUDPoliController::class, 'getPoli']);
    Route::post('/poli', [CRUDPoliController::class, 'store']);
    Route::post('/poli/{id}', [CRUDPoliController::class, 'update']);


    Route::get('/dashboard/crud-obat', [CRUDObatController::class, 'index'])->name('obat.index');
    Route::post('/dashboard/crud-obat', [CRUDObatController::class, 'store'])->name('obat.store');
    Route::put('/dashboard/crud-obat/{id}', [CRUDObatController::class, 'update'])->name('obat.update');
    Route::delete('/dashboard/crud-obat/{id}', [CRUDObatController::class, 'destroy'])->name('obat.destroy');
    Route::get('/obat', [CRUDObatController::class, 'getObat']);
    Route::post('/obat', [CRUDObatController::class, 'store']);
    Route::post('/obat/{id}', [CRUDObatController::class, 'update']);
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
