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
    public function getJadwalByPoli(Request $request)
{
    $request->validate([
        'id_poli' => 'required|exists:poli,id',
    ]);

    // Ambil dokter berdasarkan id_poli
    $dokterOptions = Dokter::where('id_poli', $request->id_poli)
        ->select('id', 'nama') // Ambil id dan nama dokter
        ->get();

    // Ambil jadwal berdasarkan id_poli dan gabungkan dengan dokter
    $jadwalOptions = Jadwal::whereHas('dokter', function ($query) use ($request) {
            $query->where('id_poli', $request->id_poli);
        })
        ->with('dokter') // Ensure the dokter relationship is eager-loaded
        ->select('id', 'hari', 'jam_mulai', 'jam_selesai', 'id_dokter')
        ->get();

    // Include doctor name along with the schedule data
    $jadwalOptions = $jadwalOptions->map(function ($jadwal) {
        $jadwal->dokter_name = $jadwal->dokter->nama; // Add doctor name to each schedule
        return $jadwal;
    });

    return response()->json([
        'dokterOptions' => $dokterOptions,
        'jadwalOptions' => $jadwalOptions,
    ]);
}






    /**
     * Menangani proses pendaftaran poli.
     */
    public function store(Request $request)
{
    // Validasi data input
    $request->validate([
        'id_poli' => 'required|exists:poli,id',          // Pastikan id_poli ada di tabel polis
        'id_jadwal' => 'required|exists:jadwal_periksa,id',      // Pastikan id_jadwal ada di tabel jadwal
        'keluhan' => 'required|string',                 // Keluhan wajib diisi
    ]);

    // Ambil id dokter dari jadwal
    $jadwal = Jadwal::findOrFail($request->id_jadwal);
    $idDokter = $jadwal->id_dokter;

    // Ambil tanggal hari ini
    $tanggalHariIni = now()->toDateString();

    // Hitung jumlah antrian untuk dokter tersebut pada hari ini
    $jumlahAntrianHariIni = Daftar::whereHas('jadwal', function ($query) use ($idDokter) {
            $query->where('id_dokter', $idDokter); // Filter berdasarkan dokter
        })
        ->whereDate('created_at', $tanggalHariIni) // Filter berdasarkan tanggal hari ini
        ->count();

    // Hitung nomor antrian baru
    $noAntrian = $jumlahAntrianHariIni + 1;

    // Menyimpan data pendaftaran poli
    Daftar::create([
        'id_pasien' => auth()->id(),        // ID pasien yang sedang login
        'id_poli' => $request->id_poli,     // ID poli yang dipilih
        'id_jadwal' => $request->id_jadwal, // ID jadwal (dokter yang dipilih)
        'keluhan' => $request->keluhan,     // Keluhan pasien
        'no_antrian' => $noAntrian,         // Nomor antrian baru
    ]);

    // Redirect ke halaman daftar poli setelah berhasil
    return redirect()->route('daftar-poli.index')->with('success', 'Pendaftaran poli berhasil!');
}



}
