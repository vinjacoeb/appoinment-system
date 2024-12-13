<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use Inertia\Inertia;

class AdminController extends Controller
{
    /**
     * Menampilkan form login dokteren.
     */
    public function showLoginForm()
    {
        return Inertia::render('Auth/LoginAdmin', [
            'type' => 'admin',
        ]);
    }

    /**
     * Proses login dokteren.
     */
    public function loginAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard.admin')); // Harus mengarah ke /dashboard/dokteren
        }
        

        // Jika login gagal
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    /**
     * Menampilkan dashboard dokteren.
     */
    public function dashboarAdmin()
    {
        return Inertia::render('Dashboard/AdminDashboard', [
            'user' => Auth::guard('admin')->user(),
            'role' => 'admin',
        ]);
    }

    /**
     * Logout dokteren.
     */
    public function logoutAdmin(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('/');
    }
}
