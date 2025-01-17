<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class CRUDJadwalPeriksaController extends Controller
{
    /**
     * Menampilkan daftar jadwal periksa berdasarkan dokter yang sedang login.
     */
    public function index()
    {
        // Hanya tampilkan jadwal milik dokter yang sedang login
        $jadwals = Jadwal::where('id_dokter', Auth::id())->get();

        return Inertia::render('Dashboard/JadwalPeriksa', [
            'jadwals' => $jadwals,
        ]);
    }

    // Menyimpan jadwal periksa baru
    public function store(Request $request)
{
    $validated = $request->validate([
        'hari' => 'required|string|max:15',
        'jam_mulai' => 'required|date_format:H:i',
        'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        'status' => 'required|boolean',
    ]);

    // Format waktu menggunakan Carbon untuk memastikan format H:i
    $jamMulai = Carbon::createFromFormat('H:i', $validated['jam_mulai'])->format('H:i');
    $jamSelesai = Carbon::createFromFormat('H:i', $validated['jam_selesai'])->format('H:i');

    // Check if the schedule already exists for the doctor on the same day
    $existingSchedule = Jadwal::where('id_dokter', Auth::id())
        ->where('hari', $validated['hari'])
        ->exists();

        if ($existingSchedule) {
            return response()->json([
                'message' => 'Anda Tidak Dapat Menambah Jadwal Periksa. Jadwal periksa sudah ada untuk hari tersebut.'
            ], 400);
        }
        

    Jadwal::create([
        'id_dokter' => Auth::id(),
        'hari' => $validated['hari'],
        'jam_mulai' => $jamMulai,
        'jam_selesai' => $jamSelesai,
        'status' => $validated['status'],
    ]);

    return redirect()->route('jadwal-periksa.index')->with('success', 'Jadwal periksa berhasil ditambahkan.');
}

public function update(Request $request, $id)
{
    $validated = $request->validate([
        'hari' => 'required|string|max:15',
        'jam_mulai' => 'required|date_format:H:i',
        'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        'status' => 'required|boolean',
    ]);

    // Format waktu menggunakan Carbon untuk memastikan format H:i
    $jamMulai = Carbon::createFromFormat('H:i', $validated['jam_mulai'])->format('H:i');
    $jamSelesai = Carbon::createFromFormat('H:i', $validated['jam_selesai'])->format('H:i');

    // Check if the schedule already exists for the doctor
    $existingSchedule = Jadwal::where('id_dokter', Auth::id())
        ->where('hari', $validated['hari'])
        ->where('jam_mulai', $jamMulai)
        ->where('jam_selesai', $jamSelesai)
        ->where('id', '!=', $id)
        ->exists();

    if ($existingSchedule) {
        return redirect()->route('jadwal-periksa.index')->with('error', 'Jadwal periksa sudah ada untuk waktu tersebut.');
    }

    // Cari jadwal periksa milik dokter yang sedang login
    $jadwal = Jadwal::where('id', $id)
        ->where('id_dokter', Auth::id())
        ->first();

    if (!$jadwal) {
        return response()->json(['message' => 'Jadwal periksa tidak ditemukan'], 404);
    }

    // Update jadwal periksa dengan data yang sudah diformat
    $jadwal->update([
        'hari' => $validated['hari'],
        'jam_mulai' => $jamMulai,
        'jam_selesai' => $jamSelesai,
        'status' => $validated['status'],
    ]);

    return redirect()->route('jadwal-periksa.index')->with('success', 'Jadwal periksa berhasil diperbarui.');
}
    /**
     * Menghapus jadwal periksa.
     */
    public function destroy($id)
    {
        // Cari jadwal periksa milik dokter yang sedang login
        $jadwal = Jadwal::where('id', $id)
            ->where('id_dokter', Auth::id())
            ->first();

        if (!$jadwal) {
            return response()->json(['message' => 'Jadwal periksa tidak ditemukan'], 404);
        }

        $jadwal->delete();

        return redirect()->route('jadwal-periksa.index')->with('success', 'Jadwal periksa berhasil dihapus.');
    }
    public function getJadwal(Request $request)
{
    // Ambil ID dokter yang sedang login
    $dokterId = Auth::id(); // Jika dokter menggunakan autentikasi berbasis pengguna, misalnya menggunakan Laravel Passport atau Sanctum

    // Ambil jadwal yang sesuai dengan id_dokter yang sedang login
    $jadwal = Jadwal::where('id_dokter', $dokterId)->get();  // Filter jadwal berdasarkan dokter yang sedang login

    // Format jam_mulai dan jam_selesai ke format H:i
    $jadwal->map(function ($item) {
        $item->jam_mulai = Carbon::parse($item->jam_mulai)->format('H:i');
        $item->jam_selesai = Carbon::parse($item->jam_selesai)->format('H:i');
        return $item;
    });

    // Kembalikan response dengan data yang sudah diformat
    return response()->json($jadwal);
}

}
