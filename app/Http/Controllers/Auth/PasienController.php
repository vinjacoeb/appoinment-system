<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Pasien;
use Inertia\Inertia;

class PasienController extends Controller
{
    /**
     * Menampilkan form login pasien.
     */
    public function showLoginForm()
    {
        return Inertia::render('Auth/LoginPasien', [
            'type' => 'pasien',
        ]);
    }

    /**
     * Proses login pasien.
     */
    public function loginPasien(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('pasien')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard.pasien')); // Harus mengarah ke /dashboard/pasien
        }
        

        // Jika login gagal
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    /**
     * Menampilkan dashboard pasien.
     */
    public function dashboardPasien()
    {
        return Inertia::render('Dashboard/PasienDashboard', [
            'user' => Auth::guard('pasien')->user(),
            'role' => 'pasien',
        ]);
    }

    /**
     * Logout pasien.
     */
    public function logoutPasien(Request $request)
    {
        Auth::guard('pasien')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pasien.login');
    }
}
