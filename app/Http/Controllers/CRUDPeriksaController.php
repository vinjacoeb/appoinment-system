<?php

namespace App\Http\Controllers;

use App\Models\Daftar;
use App\Models\Pasien;
use App\Models\Periksa;
use App\Models\DetailPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Obat; // Import the Obat model
use Illuminate\Support\Facades\Log; // Import the Log facade
use Carbon\Carbon; // Import Carbon


class CRUDPeriksaController extends Controller
{
    /**
     * Menampilkan daftar periksa berdasarkan dokter yang sedang login.
     */
    public function index()
    {
        // Ambil daftar poli yang sesuai dengan jadwal yang dimiliki oleh dokter yang sedang login
        $daftarPoli = Daftar::whereHas('jadwal', function ($query) {
            $query->where('id_dokter', Auth::id()); // Filter berdasarkan dokter yang sedang login
        })
        ->with(['pasien', 'jadwal']) // Load relasi dengan pasien dan jadwal
        ->get();

        // Tampilkan daftar poli dengan informasi pasien dan jadwal
        return Inertia::render('Dashboard/Periksa', [
            'daftarPoli' => $daftarPoli,
        ]);
    }

    /**
     * Mengambil daftar periksa untuk dokter yang sedang login
     */
    public function getPeriksa(Request $request)
{
    $filterByDokter = $request->input('filter_by_dokter', true);
    $dokterId = $filterByDokter ? Auth::id() : null;

    // Query data dari tabel 'daftar' dengan relasi pasien
    $query = Daftar::with('pasien');

    // Filter berdasarkan dokter jika diperlukan
    if ($filterByDokter && $dokterId) {
        $query->whereHas('jadwal', function ($q) use ($dokterId) {
            $q->where('id_dokter', $dokterId);
        });
    }

    // Ambil data
    $daftarPoli = $query->get();

    // Format data dan tambahkan properti 'isSaved'
    $formattedData = $daftarPoli->map(function ($item) {
        return [
            'id' => $item->id,
            'name' => $item->pasien ? $item->pasien->name : null,
            'keluhan' => $item->keluhan,
            'no_antrian' => $item->no_antrian,
            'id_pasien' => $item->id_pasien,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
            // Periksa apakah id daftar poli sudah ada di tabel periksa
            'isSaved' => Periksa::where('id_daftar_poli', $item->id)->exists(),
        ];
    });

    return response()->json($formattedData);
}




    /**
     * Memperbarui data periksa (post ke tabel periksa)
     */
    public function update(Request $request, $id)
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validasi input
        $validated = $request->validate([
            'id_daftar_poli' => 'required|exists:daftar_poli,id', // Pastikan id_daftar_poli ada di tabel daftar
            'tgl_periksa' => 'required|date',
            'biaya_periksa' => 'required|numeric',
            'catatan' => 'nullable|string', // Catatan bersifat opsional
        ]);

        // Convert the date format to MySQL compatible format
        $validated['tgl_periksa'] = Carbon::parse($validated['tgl_periksa'])->format('Y-m-d H:i:s');

        // Cari data periksa berdasarkan ID
        $periksa = Periksa::find($id);

        // Jika data periksa tidak ditemukan
        if (!$periksa) {
            return response()->json(['message' => 'Data periksa tidak ditemukan'], 404);
        }

        // Perbarui data periksa
        $periksa->update([
            'id_daftar_poli' => $validated['id_daftar_poli'], // Update jika diperlukan
            'tgl_periksa' => $validated['tgl_periksa'],
            'biaya_periksa' => $validated['biaya_periksa'],
            'catatan' => $validated['catatan'], // Tambahkan atau perbarui catatan
        ]);

        return response()->json(['message' => 'Data periksa berhasil diperbarui'], 200);
    }

    /**
     * Mengambil daftar obat untuk dokter yang sedang login
     */
    public function getObat()
    {
        // Fetch all poli data for dropdown
        $obat = Obat::all();

        return response()->json($obat);
    }



    public function savePeriksa(Request $request)
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate the request data
        $validated = $request->validate([
            'id_daftar_poli' => 'required|exists:daftar_poli,id', // Validate that the id exists in daftar_poli
            'tgl_periksa' => 'required|date',
            'biaya_periksa' => 'required|numeric',
            'catatan' => 'nullable|string',
            'obats' => 'required|array',
            'obats.*' => 'exists:obat,id',
        ]);

        // Convert the date format to MySQL-compatible format
        $validated['tgl_periksa'] = Carbon::parse($validated['tgl_periksa'])->format('Y-m-d H:i:s');

        // Create the periksa record
        $periksa = Periksa::create([
            'id_daftar_poli' => $validated['id_daftar_poli'],  // Ensure id_daftar_poli is included
            'tgl_periksa' => $validated['tgl_periksa'],
            'biaya_periksa' => $validated['biaya_periksa'],
            'catatan' => $validated['catatan'] ?? '',
        ]);

        // Create the detail_periksa entries for each obat
        foreach ($validated['obats'] as $obatId) {
            DetailPeriksa::create([
                'id_periksa' => $periksa->id,
                'id_obat' => $obatId,
            ]);
        }

        return response()->json(['message' => 'Data periksa berhasil disimpan'], 200);
    }

    public function isSaved($id)
{
    // Cari data periksa berdasarkan id
    $periksa = Periksa::find($id);

    if (!$periksa) {
        return response()->json(['message' => 'Data periksa tidak ditemukan'], 404);
    }

    // Periksa apakah id memiliki nilai
    $isSaved = $periksa->id_daftar_poli !== null;

    return response()->json(['is_saved' => $isSaved], 200);
}
    


    /**
     * Menghapus data periksa
     */
    public function destroy($id)
    {
        // Cari data periksa berdasarkan id
        $periksa = Periksa::find($id);

        if (!$periksa) {
            return response()->json(['message' => 'Data periksa tidak ditemukan'], 404);
        }

        $periksa->delete();

        return response()->json(['message' => 'Periksa berhasil dihapus'], 200);
    }
}
