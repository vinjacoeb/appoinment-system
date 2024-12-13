<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DokterDashboardController extends Controller
{
    /**
     * Menampilkan dashboard pasien.
     */
    public function index()
    {
        // Mengambil informasi user yang sedang login
        $user = Auth::guard('dokter')->user();

        // Mengembalikan tampilan dashboard pasien menggunakan Inertia.js
        return Inertia::render('Dashboard/DokterDashboard', [
            'user' => $user,
        ]);
    }
}
