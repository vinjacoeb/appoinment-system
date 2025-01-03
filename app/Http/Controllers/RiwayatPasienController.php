<?php
namespace App\Http\Controllers;

use App\Models\Periksa;
use App\Models\DetailPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Obat; // Import the Obat model
use Illuminate\Support\Facades\Log; // Import the Log facade
use Carbon\Carbon; // Import Carbon

class RiwayatPasienController extends Controller
{
    /**
     * Menampilkan riwayat periksa berdasarkan dokter yang sedang login.
     */
    public function index()
    {
        // Ambil riwayat periksa yang sesuai dengan dokter yang sedang login
        $riwayatPeriksa = Periksa::whereHas('daftar', function ($query) {
            $query->whereHas('jadwal', function ($query) {
                $query->where('id_dokter', Auth::id()); // Filter berdasarkan dokter yang sedang login
            });
        })
        ->with(['detailPeriksa', 'detailPeriksa.obat', 'daftar.pasien']) // Load relasi dengan detail periksa, obat, dan pasien
        ->get();

        // Tampilkan riwayat periksa dengan informasi detail periksa, obat, dan pasien
        return Inertia::render('Dashboard/RiwayatPasien', [
            'riwayatPeriksa' => $riwayatPeriksa,
        ]);
    }

    /**
     * Mengambil riwayat periksa untuk dokter yang sedang login
     */
    public function getRiwayatPeriksa(Request $request)
    {
        $filterByDokter = $request->input('filter_by_dokter', true);
        $dokterId = $filterByDokter ? Auth::id() : null;

        // Query data dari tabel 'periksa' dengan relasi detail periksa, obat, dan pasien
        $query = Periksa::with(['detailPeriksa', 'detailPeriksa.obat', 'daftar.pasien']);

        // Filter berdasarkan dokter jika diperlukan
        if ($filterByDokter && $dokterId) {
            $query->whereHas('daftar', function ($q) use ($dokterId) {
                $q->whereHas('jadwal', function ($q) use ($dokterId) {
                    $q->where('id_dokter', $dokterId);
                });
            });
        }

        // Ambil data
        $riwayatPeriksa = $query->get();

        // Format data dan tambahkan properti 'isSaved'
        $formattedData = $riwayatPeriksa->map(function ($item) {
            return [
                'id' => $item->id,
                'id_daftar_poli' => $item->id_daftar_poli,
                'tgl_periksa' => $item->tgl_periksa,
                'biaya_periksa' => $item->biaya_periksa,
                'catatan' => $item->catatan,
                'detailPeriksa' => $item->detailPeriksa,
                'pasien' => $item->daftar->pasien,
                'nama_pasien' => $item->daftar->pasien->name,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                // Periksa apakah id periksa sudah ada di tabel detail_periksa
                'isSaved' => DetailPeriksa::where('id_periksa', $item->id)->exists(),
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
            'tgl_periksa' => 'required|date',
            'biaya_periksa' => 'required|numeric',
            'catatan' => 'nullable|string', // Catatan bersifat opsional
        ]);

        // Convert the date format to MySQL compatible format
        $validated['tgl_periksa'] = Carbon::parse($validated['tgl_periksa'])->format('Y-m-d');

        // Cari data periksa berdasarkan ID
        $periksa = Periksa::find($id);

        // Jika data periksa tidak ditemukan
        if (!$periksa) {
            return response()->json(['message' => 'Data periksa tidak ditemukan'], 404);
        }

        // Perbarui data periksa
        $periksa->update([
            'tgl_periksa' => $validated['tgl_periksa'],
            'biaya_periksa' => $validated['biaya_periksa'],
            'catatan' => $validated['catatan'], // Tambahkan atau perbarui catatan
        ]);

        return response()->json(['message' => 'Data periksa berhasil diperbarui'], 200);
    }

    /**
     * Mengambil daftar obat
     */
    public function getObat()
    {
        // Fetch all obat data for dropdown
        $obat = Obat::all();

        return response()->json($obat);
    }

    /**
     * Menyimpan data periksa
     */
    public function savePeriksa(Request $request)
{
    // Check if the user is authenticated
    if (!Auth::check()) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Validate the request data
    $validated = $request->validate([
        'id_daftar_poli' => 'required|exists:daftar_poli,id',
        'tgl_periksa' => 'required|date',
        'biaya_periksa' => 'required|numeric',
        'catatan' => 'nullable|string',
        'obats' => 'required|array',
        'obats.*' => 'exists:obat,id',
    ]);

    // Convert the date format to MySQL-compatible format
    $validated['tgl_periksa'] = Carbon::parse($validated['tgl_periksa'])->format('Y-m-d H:i:s');

    // Check if the periksa record already exists
    $periksa = Periksa::where('id_daftar_poli', $validated['id_daftar_poli'])->first();

    if ($periksa) {
        // Update existing periksa record
        $periksa->update([
            'tgl_periksa' => $validated['tgl_periksa'],
            'biaya_periksa' => $validated['biaya_periksa'],
            'catatan' => $validated['catatan'] ?? '',
        ]);

        // Update obat details in DetailPeriksa
        $existingObatIds = DetailPeriksa::where('id_periksa', $periksa->id)->pluck('id_obat')->toArray();

        // Find obats to add and remove
        $newObatIds = $validated['obats'];
        $obatsToAdd = array_diff($newObatIds, $existingObatIds);
        $obatsToRemove = array_diff($existingObatIds, $newObatIds);

        // Remove obats that are no longer needed
        DetailPeriksa::where('id_periksa', $periksa->id)
            ->whereIn('id_obat', $obatsToRemove)
            ->delete();

        // Add new obats
        foreach ($obatsToAdd as $obatId) {
            DetailPeriksa::create([
                'id_periksa' => $periksa->id,
                'id_obat' => $obatId,
            ]);
        }

    } else {
        // Return an error if trying to update a non-existent record
        return response()->json(['error' => 'Data periksa tidak ditemukan'], 404);
    }

    return response()->json(['message' => 'Data periksa berhasil diperbarui'], 200);
}


    /**
     * Memeriksa apakah data periksa sudah disimpan
     */
    public function isSaved($id)
    {
        // Cari data periksa berdasarkan id
        $periksa = Periksa::find($id);

        if (!$periksa) {
            return response()->json(['message' => 'Data periksa tidak ditemukan'], 404);
        }

        // Periksa apakah id memiliki nilai
        $isSaved = $periksa->id !== null;

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