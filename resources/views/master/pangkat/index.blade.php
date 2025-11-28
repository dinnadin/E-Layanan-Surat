@extends('layouts.app')

@section('title', 'Data Pangkat/Golongan Ruang')

@section('content')
<link rel="stylesheet" href="{{ asset('css/datapangkat.css') }}">

<div class="container">
    <h3>Data Pangkat/Golongan Ruang</h3>

    {{-- Pesan Sukses --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tombol Tambah --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#addModal">
            + Add Pangkat/Golongan
        </button>
    </div>

    <!-- Tabel Data -->
    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th width="50">No</th>
                <th>Pangkat</th>
                <th>Golongan</th>
                <th>Ruang</th>
                <th width="150">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $i => $item)
            <tr>
                <td>{{ ($data->currentPage() - 1) * $data->perPage() + ($i + 1) }}</td>
                <td>{{ $item->pangkat }}</td>
                <td>{{ $item->golongan }}</td>
                <td>{{ $item->ruang }}</td>
                <td>
                    <div class="action-buttons-inline">
                        <a href="javascript:void(0)" class="btn-icon-edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id_pangkat }}">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" class="btn-icon-delete" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id_pangkat }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Modal Edit -->
            <div class="modal fade" id="editModal{{ $item->id_pangkat }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('pangkat.update', $item->id_pangkat) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5>Edit Pangkat/Golongan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="text" name="pangkat" value="{{ $item->pangkat }}" class="form-control mb-2" required>
                                <input type="text" name="golongan" value="{{ $item->golongan }}" class="form-control mb-2" placeholder="I-IV" required id="golonganEdit{{ $item->id_pangkat }}">
                                <input type="text" name="ruang" value="{{ $item->ruang }}" class="form-control" placeholder="a-e" maxlength="1" required id="ruangEdit{{ $item->id_pangkat }}">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-gradient">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Hapus -->
            <div class="modal fade" id="deleteModal{{ $item->id_pangkat }}" tabindex="-1">
                <div class="modal-dialog modal-delete-wide">
                    <div class="modal-content">
                        <form action="{{ route('pangkat.destroy', $item->id_pangkat) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="modal-header">
                                <h5>Hapus Pangkat/Golongan</h5>
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
            @empty
            <tr><td colspan="5" class="text-center">Belum ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="d-flex justify-content-end mt-4">
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-end">

                {{-- Tombol Sebelumnya (<) --}}
                @if ($data->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">&lt;</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $data->previousPageUrl() }}" aria-label="Previous">&lt;</a>
                    </li>
                @endif

                {{-- Nomor Halaman --}}
                @foreach ($data->getUrlRange(1, $data->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $data->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach

                {{-- Tombol Berikutnya (>) --}}
                @if ($data->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $data->nextPageUrl() }}" aria-label="Next">&gt;</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link">&gt;</span></li>
                @endif

            </ul>
        </nav>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('pangkat.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5>Tambah Pangkat/Golongan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="pangkat" class="form-control mb-2" placeholder="Nama Pangkat" required>
                    <input type="text" name="golongan" class="form-control mb-2" placeholder="I-IV" required id="golonganAdd">
                    <input type="text" name="ruang" class="form-control" placeholder="a-e" maxlength="1" required id="ruangAdd">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-gradient">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// === Validasi Golongan dan Ruang ===
function validateGolongan(input) {
    input.addEventListener('input', function() {
        const allowed = ['I','II','III','IV'];
        this.value = allowed.includes(this.value.toUpperCase()) ? this.value.toUpperCase() : '';
    });
}
function validateRuang(input) {
    input.addEventListener('input', function() {
        const val = this.value.toLowerCase();
        this.value = ['a','b','c','d','e'].includes(val) ? val : '';
    });
}
validateGolongan(document.getElementById('golonganAdd'));
validateRuang(document.getElementById('ruangAdd'));
@foreach($data as $item)
validateGolongan(document.getElementById('golonganEdit{{ $item->id_pangkat }}'));
validateRuang(document.getElementById('ruangEdit{{ $item->id_pangkat }}'));
@endforeach
</script>
@endsection