@php
    $role = strtolower(trim(session('role') ?? ''));
    if ($role === 'admin') {
        $layout = 'layouts.app';
        $bodyClass = 'layout-admin';
    } elseif (strpos($role, 'kepala') !== false) {
        $layout = 'layouts.appkepala';
        $bodyClass = 'layout-kepala';
    } elseif (strpos($role, 'pegawai') !== false) {
        $layout = 'layouts.apppegawai';
        $bodyClass = 'layout-pegawai';
    } else {
        $layout = 'layouts.apppegawai';
        $bodyClass = 'layout-pegawai';
    }
@endphp

@extends($layout)

@section('content')
{{-- Panggil file CSS eksternal --}}
<link rel="stylesheet" href="{{ asset('css/riwayatpengajuan.css') }}">

<div class="container content-riwayat {{ $bodyClass }}">
    <h3 class="judul-riwayat">Riwayat Pengajuan</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        <script>
            // Pastikan halaman benar-benar memuat ulang dari server, bukan cache
            if (performance.navigation.type === 2) {
                location.reload(true);
            }
        </script>
    @endif

    <form method="GET" action="{{ route('riwayat') }}" class="mb-3 d-flex gap-2">
        <select name="bulan" class="filter-select">
            <option value="">-- Bulan --</option>
            @for($m=1; $m<=12; $m++)
                <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
            @endfor
        </select>

        <select name="tahun" class="filter-select">
            <option value="">-- Tahun --</option>
            @foreach($tahunList as $y)
                <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
        
        {{-- ✅ Filter jenis surat --}}
        <select name="jenis_surat" class="filter-select">
            <option value="">-- Jenis Surat --</option>
            <option value="ijin" {{ request('jenis_surat') == 'ijin' ? 'selected' : '' }}>Surat Ijin</option>
            <option value="aktif" {{ request('jenis_surat') == 'aktif' ? 'selected' : '' }}>Surat Aktif</option>
            <option value="cuti" {{ request('jenis_surat') == 'cuti' ? 'selected' : '' }}>Surat Cuti</option>
        </select>
        
        <select name="status" class="filter-select">
            <option value="">-- Status Surat --</option>
            <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
        </select>

        <button type="submit" class="btn btn-filter">Filter</button>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-nomor">No. Surat</th>
                    <th class="col-jenis">Jenis Surat</th>
                    <th class="col-tgl-pengajuan">Tanggal Pengajuan</th>
                    <th class="col-tgl-disetujui">Tanggal Disetujui</th>
                    <th class="col-keterangan">Keterangan</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $riwayat)
                    <tr>
                        <td class="col-no">{{ $data->firstItem() + $loop->index }}</td>
                        <td class="col-nomor">{{ $riwayat->nomor_surat ?? '-' }}</td>
                        <td class="col-jenis">{{ $riwayat->jenis_surat }}</td>
                        
                        {{-- ✅ KOLOM TANGGAL - LOGIC BARU --}}
 <td class="col-tgl-pengajuan">
    @php
        $tanggalDisplay = '-';

        // 1 Surat Ijin → ambil mulai_tanggal dari tabel permintaan_surat_ijin
        if (stripos($riwayat->jenis_surat, 'Ijin') !== false && $riwayat->id_surat_ijin) {
            $suratIjin = \App\Models\SuratIjin::find($riwayat->id_surat_ijin);

            if ($suratIjin && $suratIjin->id_permintaan) {
                $permintaan = \App\Models\PermintaanSuratIjin::find($suratIjin->id_permintaan);

                if ($permintaan && $permintaan->mulai_tanggal) {
                    $tanggalDisplay = \Carbon\Carbon::parse($permintaan->mulai_tanggal)->format('d-m-Y');
                }
            }
        }

       // ✅ PERBAIKAN: Surat Aktif → ambil tanggal_pengajuan dari tabel permintaan_surat
elseif (stripos($riwayat->jenis_surat, 'Aktif') !== false && $riwayat->id_surat_aktif) {
    // Ambil data surat aktif dulu
    $suratAktif = \App\Models\SuratAktif::find($riwayat->id_surat_aktif);
    
    // Jika surat aktif ada, ambil permintaan surat berdasarkan id_permintaan
    if ($suratAktif && $suratAktif->id_permintaan) {
        $permintaan = \App\Models\PermintaanSurat::find($suratAktif->id_permintaan);
        
        if ($permintaan && $permintaan->tanggal_pengajuan) {
            $tanggalDisplay = \Carbon\Carbon::parse($permintaan->tanggal_pengajuan)->format('d-m-Y');
        }
    }
}

        // 3 Surat Cuti → ambil tanggal_pengajuan dari tabel pengajuan_cuti
        elseif (stripos($riwayat->jenis_surat, 'Cuti') !== false && $riwayat->id_cuti) {
            $cuti = \App\Models\PengajuanCuti::find($riwayat->id_cuti);
            if ($cuti && $cuti->tanggal_pengajuan) {
                $tanggalDisplay = \Carbon\Carbon::parse($cuti->tanggal_pengajuan)->format('d-m-Y');
            }
        }
    @endphp

    {{ $tanggalDisplay }}
</td>                       
                        <td class="col-tgl-disetujui">
                            @if($riwayat->jenis_surat !== 'Surat Cuti')
                                @if($riwayat->tanggal_disetujui)
                                    {{ \Carbon\Carbon::parse($riwayat->tanggal_disetujui)->format('d-m-Y') }}
                                @else
                                    -
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        
                        {{-- ✅ KOLOM KETERANGAN --}}
                        <td class="col-keterangan">
                            @php
                                $tipe = 'ijin';
                                if (stripos($riwayat->jenis_surat, 'Aktif') !== false) {
                                    $tipe = 'aktif';
                                } elseif (stripos($riwayat->jenis_surat, 'Cuti') !== false) {
                                    $tipe = 'cuti';
                                }

                                $suratId = $tipe === 'ijin' ? $riwayat->id_surat_ijin
                                            : ($tipe === 'aktif' ? $riwayat->id_surat_aktif
                                            : ($tipe === 'cuti' ? $riwayat->id_cuti : null));
                            @endphp

                            {{-- Jika surat ditolak --}}
                            @if(!empty($riwayat->keterangan) && Str::contains(strtoupper($riwayat->keterangan), 'DITOLAK'))
                                @php
                                    $alasan = $riwayat->keterangan;
                                    if (Str::contains(strtolower($alasan), 'alasan:')) {
                                        $alasan = trim(Str::after($alasan, 'Alasan:'));
                                    }
                                @endphp
                                <span class="text-danger">
                                    <b>Ditolak:</b> {{ $alasan ?: 'Tidak ada alasan yang diberikan' }}
                                </span>
                            
                            {{-- Jika surat disetujui (Surat Ijin) --}}
                            @elseif($suratId && $riwayat->jenis_surat === 'Surat Ijin')
                                {{ $riwayat->keterangan_ijin ?? '-' }}
                            
                            {{-- Default --}}
                            @else
                                -
                            @endif
                        </td>

                        {{-- ✅ KOLOM AKSI --}}
                        <td class="col-aksi">
                            @php
                                $tipe = 'ijin';
                                if (stripos($riwayat->jenis_surat, 'Aktif') !== false) {
                                    $tipe = 'aktif';
                                } elseif (stripos($riwayat->jenis_surat, 'Cuti') !== false) {
                                    $tipe = 'cuti';
                                }

                                $suratId = $tipe === 'ijin' ? $riwayat->id_surat_ijin
                                            : ($tipe === 'aktif' ? $riwayat->id_surat_aktif
                                            : ($tipe === 'cuti' ? $riwayat->id_cuti : null));
                            @endphp

                            @if($suratId)
                                <a href="{{ route('riwayat.showPdf', $riwayat->id) }}" class="btn btn-sm btn-lihat-detail">
                                    Lihat Detail
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada riwayat pengajuan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if ($data->lastPage() > 1)
        <ul class="pagination">
            @if ($data->onFirstPage())
                <li class="disabled"><span>‹</span></li>
            @else
                <li><a href="{{ $data->previousPageUrl() }}">‹</a></li>
            @endif

            @for ($i = 1; $i <= $data->lastPage(); $i++)
                @if ($i == $data->currentPage())
                    <li class="active"><span>{{ $i }}</span></li>
                @else
                    <li><a href="{{ $data->url($i) }}">{{ $i }}</a></li>
                @endif
            @endfor

            @if ($data->hasMorePages())
                <li><a href="{{ $data->nextPageUrl() }}">›</a></li>
            @else
                <li class="disabled"><span>›</span></li>
            @endif
        </ul>
    @endif
</div>
@endsection