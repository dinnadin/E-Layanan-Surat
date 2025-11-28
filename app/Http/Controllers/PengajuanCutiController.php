<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanCuti;
use App\Models\Pengguna;
use App\Models\RiwayatSurat;
use App\Models\DataCuti;
use PDF;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\DataLibur;

class PengajuanCutiController extends Controller
{
    public function index()
    {
        $cuti = PengajuanCuti::with('pengguna')->orderBy('created_at', 'desc')->get();
        return view('pengajuan_cuti.index', compact('cuti'));
    }

    public function create()
{
    $user = auth()->user();
    $id_pengguna = is_array($user) ? $user['id_pengguna'] : $user->id_pengguna;

    $pengguna = Pengguna::with(['jabatan', 'unitKerja'])
        ->findOrFail($id_pengguna);

    // ✅ KETUA TIM KERJA - Format: Nama Lengkap (Jabatan Pimpinan)
    $ketuaTim = Pengguna::with(['pimpinan', 'jabatan'])
        ->whereHas('pimpinan', function ($q) {
            $q->whereRaw('LOWER(nama_pimpinan) LIKE ?', ['%ketua tim kerja%']);
        })
        ->get()
        ->unique('id_pengguna')
        ->map(function ($item) {
            return [
                'id_pengguna' => $item->id_pengguna,
                'nama_lengkap' => $item->nama_lengkap,
                'nama_pimpinan' => $item->pimpinan->nama_pimpinan ?? '-',
            ];
        });

    // ✅ KEPALA KELOMPOK - Sudah OK (tidak perlu diubah)
    $kepalaKelompok = Pengguna::with(['pimpinan', 'jabatan'])
        ->whereHas('pimpinan', function ($q) {
            $q->where(function ($subQuery) {
                $subQuery->whereRaw("REPLACE(REPLACE(LOWER(nama_pimpinan), ' ', ''), '\t', '') LIKE ?", ['%kepalabagian%'])
                         ->orWhereRaw("REPLACE(REPLACE(LOWER(nama_pimpinan), ' ', ''), '\t', '') LIKE ?", ['%ketuakelompok%'])
                         ->orWhereRaw("REPLACE(REPLACE(LOWER(nama_pimpinan), ' ', ''), '\t', '') LIKE ?", ['%kepalakelompok%']);
            });
        })
        ->get()
        ->unique('id_pengguna')
        ->map(function ($item) {
            return [
                'id_pengguna' => $item->id_pengguna,
                'nama_lengkap' => $item->nama_lengkap,
                'nama_pimpinan' => $item->pimpinan->nama_pimpinan ?? '-',
            ];
        });

    // ✅ KEPALA BALAI - Format: Nama Lengkap (Jabatan Pimpinan)
    $kepalaBalai = Pengguna::with(['pimpinan', 'jabatan'])
        ->whereHas('pimpinan', function ($q) {
            $q->whereRaw('LOWER(nama_pimpinan) LIKE ?', ['%kepala balai%']);
        })
        ->get()
        ->unique('id_pengguna')
        ->map(function ($item) {
            return [
                'id_pengguna' => $item->id_pengguna,
                'nama_lengkap' => $item->nama_lengkap,
                'nama_pimpinan' => $item->pimpinan->nama_pimpinan ?? '-',
            ];
        });

    return view('pengajuan_cuti.create', compact('pengguna', 'ketuaTim', 'kepalaKelompok', 'kepalaBalai'));
}

   public function store(Request $request)
{
    $request->validate([
        'id_pengguna'       => 'required|exists:pengguna,id_pengguna',
        'tanggal_mulai'     => 'required|date',
        'tanggal_selesai'   => 'required|date|after_or_equal:tanggal_mulai',
        'alasan'            => 'required|string',
        'jenis_permohonan'  => 'required|string',
        'alamat_cuti'       => 'required|string',
        'tanggal_pengajuan' => 'required|date|before_or_equal:tanggal_mulai',
        'tandatangan_id'    => 'required|exists:pengguna,id_pengguna',
        'penandatangan_id'  => 'nullable|exists:pengguna,id_pengguna',
    ]);

    // ===== AMBIL TAHUN DARI TANGGAL MULAI =====
    $tahunPengajuan = Carbon::parse($request->tanggal_mulai)->year;


    // Cek overlap tanggal cuti
    $overlap = PengajuanCuti::where('id_pengguna', $request->id_pengguna)
        ->where(function($query) use ($request) {
            $query->whereBetween('tanggal_mulai', [$request->tanggal_mulai, $request->tanggal_selesai])
                  ->orWhereBetween('tanggal_selesai', [$request->tanggal_mulai, $request->tanggal_selesai])
                  ->orWhere(function($q) use ($request) {
                      $q->where('tanggal_mulai', '<=', $request->tanggal_mulai)
                        ->where('tanggal_selesai', '>=', $request->tanggal_selesai);
                  });
        })
        ->exists();

    if ($overlap) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'Tanggal cuti yang dipilih beririsan dengan pengajuan cuti Anda yang sudah ada. Silakan pilih tanggal lain.');
    }

    $lama = $this->hitungHariCuti(
        $request->tanggal_mulai, 
        $request->tanggal_selesai,
        $request->jenis_permohonan
    );

    // Normalisasi input jenis cuti
    $jenisCuti = strtolower(trim(preg_replace('/\s+/', ' ', $request->jenis_permohonan)));

    // ===== ✅ VALIDASI: HANYA ASN YANG BISA CUTI BESAR =====
    if ($jenisCuti === 'cuti besar') {
        $pengguna = Pengguna::find($request->id_pengguna);
        $statusKepegawaian = strtolower(trim($pengguna->status_kepegawaian ?? ''));
        
        // Cek apakah ASN
        $isASN = (strpos($statusKepegawaian, 'aparatur sipil negara') !== false) 
                 || (strpos($statusKepegawaian, 'asn') !== false)
                 || ($statusKepegawaian === 'pns');
        
        if (!$isASN) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Maaf, Cuti Besar hanya dapat diajukan oleh pegawai dengan status kepegawaian "Aparatur Sipil Negara (ASN)". Status Anda saat ini: ' . ($pengguna->status_kepegawaian ?? 'Belum diisi'));
        }
    }

    // ===== VALIDASI KHUSUS CUTI BESAR =====
    if ($jenisCuti === 'cuti besar') {
        $maxHari = 90;
        if ($lama > $maxHari) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Cuti Besar maksimal 3 bulan (90 hari). Jumlah hari yang Anda ajukan: $lama hari.");
        }

        }

  // ===== ✅ VALIDASI: CUTI TAHUNAN TIDAK BOLEH JIKA ADA CUTI BESAR DI TAHUN YANG SAMA =====
if ($jenisCuti === 'cuti tahunan') {
    $adaCutiBesar = PengajuanCuti::where('id_pengguna', $request->id_pengguna)
        ->where('jenis_permohonan', 'Cuti Besar')
        ->whereYear('tanggal_mulai', $tahunPengajuan)
        ->exists();
    
    if ($adaCutiBesar) {
        return redirect()->back()
            ->withInput()
            ->with('error', "Maaf, Anda tidak dapat mengajukan Cuti Tahunan di tahun $tahunPengajuan karena sudah mengambil Cuti Besar di tahun yang sama. (Sesuai PP No. 11 Tahun 2017). Namun, Anda masih dapat mengajukan Cuti Sakit, Cuti Melahirkan, atau Cuti Alasan Penting.");
    }

    // Validasi sisa cuti
    $dataCuti = DataCuti::where('id_pengguna', $request->id_pengguna)->first();
    if (!$dataCuti) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'Data cuti tidak ditemukan. Silakan hubungi admin.');
    }

    $sisaCuti = ($dataCuti->n ?? 0) + ($dataCuti->n_1 ?? 0) + ($dataCuti->n_2 ?? 0);
    if ($sisaCuti == 0) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'Sisa Cuti Tahun ini Kosong. Jika ingin mengajukan cuti, tolong hubungi admin.');
    }
    if ($lama > $sisaCuti) {
        return redirect()->back()
            ->withInput()
            ->with('error', "Jumlah hari cuti yang diminta ($lama hari) lebih besar dari sisa cuti Anda ($sisaCuti hari).");
    }
}

    // ===== VALIDASI MAKSIMAL 3 BULAN (CUTI ALASAN PENTING & MELAHIRKAN) =====
    if (in_array($jenisCuti, ['cuti alasan penting', 'cuti melahirkan'])) {
        $maxHari = 90;
        if ($lama > $maxHari) {
            return redirect()->back()
                ->withInput()
                ->with('error', ucfirst($jenisCuti) . " maksimal 3 bulan (90 hari). Jumlah hari yang Anda ajukan: $lama hari.");
        }
    }

    // ===== UPDATE DATA CUTI (Cuti Tahunan saja) =====
    if ($jenisCuti === 'cuti tahunan') {
        $dataCuti = DataCuti::where('id_pengguna', $request->id_pengguna)->first();
        if ($dataCuti) {
            $need = (int) $lama;

            $n = $dataCuti->n ?? 0;
            $takeFromN = min($n, $need);
            $dataCuti->n = $n - $takeFromN;
            $need -= $takeFromN;

            if ($need > 0) {
                $n1 = $dataCuti->n_1 ?? 0;
                $takeFromN1 = min($n1, $need);
                $dataCuti->n_1 = $n1 - $takeFromN1;
                $need -= $takeFromN1;
            }

            if ($need > 0) {
                $n2 = $dataCuti->n_2 ?? 0;
                $takeFromN2 = min($n2, $need);
                $dataCuti->n_2 = $n2 - $takeFromN2;
                $need -= $takeFromN2;
            }

            $dataCuti->diambil = ($dataCuti->diambil ?? 0) + (int)$lama;
            $dataCuti->jumlah = ($dataCuti->n ?? 0) + ($dataCuti->n_1 ?? 0) + ($dataCuti->n_2 ?? 0);
            $dataCuti->sisa = max(0, $dataCuti->jumlah - $dataCuti->diambil);

            $dataCuti->save();
        }
    }

    // ===== SIMPAN PENGAJUAN CUTI =====
    $cuti = PengajuanCuti::create([
        'id_pengguna'      => $request->id_pengguna,
        'tanggal_mulai'    => $request->tanggal_mulai,
        'tanggal_selesai'  => $request->tanggal_selesai,
        'alasan'           => $request->alasan,
        'jenis_permohonan' => $request->jenis_permohonan,
        'lama'             => $lama,
        'satuan_lama'      => 'hari',
        'alamat_cuti'      => $request->alamat_cuti,
        'tanggal_pengajuan'=> $request->tanggal_pengajuan,
        'tandatangan_id'   => $request->tandatangan_id,
        'penandatangan_id' => $request->penandatangan_id,
    ]);

    RiwayatSurat::create([
        'id_pengguna' => $request->id_pengguna,
        'id_cuti'     => $cuti->id_cuti,
        'keterangan'  => 'Pengajuan cuti',
    ]);

    return redirect()
        ->route('riwayat')
        ->with('success', 'Pengajuan cuti berhasil disimpan.');
}
    // ===== METHOD batalkan() =====
    public function batalkan($id_cuti)
{
    $cuti = PengajuanCuti::findOrFail($id_cuti);
    $jenisCuti = strtolower(preg_replace('/\s+/', ' ', trim($cuti->jenis_permohonan)));

    // ❗ Jika bukan cuti tahunan → langsung hapus, aman, tidak sentuh quota
    if ($jenisCuti !== 'cuti tahunan') {
        $cuti->delete();
        return redirect()->back()->with('success', 'Pengajuan cuti berhasil dibatalkan (Kuota tidak berubah).');
    }

    // === khusus cuti tahunan ===
    $dataCuti = DataCuti::where('id_pengguna', $cuti->id_pengguna)->first();

    if ($dataCuti) {
        $lamaCuti = (int) filter_var($cuti->lama, FILTER_SANITIZE_NUMBER_INT);

        // Kembalikan ke slot N dulu
        $dataCuti->n = ($dataCuti->n ?? 0) + $lamaCuti;

        // Update total dan sisa
        $dataCuti->jumlah = ($dataCuti->n ?? 0) + ($dataCuti->n_1 ?? 0) + ($dataCuti->n_2 ?? 0);
        $dataCuti->diambil = max(0, ($dataCuti->diambil ?? 0) - $lamaCuti);
        $dataCuti->sisa = max(0, $dataCuti->jumlah - $dataCuti->diambil);
        $dataCuti->save();
    }

    $cuti->delete();
    return redirect()->back()->with('success', 'Pengajuan cuti berhasil dibatalkan dan kuota cuti dikembalikan.');
}
    // ===== METHOD CETAK =====
    public function cetak($id_cuti)
    {
        $cuti = PengajuanCuti::with(['pengguna', 'penandatangan', 'tandatangan'])
            ->findOrFail($id_cuti);

        $dataCuti = DataCuti::where('id_pengguna', $cuti->id_pengguna)->first();
        $n2 = $dataCuti->n_2 ?? 0;
        $n1 = $dataCuti->n_1 ?? 0;
        $n  = $dataCuti->n ?? 0;

        $jumlah  = $n2 + $n1 + $n;
        $diambil = PengajuanCuti::where('id_pengguna', $cuti->id_pengguna)
            ->where('jenis_permohonan', 'Cuti Tahunan')
            ->sum('lama');
        $sisa = $jumlah - $diambil;
        if ($sisa < 0) $sisa = 0;

        $pdf = \PDF::loadView('pdf.permintaan_cuti', [
            'surat'   => $cuti,
            'dataCuti'=> $dataCuti,
            'n2'      => $n2,
            'n1'      => $n1,
            'n'       => $n,
            'jumlah'  => $jumlah,
            'diambil' => $diambil,
            'sisa'    => $sisa,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("formulir_cuti_{$cuti->id_cuti}.pdf");
    }

    // ===== METHOD hitungHariCuti =====
    public function hitungHariCuti($start, $end, $jenis = null)
    {
        $startDate = new \DateTime($start);
        $endDate   = new \DateTime($end);
        $endDate->modify('+1 day');

        $tanggalLibur = \DB::table('data_libur')->pluck('tanggal')->toArray();
        $interval = new \DateInterval('P1D');
        $period   = new \DatePeriod($startDate, $interval, $endDate);
        $jumlahHari = 0;

        foreach ($period as $date) {
            $hari = $date->format('N');
            $tgl  = $date->format('Y-m-d');

            if (strtolower($jenis) !== 'cuti tahunan') {
                $jumlahHari++;
                continue;
            }

            if ($hari == 6 || $hari == 7) continue;
            if (in_array($tgl, $tanggalLibur)) continue;
            $jumlahHari++;
        }

        return $jumlahHari;
    }

    // ===== METHOD hitungLama =====
    public function hitungLama(Request $request)
    {
        $request->validate([
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $jumlah = $this->hitungHariCuti(
            $request->tanggal_mulai,
            $request->tanggal_selesai,
            $request->jenis_permohonan
        );

        return response()->json(['jumlah_hari' => $jumlah]);
    }

    // ===== METHOD cekSisaCuti =====
    public function cekSisaCuti($id_pengguna)
    {
        $dataCuti = DataCuti::where('id_pengguna', $id_pengguna)->first();
        if (!$dataCuti) {
            return response()->json([
                'success' => false,
                'message' => 'Data cuti tidak ditemukan',
                'n' => 0
            ]);
        }

        return response()->json([
            'success' => true,
            'n' => $dataCuti->n ?? 0,
            'n_1' => $dataCuti->n_1 ?? 0,
            'n_2' => $dataCuti->n_2 ?? 0,
            'jumlah' => ($dataCuti->n ?? 0) + ($dataCuti->n_1 ?? 0) + ($dataCuti->n_2 ?? 0)
        ]);
    }

    // ===== METHOD cekOverlap =====
    public function cekOverlap($id_pengguna, Request $request)
    {
        $overlap = PengajuanCuti::where('id_pengguna', $id_pengguna)
            ->where(function($query) use ($request) {
                $mulai = $request->input('mulai');
                $selesai = $request->input('selesai');
                $query->whereBetween('tanggal_mulai', [$mulai, $selesai])
                      ->orWhereBetween('tanggal_selesai', [$mulai, $selesai])
                      ->orWhere(function($q) use ($mulai, $selesai) {
                          $q->where('tanggal_mulai', '<=', $mulai)
                            ->where('tanggal_selesai', '>=', $selesai);
                      });
            })
            ->exists();

        return response()->json(['overlap' => $overlap]);
    }

    // ===== METHOD cekCutiBesar - VERSI SIMPLE =====
public function cekCutiBesar($id_pengguna)
{
    $cutiBesarTerakhir = PengajuanCuti::where('id_pengguna', $id_pengguna)
        ->where('jenis_permohonan', 'Cuti Besar')
        ->orderBy('tanggal_mulai', 'desc')
        ->first();

    if (!$cutiBesarTerakhir) {
        return response()->json([
            'boleh' => true,
            'pesan' => 'Anda belum pernah mengambil cuti besar'
        ]);
    }

    $tanggalTerakhir = Carbon::parse($cutiBesarTerakhir->tanggal_mulai);
    $tanggalBolehLagi = $tanggalTerakhir->copy()->addYears(5);
    $sekarang = Carbon::now();
    
    // ✅ Cek apakah sudah boleh mengambil cuti besar lagi
    if ($sekarang->lt($tanggalBolehLagi)) {
        // ✅ HITUNG TOTAL BULAN SISA (dari sekarang ke tanggal boleh lagi)
        $totalBulanSisa = $sekarang->diffInMonths($tanggalBolehLagi);
        
        // ✅ Konversi ke tahun dan bulan
        $sisaTahun = floor($totalBulanSisa / 12);
        $sisaBulan = $totalBulanSisa % 12;
        
        return response()->json([
            'boleh' => false,
            'pesan' => "Anda sudah mengambil cuti besar pada {$tanggalTerakhir->format('d-m-Y')}. Cuti besar hanya bisa diambil setiap 5 tahun sekali.",
            'tanggal_terakhir' => $tanggalTerakhir->format('d-m-Y'),
            'sisa_tahun' => $sisaTahun,
            'sisa_bulan' => $sisaBulan
        ]);
    }

    return response()->json([
        'boleh' => true,
        'pesan' => 'Anda sudah bisa mengambil cuti besar lagi'
    ]);
}

    // ===== METHOD cekCutiBesarTahunIni =====
    public function cekCutiBesarTahunIni($id_pengguna, Request $request)
    {
        $tahun = $request->query('tahun', Carbon::now()->year);
        
        $adaCutiBesar = PengajuanCuti::where('id_pengguna', $id_pengguna)
            ->where('jenis_permohonan', 'Cuti Besar')
            ->whereYear('tanggal_mulai', $tahun)
            ->exists();
        
        return response()->json([
            'ada_cuti_besar' => $adaCutiBesar,
            'tahun' => $tahun
        ]);
    }
    // ===== METHOD cekStatusKepegawaian =====
public function cekStatusKepegawaian($id_pengguna)
{
    $pengguna = Pengguna::find($id_pengguna);
    
    if (!$pengguna) {
        return response()->json([
            'is_asn' => false,
            'status_kepegawaian' => null,
            'pesan' => 'Data pengguna tidak ditemukan'
        ]);
    }
    
    $statusKepegawaian = strtolower(trim($pengguna->status_kepegawaian ?? ''));
    
    // Cek apakah ASN
    $isASN = (strpos($statusKepegawaian, 'aparatur sipil negara') !== false) 
             || (strpos($statusKepegawaian, 'asn') !== false)
             || ($statusKepegawaian === 'pns');
    
    return response()->json([
        'is_asn' => $isASN,
        'status_kepegawaian' => $pengguna->status_kepegawaian,
        'pesan' => $isASN 
            ? 'Anda memenuhi syarat untuk mengajukan Cuti Besar' 
            : 'Cuti Besar hanya untuk pegawai dengan status ASN'
    ]);
}
}