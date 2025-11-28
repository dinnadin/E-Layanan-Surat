@php
    $role = strtolower(trim(session('role') ?? ''));
    $layout = (strcasecmp($role, 'kepala') === 0 || strcasecmp($role, 'ketua kelompok') === 0)
        ? 'layouts.appkepala'
        : 'layouts.apppegawai';
@endphp

@extends($layout)

@section('content')
{{-- CSS --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('css/pengajuanaktif.css') }}">

<div class="surat-wrapper">
    <div class="surat-container">
        <div class="card-form">
            <h5 class="card-title">
                <a href="javascript:history.back()" class="back-link" aria-label="Kembali">&larr;</a>
                SURAT KETERANGAN AKTIF
            </h5>

            {{-- FORM --}}
            <form id="formSuratAktif" method="POST" action="{{ route('surataktif.store') }}" class="form-custom">
                @csrf
                    <input type="hidden" name="status_kepegawaian" value="{{ $loginUser->status_kepegawaian }}">

                
                <div class="form-grid">
                    {{-- === Dropdown Kepala === --}}
                    <div class="form-item">
                        <label for="nama_kepala">Nama Kepala <span class="text-danger">*</span></label>
                        <select name="penandatangan_id" id="nama_kepala" class="form-control" required>
                            <option value="">-- Pilih Kepala --</option>
                            @foreach($kepala as $p)
                                <option value="{{ $p->id_pengguna }}"
                                    data-nip="{{ $p->nip ?? '-' }}"
                                    data-pangkat="{{ $p->pangkatGolongan->pangkat ?? '-' }} {{ $p->pangkatGolongan->golongan ?? '' }}/{{ $p->pangkatGolongan->ruang ?? '' }}"
                                    data-jabatan="{{ $p->pimpinan->nama_pimpinan ?? '-' }}">
                                    {{ $p->nama_lengkap }} — {{ $p->pimpinan->nama_pimpinan ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-item">
                        <label for="nip_kepala">NIP Kepala</label>
                        <input type="text" id="nip_kepala" class="form-control custom-input" readonly>
                    </div>

                    <div class="form-item">
                        <label for="pangkat_kepala">Pangkat/Golongan</label>
                        <input type="text" id="pangkat_kepala" class="form-control custom-input" readonly>
                    </div>

                    <div class="form-item">
                        <label for="jabatan_kepala">Pimpinan</label>
                        <input type="text" id="jabatan_kepala" class="form-control custom-input" readonly>
                    </div>
                </div>

                {{-- === Data Pegawai === --}}
                <div class="form-grid">
                    <div class="form-item">
                        <label>Nama Pegawai</label>
                        <input type="text" name="nama" class="form-control custom-input"
                               value="{{ $loginUser->nama_lengkap ?? '' }}" readonly>
                        <input type="hidden" name="id_pengguna" value="{{ $loginUser->id_pengguna ?? '' }}">
                    </div>

                    <div class="form-item">
                        <label>NIP Pegawai</label>
                        <input type="text" name="nip" class="form-control custom-input"
                               value="{{ $loginUser->nip ?? '' }}" readonly>
                    </div>

                    <div class="form-item">
                        <label>Pangkat/Golongan</label>
                        <input type="text" name="pangkat_golongan_ruang" class="form-control custom-input"
                               value="{{ $loginUser->pangkatGolongan->pangkat ?? '' }} {{ $loginUser->pangkatGolongan->golongan ?? '' }}/{{ $loginUser->pangkatGolongan->ruang ?? '' }}" readonly>
                    </div>

                    <div class="form-item">
                        <label>Jabatan</label>
                        <input type="text" name="jabatan" class="form-control custom-input"
                               value="{{ $loginUser->jabatan->nama_jabatan ?? '' }}" readonly>
                    </div>

                    {{-- ✅ STATUS KEPEGAWAIAN - Otomatis dari tabel pengguna --}}
                    <div class="form-item full">
                        <label>Status Kepegawaian</label>
                        <input type="text" class="form-control custom-input"
                               value="{{ $loginUser->status_kepegawaian ?? 'Belum diatur' }}" readonly>
                        
                        @if(!$loginUser->status_kepegawaian)
                            <small class="text-danger d-block mt-1">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>Status kepegawaian belum diisi!</strong> Silakan hubungi admin untuk mengupdate data Anda.
                            </small>
                        @endif
                    </div>
                </div>

                <div class="button-row">
                    <button type="reset" class="btn btn-reset px-4">RESET</button>
                    <button type="submit" class="btn btn-submit px-4" 
                            @if(!$loginUser->status_kepegawaian) 
                                disabled 
                                title="Status kepegawaian belum diisi. Hubungi admin untuk update data."
                                style="cursor: not-allowed; opacity: 0.6;"
                            @endif>
                        SUBMIT
                    </button>
                    
                    @if(!$loginUser->status_kepegawaian)
                        <small class="text-danger d-block mt-2 text-center">
                            <i class="bi bi-info-circle"></i> 
                            Tombol submit dinonaktifkan karena status kepegawaian belum diisi
                        </small>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Peringatan - NO BACKDROP -->
<div class="modal fade" id="tanggalModal" tabindex="-1" 
     aria-labelledby="tanggalModalLabel" aria-hidden="true"
     data-bs-backdrop="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="tanggalModalLabel">⚠️ Peringatan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-4">
        <p class="mb-0">
          ❗ Anda masih memiliki pengajuan yang belum disetujui.<br>
          Silakan tunggu hingga pengajuan sebelumnya disetujui.
        </p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-danger px-5" data-bs-dismiss="modal">
          Tutup
        </button>
      </div>
    </div>
  </div>
</div>

{{-- JS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    console.log('✅ Script loaded');

    // Inisialisasi Select2
    $('#nama_kepala').select2({
        placeholder: "-- Pilih Kepala --",
        allowClear: true,
        width: '100%',
        dropdownParent: $('body')
    });

    console.log('✅ Select2 initialized');

    // Auto-fill data kepala
    $('#nama_kepala').on('change', function() {
        let option = this.selectedOptions[0];
        if (!option || !option.value) {
            $('#nip_kepala').val('');
            $('#pangkat_kepala').val('');
            $('#jabatan_kepala').val('');
            return;
        }
        $('#nip_kepala').val(option.getAttribute('data-nip') || '');
        $('#pangkat_kepala').val(option.getAttribute('data-pangkat') || '');
        $('#jabatan_kepala').val(option.getAttribute('data-jabatan') || '');
    });

    // ===================================
    // FIX MODAL - DEBUGGING & HANDLER
    // ===================================
    
    // Event ketika modal akan ditampilkan
    $('#tanggalModal').on('show.bs.modal', function (e) {
        console.log('🔵 Modal akan ditampilkan');
        
        // Tutup semua Select2 yang terbuka
        $('.select2-container--open').each(function() {
            console.log('🔴 Menutup Select2');
            $(this).prev('select').select2('close');
        });
    });

    // Event SETELAH modal sudah ditampilkan
    $('#tanggalModal').on('shown.bs.modal', function (e) {
        console.log('✅ Modal sudah ditampilkan');
        
        // ✅ Set pointer-events SETELAH modal render
        $('.modal-backdrop').css({
            'pointer-events': 'none',
            'z-index': '1040'
        });
        
        $('#tanggalModal').css({
            'pointer-events': 'none',
            'z-index': '1050'
        });
        
        $('#tanggalModal .modal-dialog').css({
            'pointer-events': 'auto',
            'z-index': '1051'
        });
        
        console.log('✅ Pointer events sudah disesuaikan');
        console.log('Z-index modal:', $(this).css('z-index'));
        console.log('Z-index backdrop:', $('.modal-backdrop').css('z-index'));
        
        // Paksa focus ke modal
        $(this).focus();
    });

    // Event ketika modal ditutup
    $('#tanggalModal').on('hidden.bs.modal', function (e) {
        console.log('❌ Modal ditutup');
    });

    // ✅ HANDLER BUTTON TUTUP - Multiple Methods
    
    // Method 1: Via data-bs-dismiss
    $('#tanggalModal').on('click', '[data-bs-dismiss="modal"]', function(e) {
        console.log('🟢 Klik button tutup (Method 1)');
        e.preventDefault();
        e.stopPropagation();
        $('#tanggalModal').modal('hide');
    });

    // Method 2: Via class btn-danger
    $('#tanggalModal .btn-danger').on('click', function(e) {
        console.log('🟢 Klik button tutup (Method 2)');
        e.preventDefault();
        e.stopPropagation();
        $('#tanggalModal').modal('hide');
    });

    // Method 3: Via btn-close
    $('#tanggalModal .btn-close').on('click', function(e) {
        console.log('🟢 Klik button close X (Method 3)');
        e.preventDefault();
        e.stopPropagation();
        $('#tanggalModal').modal('hide');
    });

    // Method 4: Klik backdrop
    $('#tanggalModal').on('click', function(e) {
        if ($(e.target).hasClass('modal')) {
            console.log('🟢 Klik backdrop');
            $(this).modal('hide');
        }
    });

    // Method 5: Tombol ESC
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#tanggalModal').hasClass('show')) {
            console.log('🟢 Tekan ESC');
            $('#tanggalModal').modal('hide');
        }
    });

    // ===================================
    // SUBMIT FORM
    // ===================================
    
    $('#formSuratAktif').on('submit', function(e) {
        e.preventDefault();
        console.log('📤 Form submitted');

        const namaKepala = $('#nama_kepala').val();
        const idPengguna = $('input[name="id_pengguna"]').val();

        if (!namaKepala) { 
            alert('Nama Kepala harus dipilih!'); 
            return false; 
        }
        
        if (!idPengguna) { 
            alert('Session pengguna tidak ditemukan! Silakan login ulang.'); 
            return false; 
        }

        console.log('🔍 Mengecek status pengajuan...');

        $.ajax({
            url: "{{ route('surataktif.checkTanggal') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id_pengguna: idPengguna
            },
            success: function(response) {
                console.log('✅ Response:', response);
                
                if (response.exists) {
                    console.log('⚠️ Pengajuan pending ditemukan, menampilkan modal');
                    
                    // Tutup semua select2 sebelum menampilkan modal
                    $('#nama_kepala').select2('close');
                    
                    // Delay kecil untuk memastikan select2 tertutup
                    setTimeout(function() {
                        $('#tanggalModal').modal('show');
                    }, 100);
                } else {
                    console.log('✅ Tidak ada pengajuan pending, submit form');
                    $('#formSuratAktif')[0].submit();
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error AJAX:', error);
                alert('Terjadi kesalahan saat mengecek status pengajuan.');
            }
        });
    });

    // Reset form
    $('.btn-reset').on('click', function() {
        console.log('🔄 Reset form');
        $('#nip_kepala').val('');
        $('#pangkat_kepala').val('');
        $('#jabatan_kepala').val('');
    });

    // Tampilkan modal jika ada error dari session
    @if(session('error'))
        console.log('⚠️ Session error detected, showing modal');
        
        // Tutup semua select2
        $('.select2-container--open').each(function() {
            $(this).prev('select').select2('close');
        });
        
        // Delay kecil sebelum tampilkan modal
        setTimeout(function() {
            $('#tanggalModal').modal('show');
        }, 200);
    @endif

    // ===================================
    // DEBUG INFO (Hapus setelah testing)
    // ===================================
    
    // Log klik di mana saja
    $(document).on('click', function(e) {
        console.log('🖱️ Clicked:', {
            target: e.target.tagName,
            class: e.target.className,
            id: e.target.id,
            text: $(e.target).text().substring(0, 20)
        });
    });
});
</script>
@endsection