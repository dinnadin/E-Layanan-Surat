<?php

namespace App\Imports;

use App\Models\Pengguna;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use App\Models\PangkatGolonganRuang;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Str;

class PenggunaImport implements ToModel, WithHeadingRow
{
    public $rowNumber = 1; // Track row number
    
    // ========================================
    // METHOD UTAMA
    // ========================================
    public function model(array $row)
    {
        $this->rowNumber++;
        
        \Log::info("================== MULAI PROSES ROW #{$this->rowNumber} ==================");
        
        try {
            // 🔥 DEBUG 1: Log semua kolom yang ada
            \Log::info("📋 SEMUA KOLOM EXCEL:");
            \Log::info(json_encode(array_keys($row), JSON_PRETTY_PRINT));
            
            \Log::info("📄 ISI DATA ROW:");
            \Log::info(json_encode($row, JSON_PRETTY_PRINT));
            
            // 🔹 Helper function untuk merapikan teks
            $cleanText = function($text) {
                if (empty($text)) return null;
                $text = trim(preg_replace('/\s+/', ' ', $text));
                return Str::title(strtolower($text));
            };

            // ========================================
            // 🔴 FIELD WAJIB (MANDATORY)
            // ========================================
            
            \Log::info("🔍 STEP 1: Cari Nama Lengkap...");
            // 1. Nama Lengkap (WAJIB)
            $namaLengkap = $this->findValue($row, ['nama_lengkap', 'nama lengkap', 'nama', 'name']);
            \Log::info("   Raw value: " . ($namaLengkap ?? 'NULL'));
            $namaLengkap = $cleanText($namaLengkap);
            \Log::info("   Cleaned value: " . ($namaLengkap ?? 'NULL'));
            
            if (empty($namaLengkap)) {
                \Log::warning("❌ SKIP ROW #{$this->rowNumber}: Nama lengkap kosong");
                return null;
            }
            \Log::info("✅ Nama Lengkap: {$namaLengkap}");

            \Log::info("🔍 STEP 2: Cari NIP...");
            // 2. NIP (WAJIB)
            $nip = $this->findValue($row, ['nip', 'nomor induk', 'nomor_induk']);
            \Log::info("   Raw value: " . ($nip ?? 'NULL'));
            $nip = $nip ? preg_replace('/[^0-9]/', '', $nip) : '';
            \Log::info("   Cleaned value: " . ($nip ?? 'NULL'));
            
            if (empty($nip)) {
                \Log::warning("❌ SKIP ROW #{$this->rowNumber}: NIP kosong untuk {$namaLengkap}");
                return null;
            }
            \Log::info("✅ NIP: {$nip}");

            \Log::info("🔍 STEP 3: Cari Role...");
            // 3. Role (WAJIB)
            $role = $this->findValue($row, ['role', 'jabatan_role', 'level']);
            \Log::info("   Raw value: " . ($role ?? 'NULL'));
            $role = $role ? ucfirst(strtolower(trim($role))) : null;
            \Log::info("   Cleaned value: " . ($role ?? 'NULL'));
            
            if (empty($role)) {
                \Log::warning("❌ SKIP ROW #{$this->rowNumber}: Role kosong untuk {$namaLengkap}");
                return null;
            }
            \Log::info("✅ Role: {$role}");

            \Log::info("🔍 STEP 4: Parse Tanggal Lahir...");
            // 4. Tanggal Lahir (WAJIB)
            $tglLahir = null;
            $tglLahirRaw = $this->findValue($row, ['tanggal_lahir', 'tanggal lahir', 'tgl_lahir', 'tanggallahir']);
            \Log::info("   Raw value: " . ($tglLahirRaw ?? 'NULL') . " (Type: " . gettype($tglLahirRaw) . ")");
            
            if (!empty($tglLahirRaw)) {
                if (is_numeric($tglLahirRaw)) {
                    try {
                        $tglLahir = Date::excelToDateTimeObject($tglLahirRaw);
                        $tglLahir = Carbon::instance($tglLahir);
                        \Log::info("   Parsed as Excel date: " . $tglLahir->format('Y-m-d'));
                    } catch (\Exception $e) {
                        \Log::error("   Failed parse Excel date: " . $e->getMessage());
                    }
                } else {
                    $tglLahir = $this->parseTanggalIndonesia($tglLahirRaw);
                    if ($tglLahir) {
                        \Log::info("   Parsed as Indonesian date: " . $tglLahir->format('Y-m-d'));
                    }
                }
            }
            
            if (!$tglLahir) {
                \Log::warning("❌ SKIP ROW #{$this->rowNumber}: Tanggal lahir tidak valid untuk {$namaLengkap}");
                return null;
            }
            \Log::info("✅ Tanggal Lahir: " . $tglLahir->format('Y-m-d'));

            \Log::info("🔍 STEP 5: Parse Tanggal Masuk...");
            // 5. Tanggal Masuk (WAJIB)
            $tglMasuk = null;
            $tglMasukRaw = $this->findValue($row, ['tanggal_masuk', 'tanggal masuk', 'tgl_masuk', 'tanggalmasuk']);
            \Log::info("   Raw value: " . ($tglMasukRaw ?? 'NULL') . " (Type: " . gettype($tglMasukRaw) . ")");
            
            if (!empty($tglMasukRaw)) {
                if (is_numeric($tglMasukRaw)) {
                    try {
                        $tglMasuk = Date::excelToDateTimeObject($tglMasukRaw);
                        $tglMasuk = Carbon::instance($tglMasuk);
                        \Log::info("   Parsed as Excel date: " . $tglMasuk->format('Y-m-d'));
                    } catch (\Exception $e) {
                        \Log::error("   Failed parse Excel date: " . $e->getMessage());
                    }
                } else {
                    $tglMasuk = $this->parseTanggalIndonesia($tglMasukRaw);
                    if ($tglMasuk) {
                        \Log::info("   Parsed as Indonesian date: " . $tglMasuk->format('Y-m-d'));
                    }
                }
            }
            
            if (!$tglMasuk) {
                \Log::warning("❌ SKIP ROW #{$this->rowNumber}: Tanggal masuk tidak valid untuk {$namaLengkap}");
                return null;
            }
            \Log::info("✅ Tanggal Masuk: " . $tglMasuk->format('Y-m-d'));

            // ========================================
            // 🟢 FIELD OPSIONAL (Boleh Kosong)
            // ========================================

            \Log::info("🔍 STEP 6: Proses Username (Opsional)...");
            // Username (opsional, default = nama lengkap)
            $username = $this->findValue($row, ['username', 'user name', 'user']);
            $username = $username ? $cleanText($username) : $namaLengkap;
            \Log::info("✅ Username: {$username}");

            \Log::info("🔍 STEP 7: Proses Jabatan (Opsional)...");
            // === Jabatan (OPSIONAL) ===
            $jabatan = null;
            $jabatanRaw = $this->findValue($row, ['jabatan', 'jabatan_struktural', 'posisi']);
            \Log::info("   Jabatan raw: " . ($jabatanRaw ?? 'NULL'));
            
            if (!empty($jabatanRaw)) {
                try {
                    $namaJabatan = $cleanText($jabatanRaw);
                    $jabatan = Jabatan::firstOrCreate(['nama_jabatan' => $namaJabatan]);
                    \Log::info("✅ Jabatan created/found: {$namaJabatan} (ID: {$jabatan->id_jabatan})");
                } catch (\Exception $e) {
                    \Log::warning("⚠️ Gagal simpan jabatan: " . $e->getMessage());
                }
            } else {
                \Log::info("ℹ️ Jabatan kosong (OK)");
            }

            \Log::info("🔍 STEP 8: Proses Unit Kerja (Opsional)...");
            // === Unit Kerja (OPSIONAL) ===
            $unitKerja = null;
            $unitRaw = $this->findValue($row, ['unit_kerja', 'unit kerja', 'unit', 'divisi']);
            \Log::info("   Unit Kerja raw: " . ($unitRaw ?? 'NULL'));
            
            if (!empty($unitRaw)) {
                try {
                    $unitRaw = trim($unitRaw);
                    $namaUnit = $unitRaw;
                    $subUnit  = '';

                    if (str_contains($unitRaw, '(') && str_contains($unitRaw, ')')) {
                        $start = strpos($unitRaw, '(');
                        $end   = strpos($unitRaw, ')');
                        $namaUnit = trim(substr($unitRaw, 0, $start));
                        $subUnit  = trim(substr($unitRaw, $start + 1, $end - $start - 1));
                    }

                    $namaUnit = $cleanText($namaUnit);
                    $subUnit = $subUnit ? $cleanText($subUnit) : '';

                    $unitKerja = UnitKerja::firstOrCreate(
                        ['nama_unit_kerja' => $namaUnit],
                        ['sub_unit_kerja'  => $subUnit]
                    );
                    \Log::info("✅ Unit Kerja created/found: {$namaUnit} (ID: {$unitKerja->id_unit_kerja})");
                } catch (\Exception $e) {
                    \Log::warning("⚠️ Gagal simpan unit kerja: " . $e->getMessage());
                }
            } else {
                \Log::info("ℹ️ Unit Kerja kosong (OK)");
            }

            \Log::info("🔍 STEP 9: Proses Pangkat (Opsional)...");
            // === Pangkat (OPSIONAL) ===
            $pangkat = null;
            $pangkatRaw = $this->findValue($row, ['pangkat_golongan_ruang', 'pangkat golongan ruang', 'pangkat', 'golongan']);
            \Log::info("   Pangkat raw: " . ($pangkatRaw ?? 'NULL'));
            
            if (!empty($pangkatRaw)) {
                try {
                    $pangkatRaw = trim($pangkatRaw);
                    $namaPangkat = $pangkatRaw;
                    $golongan = '';
                    $ruang = '';

                    if (str_contains($pangkatRaw, '(') && str_contains($pangkatRaw, ')')) {
                        $start = strpos($pangkatRaw, '(');
                        $end   = strpos($pangkatRaw, ')');
                        $namaPangkat = trim(substr($pangkatRaw, 0, $start));
                        $golRuang    = trim(substr($pangkatRaw, $start + 1, $end - $start - 1));

                        if (str_contains($golRuang, '/')) {
                            [$golongan, $ruang] = explode('/', $golRuang);
                            $golongan = strtoupper(trim($golongan));
                            $ruang    = strtolower(trim($ruang));
                        }
                    }

                    $namaPangkat = $cleanText($namaPangkat);

                    $pangkat = PangkatGolonganRuang::firstOrCreate([
                        'pangkat'  => $namaPangkat,
                        'golongan' => $golongan,
                        'ruang'    => $ruang,
                    ]);
                    \Log::info("✅ Pangkat created/found: {$namaPangkat} (ID: {$pangkat->id_pangkat})");
                } catch (\Exception $e) {
                    \Log::warning("⚠️ Gagal simpan pangkat: " . $e->getMessage());
                }
            } else {
                \Log::info("ℹ️ Pangkat kosong (OK)");
            }

            \Log::info("🔍 STEP 10: Proses Pimpinan (Opsional)...");
            // === Pimpinan (OPSIONAL) ===
            $pimpinan = null;
            $pimpinanRaw = $this->findValue($row, ['pimpinan', 'atasan', 'kepala']);
            \Log::info("   Pimpinan raw: " . ($pimpinanRaw ?? 'NULL'));
            
            if (!empty($pimpinanRaw)) {
                try {
                    $namaPimpinan = $cleanText($pimpinanRaw);
                    $pimpinan = \App\Models\DataPimpinan::firstOrCreate([
                        'nama_pimpinan' => $namaPimpinan
                    ]);
                    \Log::info("✅ Pimpinan created/found: {$namaPimpinan} (ID: {$pimpinan->id_pimpinan})");
                } catch (\Exception $e) {
                    \Log::warning("⚠️ Gagal simpan pimpinan: " . $e->getMessage());
                }
            } else {
                \Log::info("ℹ️ Pimpinan kosong (OK)");
            }

            \Log::info("🔍 STEP 11: Hitung Masa Kerja...");
            // === Hitung Masa Kerja ===
            $masaKerjaStr = '-';
            if ($tglMasuk) {
                $masaKerja = $tglMasuk->diff(Carbon::now());
                $masaKerjaStr = $masaKerja->y . ' Tahun ' . $masaKerja->m . ' Bulan';
            }
            \Log::info("✅ Masa Kerja: {$masaKerjaStr}");

            // ========================================
            // 💾 SIMPAN KE DATABASE
            // ========================================
            \Log::info("🔍 STEP 12: Siapkan data untuk disimpan...");
            
            $dataToSave = [
                'nama_lengkap'              => $namaLengkap,
                'username'                  => $username,
                'nip'                       => $nip,
                'password'                  => Hash::make($nip),
                'id_jabatan'                => $jabatan->id_jabatan ?? null,
                'id_unit_kerja'             => $unitKerja->id_unit_kerja ?? null,
                'id_pangkat_golongan_ruang' => $pangkat->id_pangkat ?? null,
                'id_pimpinan'               => $pimpinan->id_pimpinan ?? null,
                'role'                      => $role,
                'tanggal_masuk'             => $tglMasuk->format('Y-m-d'),
                'tanggal_lahir'             => $tglLahir->format('Y-m-d'),
                'masa_kerja'                => $masaKerjaStr,
            ];
            
            \Log::info("📦 DATA YANG AKAN DISIMPAN:");
            \Log::info(json_encode($dataToSave, JSON_PRETTY_PRINT));
            
            \Log::info("🔍 STEP 13: Cek duplikasi NIP...");
            $existingPengguna = Pengguna::where('nip', $nip)->first();
            if ($existingPengguna) {
                \Log::warning("⚠️ NIP {$nip} sudah ada di database (ID: {$existingPengguna->id_pengguna})");
                \Log::warning("   Nama existing: {$existingPengguna->nama_lengkap}");
                \Log::warning("   SKIP ROW #{$this->rowNumber}");
                return null;
            }
            
            \Log::info("🔍 STEP 14: Simpan ke database...");
            $pengguna = new Pengguna($dataToSave);
            $pengguna->save();

            \Log::info("✅✅✅ BERHASIL SIMPAN ROW #{$this->rowNumber}:");
            \Log::info("   Nama: {$namaLengkap}");
            \Log::info("   NIP: {$nip}");
            \Log::info("   ID Pengguna: {$pengguna->id_pengguna}");
            \Log::info("================== SELESAI ROW #{$this->rowNumber} ==================\n");
            
            return $pengguna;

        } catch (\Exception $e) {
            \Log::error("❌❌❌ ERROR FATAL ROW #{$this->rowNumber}:");
            \Log::error("   Message: " . $e->getMessage());
            \Log::error("   File: " . $e->getFile());
            \Log::error("   Line: " . $e->getLine());
            \Log::error("   Stack trace:");
            \Log::error($e->getTraceAsString());
            \Log::info("================== GAGAL ROW #{$this->rowNumber} ==================\n");
            return null;
        }
    }

    // ========================================
    // METHOD HELPER
    // ========================================
    
    /**
     * Normalisasi key dari Excel agar fleksibel
     */
    private function normalizeKey($key)
    {
        $normalized = strtolower(trim($key));
        $normalized = preg_replace('/[^a-z0-9]/', '', $normalized);
        return $normalized;
    }

    /**
     * Cari value dari row dengan berbagai kemungkinan nama kolom
     */
    private function findValue($row, $possibleKeys)
    {
        \Log::info("      🔎 Mencari value dengan keys: " . json_encode($possibleKeys));
        
        foreach ($possibleKeys as $key) {
            // Cek key asli
            if (isset($row[$key]) && !empty($row[$key])) {
                \Log::info("      ✓ Found dengan key exact: '{$key}' = '{$row[$key]}'");
                return $row[$key];
            }
            
            // Cek dengan normalisasi
            foreach ($row as $rowKey => $rowValue) {
                if ($this->normalizeKey($rowKey) === $this->normalizeKey($key)) {
                    \Log::info("      ✓ Found dengan normalisasi: '{$rowKey}' = '{$rowValue}'");
                    return $rowValue;
                }
            }
        }
        
        \Log::info("      ✗ Tidak ditemukan value untuk keys: " . json_encode($possibleKeys));
        return null;
    }

    /**
     * Parse tanggal bahasa Indonesia
     */
    private function parseTanggalIndonesia($tanggal)
    {
        \Log::info("      📅 Parsing tanggal Indonesia: {$tanggal}");
        
        $bulan = [
            'januari'   => 'January',
            'februari'  => 'February',
            'maret'     => 'March',
            'april'     => 'April',
            'mei'       => 'May',
            'juni'      => 'June',
            'juli'      => 'July',
            'agustus'   => 'August',
            'september' => 'September',
            'oktober'   => 'October',
            'november'  => 'November',
            'desember'  => 'December',
        ];

        $tanggal = strtolower(trim($tanggal));
        
        // Replace bulan Indonesia ke English
        foreach ($bulan as $id => $en) {
            if (str_contains($tanggal, $id)) {
                $tanggal = str_replace($id, $en, $tanggal);
                \Log::info("      Replace bulan: {$id} → {$en}");
                break;
            }
        }
        
        // Coba berbagai format
        $formats = ['d F Y', 'd-m-Y', 'd/m/Y', 'Y-m-d', 'd-M-Y', 'd/M/Y'];
        
        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $tanggal);
                \Log::info("      ✓ Berhasil parse dengan format: {$format} → " . $parsed->format('Y-m-d'));
                return $parsed;
            } catch (\Exception $e) {
                continue;
            }
        }
        
        // Fallback: coba Carbon::parse
        try {
            $parsed = Carbon::parse($tanggal);
            \Log::info("      ✓ Berhasil parse dengan Carbon::parse() → " . $parsed->format('Y-m-d'));
            return $parsed;
        } catch (\Exception $e) {
            \Log::error("      ✗ Gagal parse tanggal: " . $e->getMessage());
            return null;
        }
    }
}