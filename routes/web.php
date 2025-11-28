<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuratIjinController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\SuratAktifController;
use App\Http\Controllers\RiwayatSuratController;
use App\Http\Controllers\PermintaanSuratController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermintaanSuratIjinController;
use App\Http\Controllers\PengajuanCutiController;
use App\Http\Controllers\DataPimpinanController;
use App\Http\Controllers\DataCutiController;
use App\Http\Controllers\DetailCutiController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\UnitKerjaController;
use App\Http\Controllers\GolonganRuangController;
use App\Http\Controllers\DataLiburController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportPdfController;


// === AUTH ROUTES ===
// lebih aman pakai POST untuk logout
require __DIR__.'/auth.php';

// === MENU PEGAWAI (umum) ===
Route::view('/pengajuansurat', 'pengajuansurat')->name('pengajuansurat');

Route::prefix('ijin')->group(function () {
    Route::get('/', [SuratIjinController::class, 'index'])->name('suratijin.index');
    Route::get('/create', [SuratIjinController::class, 'create'])->name('suratijin.create');
    Route::post('/', [SuratIjinController::class, 'store'])->name('suratijin.store');
    Route::get('/{id}/edit', [SuratIjinController::class, 'edit'])->name('suratijin.edit');
    Route::put('/{id}', [SuratIjinController::class, 'update'])->name('suratijin.update');
    Route::delete('/{id}', [SuratIjinController::class, 'destroy'])->name('suratijin.destroy');
});
Route::resource('jabatan', JabatanController::class);
Route::resource('unit', UnitKerjaController::class);
Route::resource('pangkat', GolonganRuangController::class);

// === DATA PEGAWAI (umum) ===
Route::prefix('data-pegawai')->group(function () {
    Route::get('/', [PenggunaController::class, 'index'])->name('data.pegawai');
    Route::post('/import', [PenggunaController::class, 'importExcel'])->name('pengguna.import');
    Route::get('/create', [PenggunaController::class, 'create'])->name('data.pegawai.create');
    Route::post('/', [PenggunaController::class, 'store'])->name('data.pegawai.store');
    Route::get('/{id_pengguna}/edit', [PenggunaController::class, 'edit'])->name('data.pegawai.edit');
    Route::put('/{id_pengguna}', [PenggunaController::class, 'update'])->name('data.pegawai.update');
    Route::delete('/{id_pengguna}', [PenggunaController::class, 'destroy'])->name('data.pegawai.destroy');
});

// === RIWAYAT SURAT (umum) ===
Route::prefix('riwayat')->group(function () {
    Route::get('/', [RiwayatSuratController::class, 'index'])->name('riwayat');
    Route::get('/download', [RiwayatSuratController::class, 'downloadPdf'])->name('riwayat.download');
    
    // ✅ GANTI INI - Gunakan ExportPdfController
    Route::get('/{id}/pdf', function($id) {
        return app(\App\Http\Controllers\ExportPdfController::class)->download('ijin', $id);
    })->name('riwayat.showPdf');
});

// === SURAT AKTIF (umum) ===
Route::prefix('surataktif')->group(function () {
    Route::get('/', [SuratAktifController::class, 'index'])->name('surataktif.index');
    Route::get('/create', [SuratAktifController::class, 'create'])->name('surataktif.create');
    Route::post('/store', [SuratAktifController::class, 'store'])->name('surataktif.store');
});

// === ADMIN ===
Route::prefix('admin')->middleware(['auth', 'role:Admin'])->group(function () {
    // dashboard
    Route::get('/dashboard', [AuthController::class, 'dashboardAdmin'])->name('dashboard.admin');

    // surat aktif
    Route::prefix('surataktif')->group(function () {
        Route::get('/', [SuratAktifController::class, 'index'])->name('admin.surataktif.index');
        Route::get('/{id}', [SuratAktifController::class, 'show'])->name('admin.surataktif.show');
        Route::get('/{id}/edit', [SuratAktifController::class, 'edit'])->name('admin.surataktif.edit');
        Route::put('/{id}', [SuratAktifController::class, 'update'])->name('admin.surataktif.update');
        Route::delete('/{id}', [SuratAktifController::class, 'destroy'])->name('admin.surataktif.destroy');
        Route::post('/{id}/approve', [SuratAktifController::class, 'approve'])->name('admin.surataktif.approve');
    });

    // permintaan surat
    Route::prefix('permintaan-surat')->group(function () {
        Route::get('/', [PermintaanSuratController::class, 'index'])->name('admin.permintaan_surat.index');
        Route::get('/{id}', [PermintaanSuratController::class, 'show'])->name('admin.permintaan_surat.show');
        Route::put('/{id}', [PermintaanSuratController::class, 'update'])->name('admin.permintaan_surat.update');
    });
});

// === PEGAWAI ===
Route::prefix('pegawai')->middleware(['auth', 'role:Pegawai'])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboardPegawai'])->name('dashboard.pegawai');
});
Route::prefix('kepala')->middleware(['auth', 'role:Kepala'])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboardKepala'])->name('dashboard.kepala');

    Route::prefix('permintaan')->group(function () {
        Route::get('/', [PermintaanSuratIjinController::class, 'kepalaIndex'])->name('kepala.permintaan.index');
        
        // ✅ UBAH INI: edit menggunakan SuratIjinController bukan PermintaanSuratIjinController
        Route::get('/{id}/edit', [SuratIjinController::class, 'edit'])->name('kepala.permintaan.edit');

        // ✅ TAMBAH INI: Route untuk Setuju & Tolak
        Route::put('/{id}/setuju', [SuratIjinController::class, 'setuju'])->name('kepala.permintaan.setuju');
        Route::put('/{id}/tolak', [SuratIjinController::class, 'tolak'])->name('kepala.permintaan.tolak');
    });
});

// === PENGATURAN AKUN ===
Route::get('/pengaturan', [ProfileController::class, 'show'])
    ->middleware('auth')
    ->name('pengaturan');Route::post('/profile/update', [ProfileController::class, 'update'])->middleware('auth')->name('profile.update');

// === LAPORAN PENGAJUAN SURAT ===
use App\Http\Controllers\LaporanPengajuanSuratController;

Route::prefix('laporan-pengajuan-surat')->middleware(['auth'])->group(function () {
    Route::get('/', [LaporanPengajuanSuratController::class, 'index'])->name('laporan.pengajuan.surat');
    Route::get('/cetak', [LaporanPengajuanSuratController::class, 'cetak'])->name('laporanpengajuansurat');
    Route::get('/export', [LaporanPengajuanSuratController::class, 'exportExcel'])->name('laporan_pengajuan.excel');
});

// === PERMINTAAN SURAT IJIN (pegawai) ===
Route::prefix('permintaan')->middleware(['auth'])->group(function () {
    Route::get('/create', [PermintaanSuratIjinController::class, 'create'])->name('permintaan.create');
    Route::post('/store', [PermintaanSuratIjinController::class, 'store'])->name('permintaan.store');
    Route::get('/riwayat', [PermintaanSuratIjinController::class, 'riwayat'])->name('permintaan.riwayat');
    Route::get('/get-user/{id}', [PermintaanSuratIjinController::class, 'getUser'])->name('permintaan.getUser');
});
Route::get('pegawai/export', [PenggunaController::class, 'exportExcel'])->name('pegawai.export');

Route::middleware(['auth'])->group(function () {
    // tampilkan form pengajuan cuti
    Route::get('/pengajuan-cuti', [PengajuanCutiController::class, 'create'])->name('pengajuan_cuti.create');
        Route::post('/admin/trigger-update-cuti-tahunan', [DataCutiController::class, 'triggerUpdateCutiTahunan'])
        ->name('admin.trigger.update.cuti');

    // simpan pengajuan cuti
    Route::post('/pengajuan-cuti', [PengajuanCutiController::class, 'store'])->name('pengajuan_cuti.store');

    // daftar pengajuan cuti (opsional kalau mau ada halaman index)
    Route::get('/pengajuan-cuti/list', [PengajuanCutiController::class, 'index'])->name('pengajuan_cuti.index');
});

Route::resource('data_cuti', DataCutiController::class);
Route::get('/data-cuti', [DataCutiController::class, 'index'])->name('data_cuti.index');
Route::post('data_cuti/import', [DataCutiController::class, 'import'])->name('data_cuti.import');

Route::get('/rekapitulasi-cuti', [\App\Http\Controllers\RekapitulasiDataCutiController::class, 'index'])->name('rekapitulasi_cuti.index');
Route::post('/rekapitulasi-cuti/{id_cuti}/batalkan', [\App\Http\Controllers\RekapitulasiDataCutiController::class, 'batalkan'])->name('rekapitulasi_cuti.batalkan');
Route::put('/cuti/{id_cuti}/batalkan', [PengajuanCutiController::class, 'batalkan'])->name('cuti.batalkan');
Route::get('/detail-cuti', [DetailCutiController::class, 'index'])->name('detail_cuti.index');
Route::get('/pengajuan-cuti/{id_cuti}/cetak', [App\Http\Controllers\PengajuanCutiController::class, 'cetak'])
     ->name('pengajuan_cuti.cetak');
Route::get('/data-cuti/export', [DataCutiController::class, 'export'])->name('data-cuti.export');
Route::get('/rekapitulasi-cuti/export', [\App\Http\Controllers\RekapitulasiDataCutiController::class, 'export'])
    ->name('rekapitulasi_cuti.export');
Route::get('/laporan-pengajuan/excel', [RiwayatSuratController::class, 'exportExcel'])->name('laporan_pengajuan.excel');

Route::resource('data_libur', DataLiburController::class);

Route::get('/hitung-hari-cuti', [PengajuanCutiController::class, 'hitungLama']);
Route::delete('/pegawai/bulk-delete', [penggunaController::class, 'bulkDelete'])
    ->name('data.pegawai.bulkDelete');
    Route::post('/data-cuti/bulk-delete', [DataCutiController::class, 'bulkDelete'])->name('data_cuti.bulkDelete');
    Route::get('/data-cuti/{id}/edit', [DataCutiController::class, 'edit'])->name('data_cuti.edit');
Route::put('/data-cuti/{id}', [DataCutiController::class, 'update'])->name('data_cuti.update');
Route::resource('data-pimpinan', DataPimpinanController::class);
Route::post('/pengguna/{id}/update-foto', [PenggunaController::class, 'updateFoto'])->name('pengguna.updateFoto');
Route::delete('/pengguna/{id}/hapus-foto', [PenggunaController::class, 'hapusFoto'])->name('pengguna.hapusFoto');
Route::get('/pengguna/{id_pengguna}', [PenggunaController::class, 'show'])->name('data.pegawai.show');
Route::get('/export-pdf/{tipe}/{id}', [ExportPdfController::class, 'download'])
    ->middleware('auth')
    ->name('export.pdf');
    // Route untuk cek nomor surat duplikat
Route::post('/check-nomor-surat', [SuratAktifController::class, 'checkNomorSurat'])
    ->name('check.nomor.surat');
    Route::get('/cek-sisa-cuti/{id_pengguna}', [PengajuanCutiController::class, 'cekSisaCuti'])->name('cek.sisa.cuti');
    // Di routes/web.php
    Route::get('/cek-overlap-cuti/{id_pengguna}', [PengajuanCutiController::class, 'cekOverlap'])->name('cek.overlap.cuti');
    Route::post('/surataktif/check-tanggal', [App\Http\Controllers\SuratAktifController::class, 'checkTanggal'])
    ->name('surataktif.checkTanggal');
    // ✅ ROUTE INI SUDAH BENAR (line 168-169)
Route::post('/permintaan/check-tanggal', [PermintaanSuratIjinController::class, 'checkTanggal'])
    ->name('permintaan.checkTanggal');
    Route::get('/cek-cuti-besar/{id_pengguna}', [PengajuanCutiController::class, 'cekCutiBesar'])
    ->name('cek.cuti.besar');
    Route::get('/cek-cuti-besar-tahun-ini/{id_pengguna}', [PengajuanCutiController::class, 'cekCutiBesarTahunIni'])
    ->name('cek.cuti.besar.tahun.ini');
    Route::get('/cek-status-kepegawaian/{id_pengguna}', [PengajuanCutiController::class, 'cekStatusKepegawaian'])
    ->name('cek.status.kepegawaian');

    Route::get('/data_cuti/create', [DataCutiController::class, 'create'])->name('data_cuti.create');
Route::post('/data_cuti', [DataCutiController::class, 'store'])->name('data_cuti.store');

    // === DEBUG ===
Route::get('/cek-role', function () {
    return auth()->check()
        ? 'Role saat ini: ' . auth()->user()->role
        : 'Belum login';
})->middleware(['auth', 'role:Admin']);
