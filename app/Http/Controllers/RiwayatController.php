<?php
namespace App\Http\Controllers;

use App\Models\Periksa;
use App\Models\DetailPeriksa;
use App\Models\Daftar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class RiwayatController extends Controller
{
    /**
     * Menampilkan riwayat pemeriksaan untuk pasien
     */
    // Controller method to display history of the logged-in patient
    public function index()
    {
        // Retrieve all the records from the daftar_poli table with related data (Pasien and Jadwal)
        $riwayat = Daftar::with(['pasien', 'jadwal'])->get();

    // Return the data wrapped inside an Inertia response
    return Inertia::render('Dashboard/Riwayat', [
        'riwayatPeriksa' => $riwayat,
    ]);
    }
    
    public function getPeriksa($id_daftar_poli) {
        // Log the incoming id_daftar_poli
        \Log::info("Fetching Riwayat for id_daftar_poli: " . $id_daftar_poli);
    
        // Fetch the 'riwayat' using id_daftar_poli
        $riwayat = Periksa::with('daftar.pasien', 'detailPeriksa.obat')
                          ->where('id_daftar_poli', $id_daftar_poli) // Using where instead of find
                          ->first();
    
        if (!$riwayat) {
            \Log::error("Riwayat not found for id_daftar_poli: " . $id_daftar_poli);  // Log if the record is not found
            return response()->json(['message' => 'Riwayat not found'], 404);
        }
    
        // Log the retrieved riwayat data
        \Log::info("Riwayat found: ", $riwayat->toArray());
    
        return response()->json($riwayat);
    }
    
    


    
    public function show(Request $request)
{
    // Get the authenticated user (pasien)
    $pasien = Auth::user();
    
    // Fetch riwayat periksa (history) with necessary relationships
    $riwayatPeriksa = Daftar::with(['pasien', 'jadwal'])
        ->where('id_pasien', $pasien->id)  // Filter by the authenticated patient's ID
        ->get();
    
    // Return the data as JSON
    return response()->json($riwayatPeriksa);
}
}
