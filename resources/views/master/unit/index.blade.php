@extends('layouts.app')

@section('title', 'Data Unit Kerja')

@section('content')
<link rel="stylesheet" href="{{ asset('css/dataunitkerja.css') }}">

<div class="container">
    <h3>Data Unit Kerja</h3>

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

    {{-- Tombol Tambah --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#addModal">
            + Add Unit Kerja
        </button>
    </div>

    <!-- Tabel Data -->
    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th width="50">No</th>
                <th>Nama Unit Kerja</th>
                <th>Sub Unit Kerja</th>
                <th width="150">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $i => $item)
            <tr>
                <td>{{ $data->firstItem() + $i }}</td>
                <td>{{ $item->nama_unit_kerja }}</td>
                <td>{{ $item->sub_unit_kerja ?? '-' }}</td>
                <td>
                    <div class="action-buttons-inline">
                        <a href="javascript:void(0)" class="btn-icon-edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id_unit_kerja }}">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" class="btn-icon-delete" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id_unit_kerja }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Modal Edit -->
            <div class="modal fade" id="editModal{{ $item->id_unit_kerja }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('unit.update', $item->id_unit_kerja) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5>Edit Unit Kerja</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="text" name="nama_unit_kerja" value="{{ $item->nama_unit_kerja }}" class="form-control mb-2" required>
                                <input type="text" name="sub_unit_kerja" value="{{ $item->sub_unit_kerja }}" class="form-control" placeholder="Sub Unit Kerja">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-gradient">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Hapus -->
            <div class="modal fade" id="deleteModal{{ $item->id_unit_kerja }}" tabindex="-1">
                <div class="modal-dialog modal-delete-wide">
                    <div class="modal-content">
                        <form action="{{ route('unit.destroy', $item->id_unit_kerja) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="modal-header">
                                <h5>Hapus Unit Kerja</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
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
            @empty
            <tr><td colspan="4" class="text-center">Belum ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    @if ($data->lastPage() > 1)
    <div class="d-flex justify-content-end mt-3">
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item {{ ($data->currentPage() == 1) ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $data->url($data->currentPage() - 1) }}" aria-label="Previous">&lt;</a>
                </li>

                @for ($i = 1; $i <= $data->lastPage(); $i++)
                    <li class="page-item {{ ($data->currentPage() == $i) ? 'active' : '' }}">
                        <a class="page-link" href="{{ $data->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor

                <li class="page-item {{ ($data->currentPage() == $data->lastPage()) ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $data->url($data->currentPage() + 1) }}" aria-label="Next">&gt;</a>
                </li>
            </ul>
        </nav>
    </div>
    @endif
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('unit.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5>Tambah Unit Kerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="nama_unit_kerja" class="form-control mb-2" placeholder="Nama Unit Kerja" required>
                    <input type="text" name="sub_unit_kerja" class="form-control" placeholder="Sub Unit Kerja">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-gradient">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
