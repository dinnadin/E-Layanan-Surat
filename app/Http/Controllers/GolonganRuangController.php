<?php

namespace App\Http\Controllers;

use App\Models\PangkatGolonganRuang;
use Illuminate\Http\Request;

class GolonganRuangController extends Controller
{
    public function index()
    {
    $data = PangkatGolonganRuang::paginate(10);
        return view('master.pangkat.index', compact('data'));
    }

    public function store(Request $request)
    {
        // Trim & format input
        $pangkat = trim($request->pangkat);
        $golongan = strtoupper(trim($request->golongan));
        $ruang = strtolower(trim($request->ruang));

        // Validasi
        $request->merge([
            'pangkat' => $pangkat,
            'golongan' => $golongan,
            'ruang' => $ruang,
        ]);

        $request->validate([
            'pangkat' => 'required|string|max:255',
            'golongan' => 'required|in:I,II,III,IV',
            'ruang' => ['required', 'string', 'size:1', 'regex:/^[a-e]$/'],
        ], [
            'golongan.in' => 'Golongan hanya boleh I, II, III, atau IV.',
            'ruang.regex' => 'Ruang hanya boleh huruf a, b, c, d, atau e.',
            'ruang.size' => 'Ruang harus 1 huruf saja.',
        ]);

        // Cek duplikat di DB
        $exists = PangkatGolonganRuang::where('pangkat', $pangkat)
            ->where('golongan', $golongan)
            ->where('ruang', $ruang)
            ->first();

        if ($exists) {
            return redirect()->route('pangkat.index')->with('error', 'Pangkat/Golongan/Ruang sudah ada!');
        }

        try {
            PangkatGolonganRuang::create([
                'pangkat' => $pangkat,
                'golongan' => $golongan,
                'ruang' => $ruang,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('pangkat.index')->with('error', 'Terjadi kesalahan, kemungkinan data duplikat!');
        }

        return redirect()->route('pangkat.index')->with('success', 'Pangkat/Golongan berhasil ditambahkan');
    }

    public function update(Request $request, PangkatGolonganRuang $pangkat)
    {
        // Trim & format input
        $newPangkat = trim($request->pangkat);
        $newGolongan = strtoupper(trim($request->golongan));
        $newRuang = strtolower(trim($request->ruang));

        $request->merge([
            'pangkat' => $newPangkat,
            'golongan' => $newGolongan,
            'ruang' => $newRuang,
        ]);

        // Validasi
        $request->validate([
            'pangkat' => 'required|string|max:255',
            'golongan' => 'required|in:I,II,III,IV',
            'ruang' => ['required', 'string', 'size:1', 'regex:/^[a-e]$/'],
        ], [
            'golongan.in' => 'Golongan hanya boleh I, II, III, atau IV.',
            'ruang.regex' => 'Ruang hanya boleh huruf a, b, c, d, atau e.',
            'ruang.size' => 'Ruang harus 1 huruf saja.',
        ]);

        // Cek duplikat kecuali dirinya sendiri
        $exists = PangkatGolonganRuang::where('pangkat', $newPangkat)
            ->where('golongan', $newGolongan)
            ->where('ruang', $newRuang)
            ->where('id_pangkat', '!=', $pangkat->id_pangkat)
            ->first();

        if ($exists) {
            return redirect()->route('pangkat.index')->with('error', 'Pangkat/Golongan/Ruang sudah ada!');
        }

        try {
            $pangkat->update([
                'pangkat' => $newPangkat,
                'golongan' => $newGolongan,
                'ruang' => $newRuang,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('pangkat.index')->with('error', 'Terjadi kesalahan saat update!');
        }

        return redirect()->route('pangkat.index')->with('success', 'Pangkat/Golongan berhasil diperbarui');
    }

    public function destroy(PangkatGolonganRuang $pangkat)
    {
        $pangkat->delete();
        return redirect()->route('pangkat.index')->with('success', 'Pangkat/Golongan berhasil dihapus');
    }
}
