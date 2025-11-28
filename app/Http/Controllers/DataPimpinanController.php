<?php

namespace App\Http\Controllers;

use App\Models\DataPimpinan;
use Illuminate\Http\Request;

class DataPimpinanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataPimpinan = DataPimpinan::latest()->paginate(10);
        return view('master.datapimpinan.index', compact('dataPimpinan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master.datapimpinan.index.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pimpinan' => 'required|string|max:100|unique:data_pimpinan,nama_pimpinan' // 🔥 TAMBAHKAN unique
        ], [
            'nama_pimpinan.unique' => 'Nama pimpinan sudah ada, gunakan nama lain.' // 🔥 Custom error message
        ]);

        DataPimpinan::create([
            'nama_pimpinan' => $request->nama_pimpinan
        ]);

        return redirect()->route('data-pimpinan.index')
            ->with('success', 'Data pimpinan berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(DataPimpinan $dataPimpinan)
    {
        return view('master.datapimpinan.index.show', compact('dataPimpinan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DataPimpinan $dataPimpinan)
    {
        return view('master.datapimpinan.index', compact('dataPimpinan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DataPimpinan $dataPimpinan)
    {
        $request->validate([
            // 🔥 Tambahkan unique dengan pengecualian ID yang sedang diedit
            'nama_pimpinan' => 'required|string|max:100|unique:data_pimpinan,nama_pimpinan,' . $dataPimpinan->id_pimpinan . ',id_pimpinan'
        ], [
            'nama_pimpinan.unique' => 'Nama pimpinan sudah ada, gunakan nama lain.' // 🔥 Custom error message
        ]);

        $dataPimpinan->update([
            'nama_pimpinan' => $request->nama_pimpinan
        ]);

        return redirect()->route('data-pimpinan.index')
            ->with('success', 'Data pimpinan berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DataPimpinan $dataPimpinan)
    {
        try {
            $dataPimpinan->delete();
            return redirect()->route('data-pimpinan.index')
                ->with('success', 'Data pimpinan berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            // 🔥 Handle jika data masih digunakan di tabel lain
            if ($e->getCode() == '23000') {
                return redirect()->route('data-pimpinan.index')
                    ->with('error', 'Data pimpinan tidak bisa dihapus karena masih digunakan oleh data pegawai.');
            }
            return redirect()->route('data-pimpinan.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}