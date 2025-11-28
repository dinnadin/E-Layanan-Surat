<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use App\Models\DataPimpinan;
use App\Models\PangkatGolonganRuang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PenggunaImport;
use App\Exports\PenggunaExport;
use Carbon\Carbon;
use Illuminate\Support\Str;


class PenggunaController extends Controller
{
public function index(Request $request)
{
    // 🔥 Cek otomatis setiap kali halaman dibuka
    foreach (Pengguna::all() as $pegawai) {
        $pegawai->cekDanUpdateStatusPensiun();
    }

    $search = $request->get('search');
    $filter = $request->get('filter');

    $pengguna = \App\Models\Pengguna::with(['jabatan', 'pangkatGolongan', 'unitKerja', 'pimpinan'])
        ->when($search, function($query, $search) {
            // ✅ Search HANYA berdasarkan: Nama Lengkap, NIP, Unit Kerja
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhereHas('unitKerja', function($q) use ($search) {
                      $q->where('nama_unit_kerja', 'like', "%{$search}%");
                  });
            });
        })
        ->when($filter, function ($query, $filter) {
            if ($filter === 'aktif') {
                $query->where('status_aktif', '=', 'aktif');
            } elseif ($filter === 'pensiun') {
                $query->where(function($q) {
                    $q->where('status_aktif', '=', 'pensiun')
                      ->orWhere('status_aktif', '=', 'nonaktif');
                });
            }
        })
        ->orderBy('nama_lengkap', 'asc')
        ->paginate(5)
        ->appends($request->except('page'));

    // ✅ AJAX Request - Return hanya table content
    if ($request->ajax()) {
        return view('pengguna.index', compact('pengguna'))->render();
    }

    return view('pengguna.index', compact('pengguna'));
}

public function show($id_pengguna)
{
    $pegawai = Pengguna::with(['pimpinan', 'jabatan', 'pangkatGolongan', 'unitKerja'])->findOrFail($id_pengguna);
    return view('pengguna.detail', compact('pegawai'));
}

// Method CREATE - Tambahkan $dataPimpinan
    public function create()
    {
        $pangkats = PangkatGolonganRuang::all();
        $jabatans = Jabatan::all();
        $unitKerjas = UnitKerja::all();
        $dataPimpinan = DataPimpinan::all();
        
        return view('pengguna.create', compact('pangkats', 'jabatans', 'unitKerjas', 'dataPimpinan'));
    }


 // 🔥 FUNGSI HELPER: Generate username dari nama lengkap
private function generateUsername($namaLengkap)
{
    // Langsung ambil nama lengkap tanpa hapus gelar
    $nama = trim($namaLengkap);
    $words = explode(' ', $nama);
    
    // Jika hanya 1 kata, gunakan kata tersebut
    if (count($words) == 1) {
        $username = strtolower($words[0]);
    } 
    // Jika lebih dari 1 kata, ambil kata pertama + terakhir
    else {
        $firstName = strtolower($words[0]);
        $lastName = strtolower(end($words));
        $username = $firstName . $lastName;
    }
    
    // ⚠️ Tidak ada pembersihan karakter spesial
    // ⚠️ Tidak ada penghapusan gelar
    
    // Cek apakah username sudah ada
    $baseUsername = $username;
    $counter = 1;
    
    while (Pengguna::where('username', $username)->exists()) {
        $username = $baseUsername . $counter;
        $counter++;
    }
    
    return $username;
}

private function cekDuplikasiTandaTangan($file, $excludeUserId = null)
{
    $fileHash = md5_file($file->getRealPath());
    
    $query = Pengguna::whereNotNull('tanda_tangan');
    
    if ($excludeUserId) {
        $query->where('id_pengguna', '!=', $excludeUserId);
    }
    
    $penggunaList = $query->get();
    
    foreach ($penggunaList as $pengguna) {
        $ttdPath = storage_path('app/public/' . $pengguna->tanda_tangan);
        
        if (file_exists($ttdPath)) {
            $existingHash = md5_file($ttdPath);
            
            if ($existingHash === $fileHash) {
                return $pengguna; // Return pengguna yang memiliki TTD sama
            }
        }
    }
    
    return null; // Tidak ada duplikasi
}

public function store(Request $request)
{
    // Validasi NIP
    $nipExists = Pengguna::where('nip', $request->nip)->exists();
    if ($nipExists) {
        return redirect()->back()
                         ->withInput()
                         ->with('error', 'NIP sudah ada, silakan gunakan NIP lain.');
    }

    if (strlen($request->nip) < 16) {
        return redirect()->back()
                         ->withInput()
                         ->with('error', 'NIP kurang, tolong lengkapi. NIP harus 16 digit.');
    }

    $validated = $request->validate([
        'nama_lengkap' => 'required|string|max:255',
        'nip' => 'required|string|max:16',
        'password' => 'required|string|max:16',
        'tanggal_lahir' => 'nullable|date|before:today',
        'id_pangkat_golongan_ruang' => 'required|exists:data_pangkat,id_pangkat',
        'id_jabatan' => 'required|exists:data_jabatan,id_jabatan',
        'id_unit_kerja' => 'required|exists:data_unit_kerja,id_unit_kerja',
        'id_pimpinan' => 'nullable|exists:data_pimpinan,id_pimpinan',
        'tanggal_masuk' => 'required|date',
        'role' => 'required|in:admin,Kepala,pegawai',
        'tanda_tangan' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'status_kepegawaian' => 'nullable|string|in:Aparatur Sipil Negara,Tenaga Harian Lepas,Pegawai Pemerintah dengan Perjanjian Kerja', // ✅ TAMBAHAN dengan validasi pilihan
    ]);

    // Hitung masa kerja
    $tanggalMasuk = \Carbon\Carbon::parse($validated['tanggal_masuk']);
    $now = \Carbon\Carbon::now();
    $diff = $tanggalMasuk->diff($now);
    $masaKerja = $diff->y . ' tahun, ' . $diff->m . ' bulan, ' . $diff->d . ' hari';

    // Generate username
    $username = $this->generateUsername($validated['nama_lengkap']);

    // ✅ FINAL FIX: Upload tanda tangan TANPA prefix storage/
    $tandaTanganPath = null;
    if ($request->hasFile('tanda_tangan')) {
        $file = $request->file('tanda_tangan');
        
        // Cek duplikasi hash file
        $fileHash = md5_file($file->getRealPath());
        
        $penggunaList = Pengguna::whereNotNull('tanda_tangan')->get();
        $ttdExists = null;
        
        foreach ($penggunaList as $pengguna) {
            // Cek file dari storage/app/public/
            $ttdPath = storage_path('app/public/' . $pengguna->tanda_tangan);
            
            if (file_exists($ttdPath)) {
                $existingHash = md5_file($ttdPath);
                
                if ($existingHash === $fileHash) {
                    $ttdExists = $pengguna;
                    break;
                }
            }
        }
        
        if ($ttdExists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['tanda_tangan' => 'Tanda tangan ini sudah digunakan oleh pegawai lain'])
                ->with('error_modal', [
                    'title' => 'Tanda Tangan Sudah Ada!',
                    'message' => 'Tanda tangan yang Anda upload sudah digunakan oleh pegawai: <strong>' . $ttdExists->nama_lengkap . '</strong>',
                    'submessage' => 'Silakan upload tanda tangan yang berbeda.'
                ]);
        }
        
        // ✅ Simpan file dan ambil path lengkap yang dikembalikan storeAs()
        $filename = 'ttd_' . $request->nip . '_' . time() . '.' . $file->getClientOriginalExtension();
        $tandaTanganPath = $file->storeAs('tanda_tangan', $filename, 'public');
        
        // ✅ CRITICAL: storeAs() sudah return 'tanda_tangan/ttd_xxx.png' (tanpa storage/)
        // Jadi TIDAK perlu modifikasi lagi, langsung simpan ke database
    }

    Pengguna::create([
        'username' => $username,
        'nama_lengkap' => $validated['nama_lengkap'],
        'nip' => $validated['nip'],
        'password' => bcrypt($validated['password']),
        'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
        'id_pangkat_golongan_ruang' => $validated['id_pangkat_golongan_ruang'],
        'id_jabatan' => $validated['id_jabatan'],
        'id_unit_kerja' => $validated['id_unit_kerja'],
        'id_pimpinan' => $validated['id_pimpinan'] ?? null,
        'tanggal_masuk' => $validated['tanggal_masuk'],
        'masa_kerja' => $masaKerja,
        'role' => $validated['role'],
        'tanda_tangan' => $tandaTanganPath,
        'status_kepegawaian' => $validated['status_kepegawaian'] ?? null, // ✅ TAMBAHAN
    ]);

    return redirect()->route('data.pegawai')
                     ->with('success', 'Data pegawai berhasil ditambahkan');
}

// ✅ FUNGSI UPDATE - FINAL FIX
public function update(Request $request, $id)
{
    $pengguna = Pengguna::findOrFail($id);

    $request->validate([
        'nama_lengkap' => 'required|string|max:255',
        'nip' => 'required|string|max:18',
        'tanggal_lahir' => 'nullable|date|before:today',
        'tanggal_masuk' => 'nullable|date|before_or_equal:today',
        'id_pangkat_golongan_ruang' => 'required|exists:data_pangkat,id_pangkat',
        'id_jabatan' => 'required|exists:data_jabatan,id_jabatan',
        'id_unit_kerja' => 'required|exists:data_unit_kerja,id_unit_kerja',
        'id_pimpinan' => 'nullable|exists:data_pimpinan,id_pimpinan',
        'role' => 'required|in:Kepala,Admin,Pegawai',
        'tanda_tangan' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'status_kepegawaian' => 'nullable|string|in:Aparatur Sipil Negara,Tenaga Harian Lepas,Pegawai Pemerintah dengan Perjanjian Kerja', // ✅ TAMBAHAN dengan validasi pilihan
    ]);

    $nipExists = Pengguna::where('nip', $request->nip)
        ->where('id_pengguna', '!=', $id)
        ->exists();

    if ($nipExists) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'NIP sudah ada, silakan gunakan NIP lain.');
    }

    if (strlen($request->nip) < 16) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'NIP kurang, tolong lengkapi. NIP harus 16 digit.');
    }

    // ✅ FINAL FIX: Handle tanda tangan
    $tandaTanganPath = $pengguna->tanda_tangan;
    
    // Jika centang "Hapus tanda tangan"
    if ($request->has('hapus_ttd') && $request->hapus_ttd == '1') {
        if ($pengguna->tanda_tangan && \Storage::disk('public')->exists($pengguna->tanda_tangan)) {
            \Storage::disk('public')->delete($pengguna->tanda_tangan);
        }
        $tandaTanganPath = null;
    }
    
    // Jika upload tanda tangan baru
    if ($request->hasFile('tanda_tangan')) {
        $file = $request->file('tanda_tangan');
        
        // Cek duplikasi hash file
        $fileHash = md5_file($file->getRealPath());
        
        $penggunaList = Pengguna::where('id_pengguna', '!=', $id)
            ->whereNotNull('tanda_tangan')
            ->get();
        
        $ttdExists = null;
        
        foreach ($penggunaList as $penggunaLain) {
            $ttdPath = storage_path('app/public/' . $penggunaLain->tanda_tangan);
            
            if (file_exists($ttdPath)) {
                $existingHash = md5_file($ttdPath);
                
                if ($existingHash === $fileHash) {
                    $ttdExists = $penggunaLain;
                    break;
                }
            }
        }
        
        if ($ttdExists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['tanda_tangan' => 'Tanda tangan ini sudah digunakan oleh pegawai lain'])
                ->with('error_modal', [
                    'title' => 'Tanda Tangan Sudah Ada!',
                    'message' => 'Tanda tangan yang Anda upload sudah digunakan oleh pegawai: <strong>' . $ttdExists->nama_lengkap . '</strong>',
                    'submessage' => 'Silakan upload tanda tangan yang berbeda.'
                ]);
        }
        
        // Hapus tanda tangan lama
        if ($pengguna->tanda_tangan && \Storage::disk('public')->exists($pengguna->tanda_tangan)) {
            \Storage::disk('public')->delete($pengguna->tanda_tangan);
        }
        
        // ✅ Simpan file dan langsung gunakan return value dari storeAs()
        $filename = 'ttd_' . $request->nip . '_' . time() . '.' . $file->getClientOriginalExtension();
        $tandaTanganPath = $file->storeAs('tanda_tangan', $filename, 'public');
        
        // ✅ CRITICAL: storeAs() sudah return 'tanda_tangan/ttd_xxx.png' (tanpa storage/)
        // Jadi TIDAK perlu modifikasi lagi, langsung simpan ke database
    }

    $pengguna->update([
        'nama_lengkap' => $request->nama_lengkap,
        'nip' => $request->nip,
        'tanggal_lahir' => $request->tanggal_lahir,
        'tanggal_masuk' => $request->tanggal_masuk,
        'id_pangkat_golongan_ruang' => $request->id_pangkat_golongan_ruang,
        'id_jabatan' => $request->id_jabatan,
        'id_unit_kerja' => $request->id_unit_kerja,
        'id_pimpinan' => $request->id_pimpinan,
        'role' => $request->role,
        'tanda_tangan' => $tandaTanganPath,
        'status_kepegawaian' => $request->status_kepegawaian, // ✅ TAMBAHAN
    ]);

    if ($pengguna->sudahPensiun()) {
        $pengguna->cekDanUpdateStatusPensiun();
        return redirect()->route('data.pegawai')
            ->with('warning', 'Data berhasil diupdate. Pegawai ini sudah memasuki usia pensiun.');
    }

    return redirect()->route('data.pegawai')
        ->with('success', 'Data pegawai berhasil diupdate');
}

public function edit($id_pengguna)
{
    $pengguna = Pengguna::findOrFail($id_pengguna);
    $pangkats = PangkatGolonganRuang::all();
    $jabatans = Jabatan::all();
    $unitKerjas = UnitKerja::all();
    $dataPimpinan = DataPimpinan::all();

return view('pengguna.edit', compact('pengguna', 'pangkats', 'jabatans', 'unitKerjas', 'dataPimpinan'))
       ->with('pimpinans', $dataPimpinan);
}

    public function destroy($id_pengguna)
{
    try {
        $pengguna = Pengguna::findOrFail($id_pengguna);

        // 🔹 Hapus foto dari storage kalau ada
        if ($pengguna->foto) {
            $path = str_replace('storage/', '', $pengguna->foto);
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
        }
        // ✅ Hapus tanda tangan dari storage kalau ada
        if ($pengguna->tanda_tangan && \Storage::disk('public')->exists($pengguna->tanda_tangan)) {
            \Storage::disk('public')->delete($pengguna->tanda_tangan);
        }

        // 🔹 Hapus data pengguna dari database
        $pengguna->delete();

        return redirect()->route('data.pegawai')->with('success', 'Data pegawai dan fotonya berhasil dihapus.');
    } catch (\Illuminate\Database\QueryException $e) {
        if ($e->getCode() == '23000') {
            return redirect()->route('data.pegawai')->with('error', 'Data ini tidak bisa dihapus karena masih memiliki relasi dengan tabel lain.');
        }

        return redirect()->route('data.pegawai')->with('error', 'Terjadi kesalahan saat menghapus data.');
    }
}

 public function importExcel(Request $request)
{
    \Log::info("========== IMPORT EXCEL DIPANGGIL ==========");
    
    try {
        \Log::info("Filename: " . $request->file('file')->getClientOriginalName());
        \Log::info("File size: " . $request->file('file')->getSize() . " bytes");
        \Log::info("File mime: " . $request->file('file')->getMimeType());
    } catch (\Exception $e) {
        \Log::error("Error getting file info: " . $e->getMessage());
        return redirect()->route('data.pegawai')
            ->with('error', 'Error membaca file: ' . $e->getMessage());
    }
    
    // ✅ VALIDASI DENGAN TAMBAHAN MIME TYPE
    try {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240', // max 10MB
                function ($attribute, $value, $fail) {
                    // Cek extension
                    $extension = strtolower($value->getClientOriginalExtension());
                    $allowedExtensions = ['xlsx', 'xls', 'csv'];
                    
                    if (!in_array($extension, $allowedExtensions)) {
                        $fail('File harus berformat .xlsx, .xls, atau .csv');
                        return;
                    }
                    
                    // Cek MIME type (termasuk application/octet-stream)
                    $mimeType = $value->getMimeType();
                    $allowedMimes = [
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                        'application/vnd.ms-excel', // .xls
                        'application/x-excel', // .xls (alternative)
                        'application/excel', // .xls (alternative)
                        'text/csv', // .csv
                        'text/plain', // .csv (alternative)
                        'application/csv', // .csv (alternative)
                        'application/octet-stream', // Generic binary (ini yang penting!)
                    ];
                    
                    if (!in_array($mimeType, $allowedMimes)) {
                        $fail("MIME type tidak valid: {$mimeType}. File harus Excel atau CSV.");
                    }
                }
            ]
        ]);
        
        \Log::info("========== VALIDASI FILE BERHASIL ==========");
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error("Validasi gagal: " . json_encode($e->errors()));
        return redirect()->route('data.pegawai')
            ->withErrors($e->errors())
            ->with('error', 'File tidak valid. Pastikan format file adalah .xlsx, .xls, atau .csv');
    }
    
    // Cek apakah file bisa dibaca
    try {
        $filePath = $request->file('file')->getRealPath();
        \Log::info("File path: {$filePath}");
        
        if (!file_exists($filePath)) {
            throw new \Exception("File tidak ditemukan di path: {$filePath}");
        }
        
        if (!is_readable($filePath)) {
            throw new \Exception("File tidak bisa dibaca");
        }
        
        \Log::info("========== FILE BISA DIBACA ==========");
    } catch (\Exception $e) {
        \Log::error("Error accessing file: " . $e->getMessage());
        return redirect()->route('data.pegawai')
            ->with('error', 'File tidak bisa diakses: ' . $e->getMessage());
    }
    
    // Test baca Excel manual
    try {
        \Log::info("========== TEST BACA EXCEL MANUAL ==========");
        
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        
        \Log::info("Total rows: {$highestRow}");
        \Log::info("Highest column: {$highestColumn}");
        
        // Baca header (row 1)
        $header = [];
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $header[] = $sheet->getCell($col . '1')->getValue();
        }
        \Log::info("Header: " . json_encode($header));
        
        // Baca data row 2
        if ($highestRow >= 2) {
            $row2 = [];
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $row2[] = $sheet->getCell($col . '2')->getValue();
            }
            \Log::info("Row 2 data: " . json_encode($row2));
        }
        
        \Log::info("========== EXCEL BISA DIBACA ==========");
        
    } catch (\Exception $e) {
        \Log::error("Error reading Excel: " . $e->getMessage());
        \Log::error("Trace: " . $e->getTraceAsString());
        
        return redirect()->route('data.pegawai')
            ->with('error', 'Error membaca Excel: ' . $e->getMessage());
    }
    
    // Mulai import dengan Laravel Excel
    \Log::info("========== MULAI IMPORT DENGAN LARAVEL EXCEL ==========");
    
    try {
        $import = new PenggunaImport;
        Excel::import($import, $request->file('file'));
        
        \Log::info("========== IMPORT SELESAI ==========");
        \Log::info("Total row diproses: " . ($import->rowNumber - 1));
        
    } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
        \Log::error("========== VALIDATION ERROR ==========");
        $failures = $e->failures();
        
        foreach ($failures as $failure) {
            \Log::error("Row " . $failure->row() . ": " . json_encode($failure->errors()));
        }
        
        return redirect()->route('data.pegawai')
            ->with('error', 'Validasi Excel gagal. Cek log untuk detail.');
            
    } catch (\Exception $e) {
        \Log::error("========== ERROR SAAT IMPORT ==========");
        \Log::error("Message: " . $e->getMessage());
        \Log::error("File: " . $e->getFile());
        \Log::error("Line: " . $e->getLine());
        \Log::error("Trace: " . $e->getTraceAsString());
        
        return redirect()->route('data.pegawai')
            ->with('error', 'Import gagal: ' . $e->getMessage());
    }

    return redirect()->route('data.pegawai')
        ->with('success', 'Data pegawai berhasil diimport dari Excel.');
}

    public function exportExcel()
    {
        return Excel::download(new PenggunaExport, 'data_pegawai.xlsx');
    }

 public function bulkDelete(Request $request)
{
    // Cek apakah user memilih "Pilih Semua Data"
    if ($request->has('select_all') && $request->select_all === 'true') {
        try {
            $penggunas = Pengguna::all();
            $totalData = $penggunas->count();

            foreach ($penggunas as $pengguna) {
                if ($pengguna->foto) {
                    $path = str_replace('storage/', '', $pengguna->foto);
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                    }
                }
                $pengguna->delete();
            }

            return redirect()->back()->with('success', "Semua data pegawai ({$totalData} data) dan fotonya berhasil dihapus.");
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return redirect()->back()->with('error', 'Beberapa data tidak bisa dihapus karena masih memiliki relasi dengan tabel lain.');
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }

    // Jika hapus berdasarkan ID yang dipilih
    $ids = $request->ids;
    if (empty($ids)) {
        return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
    }

    if (is_string($ids)) {
        $ids = array_filter(explode(',', $ids));
    }

    try {
        $penggunas = Pengguna::whereIn('id_pengguna', $ids)->get();
        $count = $penggunas->count();

        foreach ($penggunas as $pengguna) {
            if ($pengguna->foto) {
                $path = str_replace('storage/', '', $pengguna->foto);
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                }
            }
            $pengguna->delete();
        }

        return redirect()->back()->with('success', "{$count} data pegawai dan fotonya berhasil dihapus.");
    } catch (\Illuminate\Database\QueryException $e) {
        if ($e->getCode() == '23000') {
            return redirect()->back()->with('error', 'Beberapa data tidak bisa dihapus karena masih memiliki relasi dengan tabel lain.');
        }

        return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data.');
    }
}

public function getSisaPensiunAttribute()
{
    if (!$this->tanggal_lahir) return '-';

    $tanggalLahir = Carbon::parse($this->tanggal_lahir);
    $usiaPensiun = 58; // ubah jika berbeda

    $tanggalPensiun = $tanggalLahir->copy()->addYears($usiaPensiun);
    $now = Carbon::now();

    // Jika sudah lewat masa pensiun
    if ($now->greaterThan($tanggalPensiun)) {
        return 'Sudah melewati masa pensiun';
    }

    $diff = $now->diff($tanggalPensiun);

    return $diff->y . ' tahun, ' . $diff->m . ' bulan, ' . $diff->d . ' hari';
}
}