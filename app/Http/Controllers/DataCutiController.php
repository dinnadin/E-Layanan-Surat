<?php

namespace App\Http\Controllers;

use App\Models\DataCuti;
use App\Models\Pengguna;
use App\Models\SystemSetting; // ✅ TAMBAHKAN INI
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DataCutiImport;
use App\Models\PengajuanCuti;
use App\Exports\DataCutiExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; // ✅ TAMBAHKAN INI

class DataCutiController extends Controller
{
    // Tampilkan data rekap cuti
    public function index()
    {
        $data = DataCuti::with('pengguna')->paginate(8);

          // ✅ CEK APAKAH BUTTON BISA DIKLIK
        $canUpdateCuti = $this->canUpdateCutiTahunan();
        $updateMessage = $this->getUpdateMessage();
        
        return view('data_cuti.index', compact('data', 'canUpdateCuti', 'updateMessage'));
    }

public function create()
{
    // Ambil ID pengguna yang sudah punya data cuti
    $sudahPunyaCuti = DataCuti::pluck('id_pengguna')->toArray();

    // Ambil pegawai yang BELUM punya data cuti
    // urut berdasarkan id_pengguna terkecil
    // dan TIDAK termasuk admin
    $pegawai = Pengguna::whereNotIn('id_pengguna', $sudahPunyaCuti)
        ->where('role', '!=', 'admin')   // ⬅️ dikecualikan admin
        ->orderBy('id_pengguna', 'asc')
        ->first();

    // Jika semua pegawai sudah punya data cuti
    if (!$pegawai) {
        return redirect()->route('data_cuti.index')
            ->with('error', 'Semua pegawai (kecuali admin) sudah memiliki data cuti.');
    }

    return view('data_cuti.create', compact('pegawai'));
}

    // ✅ UPDATE: Simpan data baru (tanpa N-2)
    public function store(Request $request)
    {
        $request->validate([
            'id_pengguna' => 'required',
            'n_1' => 'required|integer',
            'n' => 'required|integer',
            'diambil' => 'required|integer',
        ]);

        // Hitung jumlah TANPA N-2
        $jumlah = $request->n_1 + $request->n;
        $sisa = $jumlah - $request->diambil;

        DataCuti::create([
            'id_pengguna' => $request->id_pengguna,
            'n_2' => 0,  // Selalu 0
            'n_1' => $request->n_1,
            'n' => $request->n,
            'jumlah' => $jumlah,
            'diambil' => $request->diambil,
            'sisa' => $sisa,
        ]);

        return redirect()->route('data_cuti.index')->with('success', 'Data berhasil ditambahkan');
    }

    // Import Excel
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new DataCutiImport, $request->file('file'));

        return redirect()->route('data_cuti.index')->with('success', 'Data cuti berhasil diimport!');
    }

    public function export()
    {
        return Excel::download(new DataCutiExport, 'data_cuti.xlsx');
    }

    public function destroy($id)
{
    $dataCuti = DataCuti::findOrFail($id);

    $adaPengajuan = PengajuanCuti::where('id_pengguna', $dataCuti->id_pengguna)->exists();

    if ($adaPengajuan) {
        return redirect()->route('data_cuti.index')
            ->with('error', 'Data cuti tidak dapat dihapus karena pengguna masih memiliki pengajuan cuti.');
    }

    $dataCuti->delete();

    // 🔥 AUTO RESET SYSTEM SETTING JIKA DATA KOSONG
    if (DataCuti::count() == 0) {
        SystemSetting::where('key', 'last_update_cuti_tahunan')->delete();
    }

    return redirect()->route('data_cuti.index')
        ->with('success', 'Data berhasil dihapus');
}

    // ✅ Bulk Delete dengan validasi
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        if (!$ids || count($ids) === 0) {
            return redirect()->back()
                ->with('error', 'Tidak ada data yang dipilih untuk dihapus.');
        }

        $dataCutiList = DataCuti::whereIn('id', $ids)->get();
        
        $tidakBisaHapus = [];
        $bisaHapus = [];
        
        foreach ($dataCutiList as $dataCuti) {
            $adaPengajuan = PengajuanCuti::where('id_pengguna', $dataCuti->id_pengguna)->exists();
            
            if ($adaPengajuan) {
                $tidakBisaHapus[] = $dataCuti->pengguna->nama_lengkap ?? 'ID: ' . $dataCuti->id_pengguna;
            } else {
                $bisaHapus[] = $dataCuti->id;
            }
        }
        
        if (count($tidakBisaHapus) > 0) {
            $namaList = implode(', ', $tidakBisaHapus);
            
            if (count($bisaHapus) > 0) {
                DataCuti::whereIn('id', $bisaHapus)->delete();
                
                return redirect()->back()
                    ->with('warning', count($bisaHapus) . ' data berhasil dihapus. Namun data berikut tidak dapat dihapus karena masih memiliki pengajuan cuti: ' . $namaList);
            }
            
            return redirect()->back()
                ->with('error', 'Data tidak dapat dihapus karena pengguna berikut masih memiliki pengajuan cuti: ' . $namaList);
        }
        
        DataCuti::whereIn('id', $bisaHapus)->delete();
        
                // Reset jika data kosong
if (DataCuti::count() == 0) {
    SystemSetting::where('key', 'last_update_cuti_tahunan')->delete();
}
        return redirect()->back()
            ->with('success', count($bisaHapus) . ' data cuti berhasil dihapus.');
    }

    public function edit($id)
    {
        $cuti = DataCuti::with('pengguna')->findOrFail($id);
        return view('data_cuti.edit', compact('cuti'));
    }

    // ✅ UPDATE: Update data (tanpa N-2)
    public function update(Request $request, $id)
    {
        $cuti = DataCuti::findOrFail($id);

        // Simpan nilai lama
        $n_lama = $cuti->n;

        // Update nilai baru
        $cuti->n_2 = 0;  // Selalu 0
        $cuti->n_1 = $request->n_1;
        $cuti->n = $request->n;

        // Hitung selisih perubahan n
        $selisih_n = $cuti->n - $n_lama;

        // Jika n bertambah, kurangi diambil sebanyak selisih
        if ($selisih_n > 0) {
            $cuti->diambil = max(0, $cuti->diambil - $selisih_n);
        }

        // Hitung ulang jumlah dan sisa (TANPA N-2)
        $cuti->jumlah = $cuti->n_1 + $cuti->n;
        $cuti->sisa = $cuti->jumlah - $cuti->diambil;

        $cuti->save();

        return redirect()->route('data_cuti.index')->with('success', 'Data cuti berhasil diperbarui.');
    }

    private function canUpdateCutiTahunan()
{
    $today = Carbon::now();

    // 🔥 KONDISI BARU:
    // Jika tabel data_cuti kosong → tombol harus aktif
    if (DataCuti::count() == 0) {
        return true;
    }

    // Cek apakah sudah melakukan update tahun ini
    $lastUpdate = SystemSetting::getValue('last_update_cuti_tahunan');

    if ($lastUpdate) {
        $lastUpdateDate = Carbon::parse($lastUpdate);

        // Jika tahun sama, berarti sudah update → tombol nonaktif
        if ($lastUpdateDate->year == $today->year) {
            return false;
        }
    }

    return true; // tombol aktif
}
    
  private function getUpdateMessage()
{
    $today = Carbon::now();

    $lastUpdate = SystemSetting::getValue('last_update_cuti_tahunan');

    if ($lastUpdate) {
        $lastUpdateDate = Carbon::parse($lastUpdate);

        if ($lastUpdateDate->year == $today->year) {
            return 'Update cuti tahunan sudah dilakukan pada: ' . $lastUpdateDate->format('d-m-Y H:i:s');
        }
    }

    return 'Button aktif! Klik untuk update cuti tahunan.';
}

    // ✅ METHOD: Trigger Update Cuti Tahunan Manual
    public function triggerUpdateCutiTahunan()
    {
        try {
            // ✅ CEK APAKAH BISA UPDATE
            if (!$this->canUpdateCutiTahunan()) {
                $message = $this->getUpdateMessage();
                return redirect()->back()->with('error', 'Tidak dapat melakukan update: ' . $message);
            }
            
            // Jalankan command
            Artisan::call('cuti:update-tahunan');
            
            // ✅ SIMPAN WAKTU UPDATE (OTOMATIS pakai tanggal real)
            SystemSetting::setValue('last_update_cuti_tahunan', Carbon::now()->toDateTimeString());
            
            // Ambil output command
            $output = Artisan::output();
            
            // Log aktivitas
            Log::info('Manual Trigger Update Cuti Tahunan', [
                'user' => auth()->user()->nama_lengkap ?? 'Unknown',
                'user_id' => auth()->user()->id_pengguna ?? null,
                'waktu' => now()
            ]);
            
            return redirect()->back()->with('success', 'Update cuti tahunan berhasil dijalankan! Data telah diperbarui untuk tahun baru.');
            
        } catch (\Exception $e) {
            Log::error('Manual Update Cuti Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user' => auth()->user()->nama_lengkap ?? 'Unknown'
            ]);
            
            return redirect()->back()->with('error', 'Gagal menjalankan update: ' . $e->getMessage());
        }
    }
}