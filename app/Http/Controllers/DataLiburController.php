<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataLibur;

class DataLiburController extends Controller
{
    // Tampilkan semua data libur
    public function index()
    {
        $libur = DataLibur::orderBy('tanggal', 'asc')->get();
        return view('data_libur.index', compact('libur'));
    }

    // Form tambah data libur
    public function create()
    {
        return view('data_libur.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'tanggal'   => 'required|string',
        'deskripsi' => 'nullable|string|max:255',
    ]);

    $tanggalArray = explode(',', $request->tanggal);
    $tanggalDuplikat = []; // 🔥 Simpan tanggal yang duplikat
    $tanggalBerhasil = []; // 🔥 Simpan tanggal yang berhasil

    foreach ($tanggalArray as $tgl) {
        $tgl = trim($tgl);
        
        if ($tgl) {
            // 🔥 Cek apakah tanggal sudah ada
            $exists = \App\Models\DataLibur::where('tanggal', $tgl)->first();
            
            if ($exists) {
                // Tanggal sudah ada, simpan ke array duplikat
                $tanggalDuplikat[] = [
                    'tanggal' => \Carbon\Carbon::parse($tgl)->format('d-m-Y'),
                    'deskripsi' => $exists->deskripsi
                ];
            } else {
                // Tanggal belum ada, simpan
                \App\Models\DataLibur::create([
                    'tanggal'   => $tgl,
                    'deskripsi' => $request->deskripsi ?? '',
                ]);
                $tanggalBerhasil[] = \Carbon\Carbon::parse($tgl)->format('d-m-Y');
            }
        }
    }

    // 🔥 Jika ada duplikat, kirim ke view dengan session
    if (!empty($tanggalDuplikat)) {
        return redirect()->route('data_libur.index')
            ->with('warning_modal', [
                'title' => 'Beberapa Tanggal Sudah Ada!',
                'duplikat' => $tanggalDuplikat,
                'berhasil' => $tanggalBerhasil,
            ]);
    }

    return redirect()->route('data_libur.index')
        ->with('success', 'Data libur berhasil ditambahkan');
}

    // Form edit data libur
    public function edit($id)
    {
        $libur = DataLibur::findOrFail($id);
        return view('data_libur.edit', compact('libur'));
    }

    // Update data libur
    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal'   => 'required|date|unique:data_libur,tanggal,' . $id . ',id_tanggal',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $libur = DataLibur::findOrFail($id);
        $libur->update([
            'tanggal'   => $request->tanggal,
            'deskripsi' => $request->deskripsi ?? '', // pastikan tidak null
        ]);

        return redirect()->route('data_libur.index')->with('success', 'Data libur berhasil diupdate');
    }

    // Hapus data libur
    public function destroy($id)
    {
        $libur = DataLibur::findOrFail($id);
        $libur->delete();

        return redirect()->route('data_libur.index')->with('success', 'Data libur berhasil dihapus');
    }
}
