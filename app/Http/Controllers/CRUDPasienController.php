<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;

class CRUDPasienController extends Controller
{
    /**
     * Menampilkan daftar pasien.
     */
    public function index()
    {
        $pasien = Pasien::all();  // Get all pasien data
        return Inertia::render('Dashboard/CrudPasien', [
            'pasien' => $pasien,
        ]);
    }

    /**
     * Menyimpan data pasien baru.
     */
    public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|lowercase|email|max:255|unique:' . Pasien::class,
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
        'alamat' => 'nullable|string|max:255',   // Tidak required
        'no_ktp' => 'nullable|string|max:20',    // Tidak required
        'no_hp' => 'nullable|string|max:15',     // Tidak required
        'no_rm' => 'nullable|string|max:20',     // Tidak required
    ]);

    // Ambil tanggal hari ini dalam format YYYYMMDD
    $date = Carbon::now()->format('Ymd');

    // Cari jumlah pasien yang sudah terdaftar pada tanggal tersebut
    $count = Pasien::where('no_rm', 'like', "$date%")->count();

    // Nomor urut pasien, mulai dari 001
    $no_rm = $date . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

    // Buat pasien baru dan simpan data
    $pasien = Pasien::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'alamat' => $request->alamat,  // Menyimpan alamat
        'no_ktp' => $request->no_ktp,  // Menyimpan nomor KTP
        'no_hp' => $request->no_hp,    // Menyimpan nomor HP
        'no_rm' => $no_rm,             // Menyimpan nomor rekam medis otomatis
    ]);

    // Redirect ke dashboard pasien setelah login
    return redirect()->route('pasien.index')->with('success', 'Pasien berhasil ditambahkan.');
}


    /**
     * Memperbarui data pasien.
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:pasien,email,' . $id,
            'alamat' => 'nullable|string|max:500',
            'no_hp' => 'nullable|string|max:15',
            'no_ktp' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed', // Optional password validation
        ]);

        // Find the patient by ID
        $pasien = Pasien::find($id);
        if (!$pasien) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        // Update the patient data
        $pasien->name = $validatedData['name'];
        $pasien->email = $validatedData['email'];
        $pasien->alamat = $validatedData['alamat'];
        $pasien->no_hp = $validatedData['no_hp'];
        $pasien->no_ktp = $validatedData['no_ktp'];

        // Update the password if provided
        if ($request->has('password') && !empty($request->password)) {
            $pasien->password = bcrypt($validatedData['password']);
        }

        $pasien->save(); // Save the updated data

        return response()->json(['message' => 'Patient updated successfully', 'pasien' => $pasien], 200);
    }

    /**
     * Menghapus data pasien.
     */
    public function destroy($id)
    {
        $pasien = Pasien::find($id);

        if (!$pasien) {
            return response()->json(['message' => 'Pasient not found'], 404);
        }

        $pasien->delete();

        return redirect()->route('pasien.index')->with('success', 'Pasien berhasil dihapus.');
    }


    public function getPasien()
    {
        // Fetch all poli data for dropdown
        $pasien = Pasien::all();

        return response()->json($pasien);
    }

    /**
     * Generate no_rm in the format YYYYMMDD-XXX (auto increment).
     */
    protected function generateNoRm($lastNoRm)
    {
        // Get today's date in YYYYMMDD format
        $today = date('Ymd');

        // If the last RM number exists and the date is the same, increment the counter
        if ($lastNoRm && substr($lastNoRm, 0, 8) === $today) {
            $counter = (int) substr($lastNoRm, -3) + 1;
        } else {
            // Otherwise, start the counter at 1
            $counter = 1;
        }

        // Return the new RM number in the format YYYYMMDD-XXX
        return $today . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
    }
}
