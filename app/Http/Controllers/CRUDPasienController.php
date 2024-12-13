<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

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
    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:pasien,email',
            'password' => 'required|min:8',
            'alamat' => 'required|string|max:500',
            'no_ktp' => 'required|string|max:20',
            'no_hp' => 'required|string|max:15',
        ]);

        // Auto-generate no_rm (e.g., 20241213-001)
        $lastPasien = Pasien::orderBy('created_at', 'desc')->first();
        $lastNoRm = $lastPasien ? $lastPasien->no_rm : null;
        $noRm = $this->generateNoRm($lastNoRm);

        // Hash the password before saving
        $validated['password'] = Hash::make($validated['password']);
        $validated['no_rm'] = $noRm;

        // Create a new Pasien entry
        Pasien::create($validated);

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
    public function destroy(Pasien $pasien)
    {
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
