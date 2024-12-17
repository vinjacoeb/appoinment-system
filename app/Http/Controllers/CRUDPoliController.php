<?php

namespace App\Http\Controllers;

use App\Models\Poli;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CRUDPoliController extends Controller
{
    /**
     * Menampilkan daftar poli.
     */
    public function index()
    {
        $polis = Poli::all();
        return Inertia::render('Dashboard/CrudPoli', [
            'polis' => $polis,
        ]);
    }

    /**
     * Menyimpan data poli baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_poli' => 'required|string|max:255|unique:poli,nama_poli',
            'keterangan' => 'nullable|string|max:500',
        ]);

        Poli::create($validated);

        return redirect()->route('poli.index')->with('success', 'Poli berhasil ditambahkan.');
    }

    /**
     * Memperbarui data poli.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_poli' => 'required|string|max:255|unique:poli,nama_poli,' . $id,
            'keterangan' => 'nullable|string|max:500',
        ]);

        $poli = Poli::find($id);

        if (!$poli) {
            return response()->json(['message' => 'Poli not found'], 404);
        }

        $poli->update($validated);

        return redirect()->route('poli.index')->with('success', 'Poli berhasil diperbarui.');
    }

    /**
     * Menghapus data poli.
     */
    public function destroy($id)
    {
        $poli = Poli::find($id);

        if (!$poli) {
            return response()->json(['message' => 'Poli not found'], 404);
        }

        $poli->delete();

        return redirect()->route('poli.index')->with('success', 'Poli berhasil dihapus.');
    }
    public function getPoli()
    {
        // Fetch all poli data for dropdown
        $polis = Poli::all();

        return response()->json($polis);
    }
}
