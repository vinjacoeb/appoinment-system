<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Dokter;
use Inertia\Inertia;

class DokterController extends Controller
{
    /**
     * Menampilkan form login dokteren.
     */
    public function showLoginForm()
    {
        return Inertia::render('Auth/LoginDokter', [
            'type' => 'dokter',
        ]);
    }

    /**
     * Proses login dokteren.
     */
    public function loginDokter(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('dokter')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard.dokter')); // Harus mengarah ke /dashboard/dokteren
        }
        

        // Jika login gagal
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    /**
     * Menampilkan dashboard dokteren.
     */
    public function dashboarDokter()
    {
        return Inertia::render('Dashboard/DokterDashboard', [
            'user' => Auth::guard('dokter')->user(),
            'role' => 'dokter',
        ]);
    }

    /**
     * Logout dokteren.
     */
    public function logoutDokter(Request $request)
    {
        Auth::guard('dokter')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('/');
    }
}
