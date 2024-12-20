<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;

class CRUDDokterController extends Controller
{
    /**
     * Menampilkan daftar dokter.
     */
    public function index()
{
    $dokter = Dokter::with('poli')->get();
    $poli = Poli::all();
    return Inertia::render('Dashboard/CrudDokter', [
        'dokter' => $dokter,
        'poli' => $poli,
    ]);
}

    /**
     * Menyimpan data dokter baru.
     */
    public function store(Request $request): RedirectResponse
{
    // Validasi input data
    $validated = $request->validate([
        'id_poli' => 'required|exists:poli,id',
        'nama' => 'required|string|max:255',
        'email' => 'required|string|lowercase|email|max:255|unique:' . Dokter::class,
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
        'alamat' => 'nullable|string|max:500',            // Tidak wajib diisi
        'no_hp' => 'nullable|string|max:15',             // Tidak wajib diisi
    ]);

    // Buat dokter baru dan simpan data
    $dokter = Dokter::create([
        'id_poli' => $validated['id_poli'],
        'nama' => $validated['nama'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']), // Hash password sebelum menyimpan
        'alamat' => $validated['alamat'],                // Menyimpan alamat jika ada
        'no_hp' => $validated['no_hp'],                  // Menyimpan nomor HP jika ada
    ]);

    // Redirect ke halaman daftar dokter dengan pesan sukses
    return redirect()->route('dokter.index')->with('success', 'Dokter berhasil ditambahkan.');
}


    /**
     * Memperbarui data dokter.
     */
    public function update(Request $request, $id)
{
    // Validasi data input
    $validatedData = $request->validate([
        'nama' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:dokter,email,' . $id,
        'alamat' => 'nullable|string|max:500',
        'no_hp' => 'nullable|string|max:15',
        'id_poli' => 'required|exists:poli,id', // Pastikan id_poli valid
        'password' => 'nullable|string|min:8|confirmed', // Optional password validation
    ]);

    // Temukan dokter berdasarkan ID
    $doctor = Dokter::find($id);
    if (!$doctor) {
        return response()->json(['message' => 'Doctor not found'], 404);
    }

    // Perbarui data dokter
    $doctor->nama = $validatedData['nama'];
    $doctor->email = $validatedData['email'];
    $doctor->alamat = $validatedData['alamat'];
    $doctor->no_hp = $validatedData['no_hp'];
    $doctor->id_poli = $validatedData['id_poli'];

    // Update password jika ada
    if ($request->has('password') && !empty($request->password)) {
        $doctor->password = bcrypt($validatedData['password']);
    }

    $doctor->save(); // Simpan ke database

    return response()->json(['message' => 'Doctor updated successfully', 'doctor' => $doctor], 200);
}


    /**
     * Menghapus data dokter.
     */
    public function destroy(Dokter $dokter)
    {
        $dokter->delete();
        return redirect()->route('dokter.index')->with('success', 'Dokter berhasil dihapus.');
    }

    public function getDokter(Request $request)
    {
        // Get poli_id from the request if provided
        $poli_id = $request->input('poli_id');

        // Fetch doctors, optionally filtering by poli
        $dokters = Dokter::when($poli_id, function ($query, $poli_id) {
            return $query->where('poli_id', $poli_id);  // Adjust according to your database schema
        })->get();

        return response()->json($dokters);
    }

    /**
     * Get the list of poli (specializations)
     */
    public function getPoli()
    {
        // Fetch all poli data for dropdown
        $polis = Poli::all();

        return response()->json($polis);
    }
    public function hashPassword(Request $request)
    {
        // Validate the incoming request to ensure the password field is provided
        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        // Hash the password
        $hashedPassword = Hash::make($request->password);

        // Return the hashed password as a response
        return response()->json(['hashedPassword' => $hashedPassword]);
    }
}
