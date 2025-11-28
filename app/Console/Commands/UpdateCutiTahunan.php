<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DataCuti;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateCutiTahunan extends Command
{
    protected $signature = 'cuti:update-tahunan';
    protected $description = 'Update cuti tahunan: N→N-1 (jika N<6 ambil N, jika N≥6 ambil 6), lalu set N=12';

    public function handle()
    {
        $tahun = Carbon::now()->year;
        $this->info("🔄 Memulai update cuti tahunan untuk tahun {$tahun}...");
        $this->info("📋 Logika: Jika N < 6 → N-1 = N, Jika N ≥ 6 → N-1 = 6");
        $this->line(str_repeat("=", 80));
        
        DB::beginTransaction();
        
        try {
            $dataCuti = DataCuti::all();
            $totalUpdated = 0;
            
            foreach ($dataCuti as $cuti) {
                // Simpan nilai lama untuk log
                $oldN1 = $cuti->n_1;
                $oldN = $cuti->n;
                
                // ===== LOGIKA BARU: CEK N =====
                // Jika N kurang dari 6, maka N-1 = N (sisa yang ada)
                // Jika N >= 6, maka N-1 = 6 (maksimal)
                if ($cuti->n < 6) {
                    $cuti->n_1 = $cuti->n;  // Ambil sisa N yang kurang dari 6
                    $status = "N < 6, N-1 diambil dari sisa N";
                } else {
                    $cuti->n_1 = 6;  // Maksimal 6 untuk N-1
                    $status = "N ≥ 6, N-1 diset 6";
                }
                
                // Set N = 12 (jatah tahun baru)
                $cuti->n = 12;
                
                // N-2 selalu 0 (dihapus)
                $cuti->n_2 = 0;
                
                // ===== HITUNG ULANG (tanpa N-2) =====
                $cuti->jumlah = $cuti->n_1 + $cuti->n;
                $cuti->sisa = $cuti->jumlah - ($cuti->diambil ?? 0);
                
                $cuti->save();
                $totalUpdated++;
                
                // Log detail
                $nama = $cuti->pengguna->nama_lengkap ?? 'ID: ' . $cuti->id_pengguna;
                $this->line("✅ {$nama}");
                $this->line("   Lama: N-1={$oldN1}, N={$oldN}");
                $this->line("   Baru: N-1={$cuti->n_1}, N={$cuti->n}");
                $this->line("   Status: {$status}");
                $this->line("   Total={$cuti->jumlah}, Diambil={$cuti->diambil}, Sisa={$cuti->sisa}");
                $this->line(str_repeat("-", 80));
            }
            
            DB::commit();
            
            $this->info(str_repeat("=", 80));
            $this->info("✅ SUKSES! {$totalUpdated} pegawai telah diupdate untuk tahun {$tahun}.");
            
            Log::info("Update Cuti Tahunan Success (N<6 logic)", [
                'tahun' => $tahun,
                'total' => $totalUpdated,
                'waktu' => now(),
                'logika' => 'N < 6 → N-1 = N, N ≥ 6 → N-1 = 6'
            ]);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ ERROR: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            
            Log::error('Update Cuti Tahunan Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}