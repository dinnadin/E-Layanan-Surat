@extends('layouts.app')

@section('title', 'Data Jabatan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/datajabatan.css') }}">

<div class="container">
    <h3>Data Jabatan</h3>

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

    <!-- Tombol Aksi -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#addModal">
            + Add Jabatan
        </button>
    </div>

    <!-- Tabel Data -->
    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th width="50">No</th>
                <th>Nama Jabatan</th>
                <th width="150">Usia Pensiun</th>
                <th width="120">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jabatans as $i => $jabatan)
            <tr>
                <td>{{ $jabatans->firstItem() + $i }}</td>
                <td>{{ $jabatan->nama_jabatan }}</td>
                <td class="text-center">
                    {{ $jabatan->usia_pensiun ? $jabatan->usia_pensiun . ' tahun' : '-' }}
                </td>
                <td>
                    <div class="action-buttons-inline">
                        <!-- Tombol Edit -->
                        <a href="javascript:void(0)" 
                           class="btn-icon-edit" 
                           data-bs-toggle="modal" 
                           data-bs-target="#editModal{{ $jabatan->id_jabatan }}">
                            <i class="bi bi-pencil"></i>
                        </a>

                        <!-- Tombol Hapus -->
                        <button type="button" 
                                class="btn-icon-delete" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal{{ $jabatan->id_jabatan }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Modal Edit -->
            <div class="modal fade" id="editModal{{ $jabatan->id_jabatan }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('jabatan.update', $jabatan->id_jabatan) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5>Edit Jabatan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Input Nama Jabatan -->
                                <div class="mb-3">
                                    <label for="edit_nama_jabatan{{ $jabatan->id_jabatan }}" class="form-label">
                                        Nama Jabatan <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           name="nama_jabatan" 
                                           id="edit_nama_jabatan{{ $jabatan->id_jabatan }}"
                                           value="{{ $jabatan->nama_jabatan }}" 
                                           class="form-control" 
                                           required>
                                </div>

                                <!-- Input Usia Pensiun -->
                                <div class="mb-3">
                                    <label for="edit_usia_pensiun{{ $jabatan->id_jabatan }}" class="form-label">
                                        Usia Pensiun <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           name="usia_pensiun" 
                                           id="edit_usia_pensiun{{ $jabatan->id_jabatan }}"
                                           value="{{ $jabatan->usia_pensiun }}" 
                                           class="form-control" 
                                           min="50" 
                                           max="70" 
                                           required>
                                    <small class="text-muted">Usia pensiun untuk jabatan ini</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-gradient">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Hapus -->
            <div class="modal fade" id="deleteModal{{ $jabatan->id_jabatan }}" tabindex="-1">
                <div class="modal-dialog modal-delete-wide">
                    <div class="modal-content">
                        <form action="{{ route('jabatan.destroy', $jabatan->id_jabatan) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="modal-header">
                                <h5>Hapus Jabatan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Apakah Anda yakin ingin menghapus data ini?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    @if ($jabatans->hasPages())
    <div class="d-flex justify-content-end mt-4">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                {{-- Tombol Sebelumnya --}}
                @if ($jabatans->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">&lt;</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $jabatans->previousPageUrl() }}" rel="prev">&lt;</a>
                    </li>
                @endif

                {{-- Nomor Halaman --}}
                @foreach ($jabatans->getUrlRange(1, $jabatans->lastPage()) as $page => $url)
                    @if ($page == $jabatans->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach

                {{-- Tombol Selanjutnya --}}
                @if ($jabatans->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $jabatans->nextPageUrl() }}" rel="next">&gt;</a>
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
            <form action="{{ route('jabatan.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5>Tambah Jabatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Input Nama Jabatan -->
                    <div class="mb-3">
                        <label for="nama_jabatan" class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="nama_jabatan" 
                               id="nama_jabatan"
                               class="form-control" 
                               placeholder="Contoh: Kepala Balai" 
                               required>
                    </div>

                    <!-- Input Usia Pensiun -->
                    <div class="mb-3">
                        <label for="usia_pensiun" class="form-label">Usia Pensiun <span class="text-danger">*</span></label>
                        <input type="number" 
                               name="usia_pensiun" 
                               id="usia_pensiun"
                               class="form-control" 
                               placeholder="Masukkan usia pensiun"
                               min="50" 
                               max="70" 
                               required>
                        <small class="text-muted">Usia pensiun untuk jabatan ini (50-70 tahun)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-gradient">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection