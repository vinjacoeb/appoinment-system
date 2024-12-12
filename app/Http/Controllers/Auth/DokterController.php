<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DokterController extends Controller
{
    // Menampilkan Form Login
    public function showLoginForm()
    {
        return Inertia::render('Auth/LoginDokter', ['type' => 'dokter']);
    }

    // Proses Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Cek role untuk redirect sesuai hak akses
            if ($user->role === 0) {
                return redirect()->route('dashboard.admin');
            } elseif ($user->role === 1) {
                return redirect()->route('dashboard.dokter');
            } else {
                Auth::logout();
                return back()->withErrors(['email' => 'Role tidak valid.']);
            }
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput();
    }

    // Dashboard Dokter
    public function dashboard()
    {
        return Inertia::render('Dashboard/DokterDashboard', [
            'user' => Auth::user(),
        ]);
    }
}
