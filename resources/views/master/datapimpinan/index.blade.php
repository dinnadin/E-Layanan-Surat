@extends('layouts.app')

@section('title', 'Data Pimpinan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/datapimpinan.css') }}">

<div class="container">
    <h3>Data Pimpinan</h3>

    {{-- Pesan Sukses --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Pesan Error --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- 🔥 TAMBAHAN: Error Validation --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tombol Aksi -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#addModal">
            + Add Pimpinan
        </button>
    </div>

    <!-- Tabel Data -->
    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th width="50">No</th>
                <th>Nama Pimpinan</th>
                <th width="120">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataPimpinan as $i => $pimpinan)
            <tr>
                <td>{{ $dataPimpinan->firstItem() + $i }}</td>
                <td>{{ $pimpinan->nama_pimpinan }}</td>
                <td>
                    <div class="action-buttons-inline">
                        <!-- Tombol Edit -->
                        <a href="javascript:void(0)" 
                           class="btn-icon-edit" 
                           data-bs-toggle="modal" 
                           data-bs-target="#editModal{{ $pimpinan->id_pimpinan }}">
                            <i class="bi bi-pencil"></i>
                        </a>

                        <!-- Tombol Hapus -->
                        <button type="button" 
                                class="btn-icon-delete" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal{{ $pimpinan->id_pimpinan }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Modal Edit -->
            <div class="modal fade" id="editModal{{ $pimpinan->id_pimpinan }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('data-pimpinan.update', $pimpinan->id_pimpinan) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5>Edit Pimpinan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                {{-- 🔥 TAMBAHKAN class is-invalid jika ada error --}}
                                <label for="nama_pimpinan_edit_{{ $pimpinan->id_pimpinan }}" class="form-label">Nama Pimpinan</label>
                                <input type="text" 
                                       id="nama_pimpinan_edit_{{ $pimpinan->id_pimpinan }}"
                                       name="nama_pimpinan" 
                                       value="{{ old('nama_pimpinan', $pimpinan->nama_pimpinan) }}" 
                                       class="form-control @error('nama_pimpinan') is-invalid @enderror" 
                                       required>
                                {{-- 🔥 TAMPILKAN pesan error --}}
                                @error('nama_pimpinan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-gradient">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Hapus -->
            <div class="modal fade" id="deleteModal{{ $pimpinan->id_pimpinan }}" tabindex="-1">
                <div class="modal-dialog modal-delete-wide">
                    <div class="modal-content">
                        <form action="{{ route('data-pimpinan.destroy', $pimpinan->id_pimpinan) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="modal-header">
                                <h5>Hapus Pimpinan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Apakah Anda yakin ingin menghapus data <strong>{{ $pimpinan->nama_pimpinan }}</strong>?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <tr>
                <td colspan="3" class="text-center">Tidak ada data pimpinan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    @if ($dataPimpinan->hasPages())
    <div class="d-flex justify-content-end mt-4">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                {{-- Tombol Sebelumnya --}}
                @if ($dataPimpinan->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">&lt;</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $dataPimpinan->previousPageUrl() }}" rel="prev">&lt;</a>
                    </li>
                @endif

                {{-- Nomor Halaman --}}
                @foreach ($dataPimpinan->getUrlRange(1, $dataPimpinan->lastPage()) as $page => $url)
                    @if ($page == $dataPimpinan->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach

                {{-- Tombol Selanjutnya --}}
                @if ($dataPimpinan->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $dataPimpinan->nextPageUrl() }}" rel="next">&gt;</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link">&gt;</span></li>
                @endif
            </ul>
        </nav>
    </div>
    @endif
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('data-pimpinan.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5>Tambah Pimpinan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- 🔥 TAMBAHKAN class is-invalid jika ada error --}}
                    <label for="nama_pimpinan_add" class="form-label"></label>
                    <input type="text" 
                           id="nama_pimpinan_add"
                           name="nama_pimpinan" 
                           value="{{ old('nama_pimpinan') }}"
                           class="form-control @error('nama_pimpinan') is-invalid @enderror" 
                           placeholder="Nama Pimpinan" 
                           required>
                    {{-- 🔥 TAMPILKAN pesan error --}}
                    @error('nama_pimpinan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gradient">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 🔥 TAMBAHAN: Script untuk auto-show modal jika ada error --}}
@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Jika ada error, buka kembali modal yang terakhir digunakan
        @if(old('_method') === 'PUT')
            // Untuk edit modal - perlu logic tambahan untuk tahu ID mana
            // Bisa pakai session atau localStorage
        @else
            // Untuk add modal
            var addModal = new bootstrap.Modal(document.getElementById('addModal'));
            addModal.show();
        @endif
    });
</script>
@endif

@endsection