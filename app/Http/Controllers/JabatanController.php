<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index()
    {
        $jabatans = Jabatan::paginate(7);
        return view('master.jabatan.index', compact('jabatans'));
    }

    public function store(Request $request)
    {
        // ✅ PERBAIKAN: min:50 dan max:70 pakai titik dua (:)
        $request->validate([
            'nama_jabatan' => 'required|string|max:255',
            'usia_pensiun' => 'required|integer|min:50|max:70',
        ]);

        // Cek duplikat
        $exists = Jabatan::where('nama_jabatan', $request->nama_jabatan)->first();
        if ($exists) {
            return back()->with('error', 'Nama Jabatan sudah ada!');
        }

        // Simpan data
        Jabatan::create([
            'nama_jabatan' => $request->nama_jabatan,
            'usia_pensiun' => $request->usia_pensiun,
        ]);

        return redirect()->route('jabatan.index')->with('success', 'Jabatan berhasil ditambahkan');
    }

    public function update(Request $request, Jabatan $jabatan)
    {
        // ✅ PERBAIKAN: min:50 dan max:70 pakai titik dua (:)
        $request->validate([
            'nama_jabatan' => 'required|string|max:255',
            'usia_pensiun' => 'required|integer|min:50|max:70',
        ]);

        // Cek duplikat selain dirinya sendiri
        $exists = Jabatan::where('nama_jabatan', $request->nama_jabatan)
                          ->where('id_jabatan', '!=', $jabatan->id_jabatan)
                          ->first();
        if ($exists) {
            return back()->with('error', 'Nama Jabatan sudah ada!');
        }

        // ✅ PERBAIKAN: Langsung pakai $jabatan, bukan findOrFail($id)
        $jabatan->update([
            'nama_jabatan' => $request->nama_jabatan,
            'usia_pensiun' => $request->usia_pensiun,
        ]);

        return redirect()->route('jabatan.index')->with('success', 'Jabatan berhasil diperbarui');
    }

    public function destroy(Jabatan $jabatan)
    {
        $jabatan->delete();
        return redirect()->route('jabatan.index')->with('success', 'Jabatan berhasil dihapus');
    }
}