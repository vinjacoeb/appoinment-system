<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ProfilDokterController extends Controller
{
    /**
     * Menampilkan profil dokter.
     */
    public function index()
{
    // Ambil data dokter yang sedang login
    $dokter = auth('dokter')->user();

    return Inertia::render('Dashboard/ProfilDokter', [
        'dokter' => $dokter,
    ]);
}



    /**
     * Memperbarui data profil dokter.
     */
    public function update(Request $request)
{
    // Validasi data input
    $validatedData = $request->validate([
        'nama' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:dokter,email,' . Auth::id(), // Gunakan Auth::id() untuk ID dokter yang login
        'alamat' => 'nullable|string|max:500',
        'no_hp' => 'nullable|string|max:15',
        'password' => 'nullable|string|min:8|confirmed',
    ]);

    // Temukan dokter berdasarkan ID yang sedang login
    $dokter = Auth::user(); // Mengambil data dokter yang sedang login

    // Perbarui data dokter
    $dokter->nama = $validatedData['nama'];
    $dokter->email = $validatedData['email'];
    $dokter->alamat = $validatedData['alamat'];
    $dokter->no_hp = $validatedData['no_hp'];

    // Update password jika ada
    if ($request->filled('password')) {
        $dokter->password = Hash::make($validatedData['password']);
    }

    $dokter->save(); // Simpan perubahan

    return redirect()->route('profil.index')
        ->with('success', 'Profil dokter berhasil diperbarui.');
}



    public function getProfil()
    {
        // Ambil dokter yang sedang login berdasarkan ID
        $dokter = Auth::user();  // Auth::user() sudah otomatis mengembalikan data dokter yang sedang login

        // Pastikan data dokter ada
        if (!$dokter) {
            return response()->json(['error' => 'Dokter tidak ditemukan'], 404);
        }

        return response()->json([
            'nama' => $dokter->nama,
            'email' => $dokter->email,
            'alamat' => $dokter->alamat,
            'no_hp' => $dokter->no_hp
        ]);
    }
}
