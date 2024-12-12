<?php
namespace App\Http\Controllers;

use App\Models\Daftar;
use App\Models\Pasien;
use App\Models\Jadwal;
use App\Models\Poli;
use App\Models\Dokter;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DaftarPoliController extends Controller
{
    /**
     * Menampilkan halaman form pendaftaran poli.
     */
    public function index()
    {
        // Mengambil data pasien yang sedang login
        $pasien = auth()->user();

        // Mengambil semua poli
        $poliOptions = Poli::select('id', 'nama_poli')->get();

        // Mengirim data ke Vue melalui Inertia
        return Inertia::render('Dashboard/DaftarPoli', [
            'pasien' => $pasien,
            'poliOptions' => $poliOptions,
        ]);
    }

    /**
     * Mengambil dokter berdasarkan poli.
     */
    public function getJadwalByPoli($id_poli)
{
    // Validasi id_poli
    if (!Poli::find($id_poli)) {
        return response()->json(['error' => 'Poli tidak ditemukan'], 404);
    }

    // Ambil jadwal berdasarkan id_poli
    $jadwalOptions = Jadwal::where('id_poli', $id_poli)
        ->select('id', 'label')
        ->get();

    // Kembalikan response dengan data jadwal
    return response()->json($jadwalOptions);
}



    /**
     * Menangani proses pendaftaran poli.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_poli' => 'required|exists:polis,id',
            'id_jadwal' => 'required|exists:jadwals,id',
            'keluhan' => 'required|string',
        ]);

        // Generate nomor antrian
        $noAntrian = 'A' . str_pad(Daftar::count() + 1, 3, '0', STR_PAD_LEFT);

        // Menyimpan data pendaftaran poli
        Daftar::create([
            'id_pasien' => auth()->id(),
            'id_poli' => $request->id_poli,
            'id_jadwal' => $request->id_jadwal,
            'keluhan' => $request->keluhan,
            'no_antrian' => $noAntrian,
        ]);

        // Redirect ke halaman daftar poli setelah berhasil
        return redirect()->route('daftar-poli.index')->with('success', 'Pendaftaran poli berhasil!');
    }
}
