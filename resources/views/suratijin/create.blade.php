@php
    $role = strtolower(trim(session('role') ?? ''));
    if (strcasecmp($role, 'kepala') === 0 || strcasecmp($role, 'ketua kelompok') === 0) {
        $layout = 'layouts.appkepala';
    } else {
        $layout = 'layouts.apppegawai';
    }
@endphp

@extends($layout)

@section('content')
<link rel="stylesheet" href="{{ asset('css/pengajuanizin.css') }}">
<div class="surat-container">
    <div class="card-form">
        <h5 class="card-title">
            <a href="javascript:history.back()" class="back-link" aria-label="Kembali">&larr;</a>
            SURAT IJIN KELUAR
        </h5>

        <form action="{{ route('permintaan.store') }}" method="POST" class="form-custom">
            @csrf
            {{-- Nama & NIP --}}
            <div class="field-row">
                <div class="field-group">
                    <label class="field-label">Nama</label>
                    <input type="text" class="form-control custom-input" 
                        value="{{ $loginUser->nama_lengkap }}" readonly>
                    <input type="hidden" name="id_pengguna" value="{{ $loginUser->id_pengguna }}">
                </div>
                <div class="field-group">
                    <label class="field-label">NIP</label>
                    <input type="text" class="form-control custom-input" 
                        value="{{ $loginUser->nip }}" readonly>
                    <input type="hidden" name="nip" value="{{ $loginUser->nip }}">
                </div>
            </div>

            {{-- Jabatan & Unit Kerja --}}
            <div class="field-row">
                <div class="field-group">
                    <label class="field-label">Jabatan</label>
                    <input type="text" class="form-control custom-input" 
                        value="{{ $loginUser->jabatan->nama_jabatan ?? '-' }}" readonly>
                    <input type="hidden" name="jabatan" value="{{ $loginUser->jabatan->nama_jabatan ?? '-' }}">
                </div>
                <div class="field-group">
                    <label class="field-label">Unit Kerja</label>
                    <input type="text" class="form-control custom-input" 
                        value="{{ $loginUser->unitKerja->nama_unit_kerja ?? '-' }}" readonly>
                    <input type="hidden" name="unit_kerja" value="{{ $loginUser->unitKerja->nama_unit_kerja ?? '-' }}">
                </div>
            </div>

            {{-- Tanggal & Jam Mulai --}}
            <div class="field-row">
                <div class="field-group">
                    <label class="field-label">Tanggal</label>
                    <input type="date" id="mulai_tanggal" name="mulai_tanggal" class="form-control custom-input" required>
                </div>
                <div class="field-group">
                    <label class="field-label">Jam Mulai</label>
                    <input type="time" id="mulai_jam" name="mulai_jam" class="form-control custom-input" required>
                </div>
            </div>

            {{-- Jam Selesai & Jenis Alasan --}}
            <div class="field-row">
                <div class="field-group">
                    <label class="field-label">Jam Selesai</label>
                    <input type="time" id="selesai_jam" name="selesai_jam" class="form-control custom-input" required>
                </div>
                <div class="field-group">
                    <label class="field-label">Jenis Alasan</label>
                    <select id="jenis_alasan" name="jenis_alasan" class="form-control custom-input" required>
                        <option value="">-- Pilih Jenis Alasan --</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Izin Pribadi">Izin Pribadi</option>
                        <option value="Dinas Luar">Dinas Luar</option>
                    </select>
                </div>
            </div>

            {{-- Deskripsi Alasan --}}
            <div class="field-row">
                <div class="field-group">
                    <label class="field-label">Deskripsi Alasan</label>
                    <textarea id="deskripsi_alasan" name="deskripsi_alasan" class="form-control custom-input" required></textarea>
                </div>
            </div>

            <hr style="margin-top: 20px;">

            {{-- Penandatangan --}}
            <div class="field-row">
                <div class="field-group">
                    <label class="field-label">Penandatangan</label>
                    <select name="penandatangan_id" id="penandatangan_id" required class="form-control custom-input select2-fix">
                        <option value="">-- Pilih Penandatangan --</option>
                        @foreach($pimpinanList as $p)
                            <option value="{{ $p->id_pengguna }}"
                                data-nip="{{ $p->nip }}"
                                data-jabatan="{{ $p->jabatan->nama_jabatan ?? '-' }}"
                                data-unit="{{ $p->unitkerja->nama_unit_kerja ?? '-' }}">
                                {{ $p->nama_lengkap }} - ({{ $p->pimpinan->nama_pimpinan ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field-group">
                    <label class="field-label">NIP Kepala</label>
                    <input type="text" id="nip_kepala" name="nip_kepala" class="form-control custom-input" readonly>
                </div>
            </div>

            {{-- Jabatan & Unit Kerja Kepala --}}
            <div class="field-row">
                <div class="field-group">
                    <label class="field-label">Jabatan</label>
                    <input type="text" id="jabatan_kepala" name="jabatan_kepala" class="form-control custom-input" readonly>
                </div>
                <div class="field-group">
                    <label class="field-label">Unit Kerja</label>
                    <input type="text" id="unit_kerja_kepala" name="unit_kerja_kepala" class="form-control custom-input" readonly>
                </div>
            </div>

            {{-- Tombol --}}
            <div class="button-row">
                <button type="reset" class="btn btn-reset px-4">RESET</button>
                <button type="submit" class="btn btn-submit px-4">SUBMIT</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Peringatan (Bootstrap) --}}
<div class="modal fade" id="peringatanModal" tabindex="-1" aria-labelledby="peringatanModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="peringatanModalLabel">Peringatan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p>❗ Maaf, tanggal pengajuan Anda beririsan dengan pengajuan sebelumnya.</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

{{-- Script --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Inisialisasi Select2
    $('#penandatangan_id').select2({
        placeholder: "-- Pilih Kepala/Ketua Kelompok --",
        allowClear: true,
        width: '100%'
    });

    // Autofill data kepala
    $('#penandatangan_id').on('change', function() {
        let option = this.selectedOptions[0];
        if (!option) return;
        $('#nip_kepala').val(option.getAttribute('data-nip') || '');
        $('#jabatan_kepala').val(option.getAttribute('data-jabatan') || '');
        $('#unit_kerja_kepala').val(option.getAttribute('data-unit') || '');
    });

    // Tampilkan Modal Bootstrap jika ada error dari session
    @if(session('error'))
        $('#peringatanModal').modal('show');
    @endif
});
</script>

{{-- Modal z-index fix --}}
<style>
.modal { z-index: 2000 !important; }
.modal-backdrop { z-index: 1999 !important; }
</style>
@endsection