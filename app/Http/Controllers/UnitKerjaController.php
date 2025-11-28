<?php

namespace App\Http\Controllers;

use App\Models\UnitKerja;
use Illuminate\Http\Request;

class UnitKerjaController extends Controller
{
    public function index()
    {
        $data = UnitKerja::paginate(10);
        return view('master.unit.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_unit_kerja' => 'required|string|max:255',
            'sub_unit_kerja'  => 'nullable|string|max:255',
        ]);

        // Cek duplikat (gabungan nama_unit_kerja + sub_unit_kerja)
        $exists = UnitKerja::where('nama_unit_kerja', $request->nama_unit_kerja)
            ->where('sub_unit_kerja', $request->sub_unit_kerja)
            ->first();

        if ($exists) {
            return back()->with('error', 'Unit Kerja sudah ada!');
        }

        UnitKerja::create($request->only('nama_unit_kerja', 'sub_unit_kerja'));
        return redirect()->route('unit.index')->with('success', 'Unit Kerja berhasil ditambahkan');
    }

    public function update(Request $request, UnitKerja $unit)
    {
        $request->validate([
            'nama_unit_kerja' => 'required|string|max:255',
            'sub_unit_kerja'  => 'nullable|string|max:255',
        ]);

        // Cek duplikat selain dirinya sendiri
        $exists = UnitKerja::where('nama_unit_kerja', $request->nama_unit_kerja)
            ->where('sub_unit_kerja', $request->sub_unit_kerja)
            ->where('id_unit_kerja', '!=', $unit->id_unit_kerja)
            ->first();

        if ($exists) {
            return back()->with('error', 'Unit Kerja sudah ada!');
        }

        $unit->update($request->only('nama_unit_kerja', 'sub_unit_kerja'));
        return redirect()->route('unit.index')->with('success', 'Unit Kerja berhasil diperbarui');
    }

    public function destroy(UnitKerja $unit)
    {
        $unit->delete();
        return redirect()->route('unit.index')->with('success', 'Unit Kerja berhasil dihapus');
    }
}
