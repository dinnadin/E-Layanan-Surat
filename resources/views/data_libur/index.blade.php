@extends('layouts.app')

@section('title', 'Data Libur')

@section('content')
<link rel="stylesheet" href="{{ asset('css/datalibur.css') }}">

<div class="container">
    <h3>Data Libur</h3>

    {{-- Pesan Sukses --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tombol buka modal tambah -->
    <button class="btn btn-gold mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
        + Add Data Libur
    </button>

    <!-- Tabel Data -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th width="50">No</th>
                <th>Tanggal</th>
                <th>Deskripsi</th>
                <th width="180">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groupedLibur = $libur->groupBy('deskripsi');
            @endphp

            @foreach($groupedLibur as $deskripsi => $items)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    @php
                        $dates = $items->pluck('tanggal')->sort();
                        $first = \Carbon\Carbon::parse($dates->first())->format('d-m-Y');
                        $last = \Carbon\Carbon::parse($dates->last())->format('d-m-Y');
                    @endphp
                    {{ $first === $last ? $first : $first . ' s/d ' . $last }}
                </td>
                <td>{{ $deskripsi ?? '-' }}</td>
                <td>
                    @php $firstItem = $items->first(); @endphp
                    <div class="action-buttons-inline">
                        <!-- Tombol Edit -->
                        <a href="javascript:void(0)" 
                           class="btn-icon-edit" 
                           data-bs-toggle="modal" 
                           data-bs-target="#editModal{{ $firstItem->id_tanggal }}"
                           title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>

                        <!-- Tombol Hapus Satuan -->
                        <button type="button" 
                                class="btn-icon-delete btn-delete" 
                                data-id="{{ $firstItem->id_tanggal }}"
                                data-url="{{ route('data_libur.destroy', $firstItem->id_tanggal) }}"
                                title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Modal Edit -->
            @foreach($items as $item)
            <div class="modal fade" id="editModal{{ $item->id_tanggal }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('data_libur.update', $item->id_tanggal) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5>Edit Data Libur</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="date" name="tanggal" value="{{ $item->tanggal }}" class="form-control mb-2" required>
                                <textarea name="deskripsi" class="form-control" rows="3">{{ $item->deskripsi }}</textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-gradient">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('data_libur.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5>Tambah Data Libur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Input tanggal -->
                    <div class="input-group mb-2">
                        <input type="text" id="tanggal" name="tanggal" class="form-control" placeholder="Pilih tanggal" required>
                        <span class="input-group-text">
                            <i class="bi bi-calendar-date"></i>
                        </span>
                    </div>
                    <small class="text-muted">Bisa pilih lebih dari satu tanggal</small>

                    <textarea name="deskripsi" class="form-control mt-3" rows="3" placeholder="Deskripsi"></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-gradient">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus (Global) -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="deleteForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-header">
          <h5>Hapus Data</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="deleteModalBody">
          Apakah Anda yakin ingin menghapus data ini?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Hapus</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal Warning Duplikat --}}
@if(session('warning_modal'))
<div class="modal fade" id="warningModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('warning_modal')['title'] }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if(!empty(session('warning_modal')['duplikat']))
                    <div class="alert alert-warning">
                        <strong>Tanggal yang sudah ada:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach(session('warning_modal')['duplikat'] as $item)
                                <li>
                                    <strong>{{ $item['tanggal'] }}</strong> - 
                                    {{ $item['deskripsi'] ?: 'Tanpa deskripsi' }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty(session('warning_modal')['berhasil']))
                    <div class="alert alert-success">
                        <strong>Tanggal yang berhasil ditambahkan:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach(session('warning_modal')['berhasil'] as $tanggal)
                                <li>{{ $tanggal }}</li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="text-muted mb-0">Tidak ada tanggal baru yang ditambahkan.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
flatpickr("#tanggal", { mode: "multiple", dateFormat: "Y-m-d", appendTo: document.getElementById('addModal') });

document.addEventListener('DOMContentLoaded', function () {
    const modalBody = document.getElementById('deleteModalBody');
    const modalForm = document.getElementById('deleteForm');

    // Hapus 1 data
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function () {
            modalForm.setAttribute('action', this.dataset.url);
            modalBody.innerText = "Apakah Anda yakin ingin menghapus data ini?";
            new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
        });
    });
});
 // Auto show modal jika ada warning
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('warning_modal'))
            new bootstrap.Modal(document.getElementById('warningModal')).show();
        @endif
    });
</script>
@endsection