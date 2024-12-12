<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PasienDashboardController extends Controller
{
    /**
     * Menampilkan dashboard pasien.
     */
    public function index()
    {
        // Mengambil informasi user yang sedang login
        $user = Auth::guard('pasien')->user();

        // Mengembalikan tampilan dashboard pasien menggunakan Inertia.js
        return Inertia::render('Dashboard/PasienDashboard', [
            'user' => $user,
        ]);
    }
}
