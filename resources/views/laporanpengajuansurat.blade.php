{{-- Gunakan layout berbeda sesuai role --}}
@extends(strtolower(session('role')) === 'admin' ? 'layouts.app' : 'layouts.appkepala')

@section('content')
<link rel="stylesheet" href="{{ asset('css/laporanpengajuansurat.css') }}">

<div class="container-fluid p-4 page-container">

  <h3 class="mb-3">Data Laporan Pengajuan Surat</h3>

  {{-- ✅ Search Box --}}
  <div class="search-box d-flex align-items-center mb-3">
      <div class="search-input-wrapper">
          <i class="bi bi-search"></i>
          <input type="text" id="searchInput" placeholder="Search">
      </div>
  </div>

  {{-- ✅ Filter row: left = form filter, right = tombol (sibling) --}}
  <div class="filter-row mb-3">

      {{-- Form filter (kiri) --}}
      <form method="GET" action="{{ route('laporan.pengajuan.surat') }}" class="form-filter mb-0">
          <select name="bulan" class="filter-select" aria-label="Bulan">
              <option value="">-- Bulan --</option>
              @for($m=1; $m<=12; $m++)
                  <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                      {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                  </option>
              @endfor
          </select>

          <select name="tahun" class="filter-select" aria-label="Tahun">
              <option value="">-- Tahun --</option>
              @foreach($tahunList as $y)
                  <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>
                      {{ $y }}
                  </option>
              @endforeach
          </select>
{{-- ✅ Tambahkan dropdown baru untuk jenis surat --}}
    <select name="jenis_surat" class="filter-select" aria-label="Jenis Surat">
        <option value="">-- Jenis Surat --</option>
        <option value="ijin" {{ request('jenis_surat') == 'ijin' ? 'selected' : '' }}>Surat Ijin</option>
        <option value="aktif" {{ request('jenis_surat') == 'aktif' ? 'selected' : '' }}>Surat Aktif</option>
        <option value="cuti" {{ request('jenis_surat') == 'cuti' ? 'selected' : '' }}>Surat Cuti</option>
    </select>
    
          <button type="submit" class="btn-filter">Filter</button>
      </form>

      {{-- Tombol Cetak & Export (kanan) - tampilkan untuk admin & kepala --}}
      @if(in_array(strtolower(session('role')), ['admin','kepala']))
      <div class="btn-wrapper">
          <a href="{{ route('laporanpengajuansurat', request()->only(['bulan','tahun'])) }}" class="btn-cetak" target="_blank" rel="noopener">Cetak PDF</a>
          <a href="{{ route('laporan_pengajuan.excel') }}" class="btn-excel">Export Excel</a>
      </div>
      @endif

  </div> {{-- akhir filter-row --}}

  {{-- Tabel --}}
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th style="width: 5%">No</th>
          <th style="width: 25%">Nama</th>
          <th style="width: 20%">NIP</th>
          <th style="width: 30%">Pengajuan Surat</th>
          <th style="width: 20%">Tanggal Pengajuan</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        @forelse($pengajuan as $index => $item)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->nama_lengkap }}</td>
            <td>{{ $item->nip }}</td>
            <td>
              @if($item->jenis_surat === 'Surat Ijin')
                  Surat Ijin ({{ $item->jenis_alasan ?? $item->keterangan_ijin ?? '-' }})
              @elseif($item->jenis_surat === 'Surat Aktif')
                  Surat Aktif ({{ $item->nomor_surat_aktif ?? '-' }})
              @elseif($item->jenis_surat === 'Cuti')
                  Surat Cuti ({{ $item->alasan_cuti ?? '-' }})
              @elseif(isset($item->nama))
                  {{ $item->nama }}
              @else
                  Tidak diketahui
              @endif
            </td>
            <td>{{ $item->tanggal_pengajuan ? \Carbon\Carbon::parse($item->tanggal_pengajuan)->format('d-m-Y') : '-' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-muted">Tidak ada data</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="d-flex justify-content-end mt-3">
    <nav aria-label="Pagination">
      <ul class="pagination mb-0">
        {{-- Previous --}}
        @if ($pengajuan->onFirstPage())
          <li class="page-item disabled"><span class="page-link">&lt;</span></li>
        @else
          <li class="page-item">
            <a class="page-link" href="{{ $pengajuan->appends(request()->query())->previousPageUrl() }}" aria-label="Previous">&lt;</a>
          </li>
        @endif

        {{-- Numbered links --}}
        @foreach ($pengajuan->getUrlRange(1, $pengajuan->lastPage()) as $page => $url)
          <li class="page-item {{ $page == $pengajuan->currentPage() ? 'active' : '' }}">
            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
          </li>
        @endforeach

        {{-- Next --}}
        @if ($pengajuan->hasMorePages())
          <li class="page-item">
            <a class="page-link" href="{{ $pengajuan->appends(request()->query())->nextPageUrl() }}" aria-label="Next">&gt;</a>
          </li>
        @else
          <li class="page-item disabled"><span class="page-link">&gt;</span></li>
        @endif
      </ul>
    </nav>
  </div>

</div>

{{-- Script filter search --}}
<script>
document.getElementById("searchInput")?.addEventListener("keyup", function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#tableBody tr");
    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});
</script>
@endsection