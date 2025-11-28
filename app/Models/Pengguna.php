<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;

class Pengguna extends Authenticatable
{
    use HasFactory;
    public $timestamps = false; // ✅ Nonaktifkan timestamps

    protected $table = 'pengguna';
    protected $primaryKey = 'id_pengguna';

    protected $fillable = [
        'username',
        'nama_lengkap',
        'nip',
        'password',
        'id_jabatan',
        'id_unit_kerja',
        'id_pangkat_golongan_ruang',
        'id_pimpinan',
        'role',
        'masa_kerja',
        'tanda_tangan',  // ✅ Tambahkan ini
        'tanggal_masuk',
        'tanggal_lahir',
        'status_aktif',
        'tanggal_pensiun',
        'status_kepegawaian',
        'keterangan_non_aktif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
        'tanggal_pensiun' => 'date',
    ];

    // ========================================
    // ✅ METHOD UNTUK CEK PENSIUN
    // ========================================

    /**
     * Hitung umur dalam ANGKA (integer)
     * Method biasa, bukan accessor
     */
    public function getUmur()
    {
        if (!$this->tanggal_lahir) {
            return 0;
        }

        return Carbon::parse($this->tanggal_lahir)->age;
    }

    /**
     * Cek apakah sudah pensiun
     */
    public function sudahPensiun()
    {
        if (!$this->tanggal_lahir || !$this->jabatan) {
            return false;
        }

        $umurSekarang = $this->getUmur();
        $usiaPensiun = $this->jabatan->usia_pensiun ?? 58;

        return $umurSekarang >= $usiaPensiun;
    }

    /**
     * Cek dan update status pensiun
     */
    public function cekDanUpdateStatusPensiun()
    {
        if ($this->sudahPensiun() && $this->status_aktif === 'aktif') {
            $this->update([
                'status_aktif' => 'pensiun',
                'tanggal_pensiun' => now(),
                'keterangan_non_aktif' => 'Pensiun otomatis - Usia ' . $this->getUmur() . ' tahun'
            ]);
            return true;
        }
        return false;
    }

    /**
     * Cek apakah boleh login
     */
    public function bolehLogin()
    {
        return $this->status_aktif === 'aktif';
    }

    /**
     * Sisa waktu pensiun (dalam tahun)
     */
    public function sisaWaktuPensiun()
    {
        if (!$this->jabatan) {
            return null;
        }

        $usiaPensiun = $this->jabatan->usia_pensiun ?? 58;
        $umurSekarang = $this->getUmur();

        return $usiaPensiun - $umurSekarang;
    }

    // ========================================
    // ✅ ACCESSOR UNTUK TAMPILAN (STRING)
    // ========================================

    /**
     * Accessor: Umur dalam format STRING untuk tampilan
     * Dipanggil dengan: $user->umur
     */
    public function getUmurAttribute()
    {
        if (!$this->tanggal_lahir) {
            return '-';
        }

        $tanggalLahir = Carbon::parse($this->tanggal_lahir);
        $now = Carbon::now();
        
        $diff = $tanggalLahir->diff($now);
        
        return $diff->y . ' tahun';
    }
    /**
 * Accessor: Sisa waktu menuju pensiun
 * Dipanggil dengan: $pengguna->sisa_pensiun
 */
public function getSisaPensiunAttribute()
{
    if (!$this->tanggal_lahir || !$this->jabatan) {
        return '-';
    }

    $usiaPensiun = $this->jabatan->usia_pensiun ?? 58;
    $tanggalLahir = Carbon::parse($this->tanggal_lahir);

    // Tanggal pensiun = tanggal lahir + usia pensiun
    $tanggalPensiun = $tanggalLahir->copy()->addYears($usiaPensiun);

    $now = Carbon::now();

    // Jika sudah lewat → sudah pensiun
    if ($now->greaterThanOrEqualTo($tanggalPensiun)) {
        return 'Sudah memasuki usia pensiun';
    }

    // Hitung selisih lengkap
    $diff = $now->diff($tanggalPensiun);

    return "{$diff->y} tahun, {$diff->m} bulan, {$diff->d} hari";
}

    /**
     * Accessor: Masa kerja lengkap untuk tampilan
     * Dipanggil dengan: $user->masa_kerja_lengkap
     */
    public function getMasaKerjaLengkapAttribute()
    {
        if (!$this->tanggal_masuk) {
            return '-';
        }

        $tanggalMasuk = Carbon::parse($this->tanggal_masuk);
        $now = Carbon::now();

        $diff = $tanggalMasuk->diff($now);

return $diff->y . ' tahun ' . $diff->m . ' bulan ' . $diff->d . ' hari';
    }

    // ========================================
    // ✅ RELASI
    // ========================================

    public function suratIjin()
    {
        return $this->hasMany(SuratIjin::class, 'id_pengguna', 'id_pengguna');
    }

    public function permintaanSurat()
    {
        return $this->hasMany(PermintaanSurat::class, 'pegawai_id', 'id_pengguna');
    }

    public function suratAktif()
    {
        return $this->hasMany(SuratAktif::class, 'penerima_id', 'id_pengguna');
    }

    public function suratAktifDitandatangani()
    {
        return $this->hasMany(SuratAktif::class, 'penandatangan_id', 'id_pengguna');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan', 'id_jabatan');
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function pangkatGolongan()
    {
        return $this->belongsTo(PangkatGolonganRuang::class, 'id_pangkat_golongan_ruang', 'id_pangkat');
    }

    public function pimpinan()
    {
        return $this->belongsTo(DataPimpinan::class, 'id_pimpinan', 'id_pimpinan');
    }

    public function pengajuanCuti()
    {
        return $this->hasMany(PengajuanCuti::class, 'id_pengguna', 'id_pengguna');
    }
}