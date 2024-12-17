<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CRUDObatController extends Controller
{
    /**
     * Menampilkan daftar obat.
     */
    public function index()
    {
        $obat = Obat::all();
        return Inertia::render('Dashboard/CrudObat', [
            'obat' => $obat,
        ]);
    }

    /**
     * Menyimpan data obat baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_obat' => 'required|string|max:255',
            'kemasan' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
        ]);

        Obat::create($validated);

        return redirect()->route('obat.index')->with('success', 'Obat berhasil ditambahkan.');
    }

    /**
     * Memperbarui data obat.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nama_obat' => 'required|string|max:255',
            'kemasan' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
        ]);

        // Temukan obat berdasarkan ID
        $obat = Obat::find($id);
        if (!$obat) {
            return response()->json(['message' => 'Obat tidak ditemukan'], 404);
        }

        // Perbarui data obat
        $obat->nama_obat = $validatedData['nama_obat'];
        $obat->kemasan = $validatedData['kemasan'];
        $obat->harga = $validatedData['harga'];

        $obat->save(); // Simpan ke database

        return response()->json(['message' => 'Obat berhasil diperbarui', 'obat' => $obat], 200);
    }

    /**
     * Menghapus data obat.
     */
    public function destroy($id)
    {
        $obat = Obat::find($id);

        if (!$obat) {
            return response()->json(['message' => 'Obat not found'], 404);
        }

        $obat->delete();

        return redirect()->route('obat.index')->with('success', 'Obat berhasil dihapus.');
    }

    /**
     * Mendapatkan data obat berdasarkan nama.
     */
    public function getObat(Request $request)
    {
        $obat = Obat::all();

        return response()->json($obat);
    }
}
