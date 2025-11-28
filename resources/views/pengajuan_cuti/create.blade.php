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
<link rel="stylesheet" href="{{ asset('css/pengajuancuti.css') }}">

{{-- CSS untuk Modal Warning --}}
<style>
/* Custom styling untuk SweetAlert2 Modal */
.swal-custom-popup {
    border-radius: 15px !important;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2) !important;
}

.swal-html-custom {
    padding: 0 !important;
    margin: 0 !important;
}

.swal2-icon.swal2-warning {
    border-color: #ffc107 !important;
    color: #ffc107 !important;
}

/* Animasi shake untuk icon warning */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.swal2-icon.swal2-warning {
    animation: shake 0.5s;
}

/* Styling untuk tombol confirm */
.swal2-confirm {
    padding: 10px 30px !important;
    font-weight: 600 !important;
    font-size: 15px !important;
    border-radius: 8px !important;
    transition: all 0.3s ease !important;
}

.swal2-confirm:hover {
    transform: scale(1.05) !important;
    box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3) !important;
}
</style>

<div class="container" style="padding:10px; margin-top:-10px;">
   @if(session('success')) 
    <div class="alert alert-success custom-alert">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger custom-alert">{{ session('error') }}</div>
@endif

    <div class="surat-container">
        <div class="card-form">
            <h5 style="margin-top:0;">
                <a href="javascript:history.back()" class="back-link">&larr;</a>
                SURAT CUTI
            </h5>

            <form action="{{ route('pengajuan_cuti.store') }}" method="POST" onsubmit="return validateForm()">
                @csrf
                <input type="hidden" 
                       name="id_pengguna" 
                       value="{{ is_array(auth()->user()) ? auth()->user()['id_pengguna'] : auth()->user()->id_pengguna }}">
                
                {{-- Baris 1: Nama & NIP --}}
                <div class="form-row">
                    <div class="form-group">
                        <label for="nama">Nama</label>
                        <input type="text" id="nama" name="nama" value="{{ $pengguna->nama_lengkap ?? '' }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="nip">NIP</label>
                        <input type="text" id="nip" name="nip" value="{{ $pengguna->nip ?? '' }}" readonly>
                    </div>
                </div>

                {{-- Baris 2: Jabatan & Unit Kerja --}}
                <div class="form-row">
                    <div class="form-group">
                        <label for="jabatan">Jabatan</label>
                        <input type="text" id="jabatan" name="jabatan" value="{{ $pengguna->jabatan->nama_jabatan ?? '' }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="unit_kerja">Unit Kerja</label>
                        <input type="text" id="unit_kerja" name="unit_kerja" value="{{ $pengguna->unitKerja->nama_unit_kerja ?? '' }}" readonly>
                    </div>
                </div>

                {{-- Baris 3: Jenis Cuti & Alasan --}}
                <div class="form-row">
                    <div class="form-group">
                        <label for="jenis_permohonan">Jenis Permohonan Cuti <span class="text-danger">*</span></label>
                        <select id="jenis_permohonan" name="jenis_permohonan" required>
                            <option value="">-- Pilih Jenis Cuti --</option>
                            <option value="Cuti Tahunan" selected>Cuti Tahunan</option>
                            <option value="Cuti Besar">Cuti Besar (Max 3 Bulan, Setiap 5 Tahun)</option>
                            <option value="Cuti Sakit">Cuti Sakit</option>
                            <option value="Cuti Melahirkan">Cuti Melahirkan (Max 3 Bulan)</option>
                            <option value="Cuti Alasan Penting">Cuti Alasan Penting (Max 3 Bulan)</option>
                        </select>
                        @error('jenis_permohonan')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="alasan">Alasan Cuti <span class="text-danger">*</span></label>
                        <input type="text" id="alasan" name="alasan" required>
                        @error('alasan')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                {{-- Baris 4: Tanggal Pengajuan & Tanggal Mulai --}}
                <div class="form-row">
                    <div class="form-group">
                        <label for="tanggal_pengajuan">Tanggal Pengajuan <span class="text-danger">*</span></label>
                        <input type="date" id="tanggal_pengajuan" name="tanggal_pengajuan" required>
                        @error('tanggal_pengajuan')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="tanggal_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" id="tanggal_mulai" name="tanggal_mulai" required>
                        @error('tanggal_mulai')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                {{-- Baris 5: Tanggal Selesai & Jumlah Hari --}}
                <div class="form-row">
                    <div class="form-group">
                        <label for="tanggal_selesai">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" id="tanggal_selesai" name="tanggal_selesai" required>
                        @error('tanggal_selesai')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="jumlah_hari">Jumlah Hari</label>
                        <input type="text" id="jumlah_hari" name="jumlah_hari" readonly>
                    </div>
                </div>

                {{-- Alamat Cuti --}}
                <div class="form-group">
                    <label for="alamat_cuti">Alamat Selama Menjalankan Cuti <span class="text-danger">*</span></label>
                    <textarea id="alamat_cuti" name="alamat_cuti" rows="3" required></textarea>
                    @error('alamat_cuti')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Dropdown Penandatangan Berdasarkan Role --}}
                @php 
                    $role = strtolower(trim(auth()->user()->role ?? '')); 
                    $isPimpinanKetuaTim = false;
                    if ($role === 'kepala' && isset($pengguna->pimpinan)) {
                        $namaPimpinan = strtolower(trim($pengguna->pimpinan->nama_pimpinan ?? ''));
                        $isPimpinanKetuaTim = (strpos($namaPimpinan, 'ketua tim') !== false);
                    }
                @endphp

@if ($role === 'pegawai')
    <div class="form-row">
        {{-- ✅ DROPDOWN KETUA TIM KERJA --}}
        <div class="form-group">
            <label for="tandatangan_id">Ketua Tim Kerja <span class="text-danger">*</span></label>
            <select name="tandatangan_id" id="tandatangan_id" required>
                <option value="">-- Pilih Ketua Tim Kerja --</option>
                @foreach($ketuaTim ?? [] as $item)
                    <option value="{{ $item['id_pengguna'] }}">
                        {{ $item['nama_lengkap'] }} ({{ $item['nama_pimpinan'] }})
                    </option>
                @endforeach
            </select>
            @error('tandatangan_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- ✅ DROPDOWN KEPALA KELOMPOK --}}
        <div class="form-group">
            <label for="penandatangan_id">Kepala/Ketua Kelompok <span class="text-danger">*</span></label>
            <select name="penandatangan_id" id="penandatangan_id" required>
                <option value="">-- Pilih Kepala/Ketua Kelompok --</option>
                @foreach($kepalaKelompok ?? [] as $item)
                    <option value="{{ $item['id_pengguna'] }}">
                        {{ $item['nama_lengkap'] }} ({{ $item['nama_pimpinan'] }})
                    </option>
                @endforeach
            </select>
            @error('penandatangan_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>

@elseif ($role === 'kepala' && $isPimpinanKetuaTim)
    <div class="form-row">
        {{-- ✅ DROPDOWN KEPALA KELOMPOK --}}
        <div class="form-group">
            <label for="tandatangan_id">Kepala/Ketua Kelompok <span class="text-danger">*</span></label>
            <select name="tandatangan_id" id="tandatangan_id" required>
                <option value="">-- Pilih Kepala/Ketua Kelompok --</option>
                @foreach($kepalaKelompok ?? [] as $item)
                    <option value="{{ $item['id_pengguna'] }}">
                        {{ $item['nama_lengkap'] }} ({{ $item['nama_pimpinan'] }})
                    </option>
                @endforeach
            </select>
            @error('tandatangan_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- ✅ DROPDOWN KEPALA BALAI --}}
        <div class="form-group">
            <label for="penandatangan_id">Kepala Balai <span class="text-danger">*</span></label>
            <select name="penandatangan_id" id="penandatangan_id" required>
                <option value="">-- Pilih Kepala Balai --</option>
                @foreach($kepalaBalai ?? [] as $item)
                    <option value="{{ $item['id_pengguna'] }}">
                        {{ $item['nama_lengkap'] }} ({{ $item['nama_pimpinan'] }})
                    </option>
                @endforeach
            </select>
            @error('penandatangan_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>

@elseif ($role === 'kepala')
    <input type="hidden" name="tandatangan_id" value="{{ auth()->user()->id_pengguna }}">
    
    {{-- ✅ DROPDOWN KEPALA BALAI --}}
    <div class="form-group">
        <label for="penandatangan_id">Kepala Balai <span class="text-danger">*</span></label>
        <select name="penandatangan_id" id="penandatangan_id" required>
            <option value="">-- Pilih Kepala Balai --</option>
            @foreach($kepalaBalai ?? [] as $item)
                <option value="{{ $item['id_pengguna'] }}">
                    {{ $item['nama_lengkap'] }} ({{ $item['nama_pimpinan'] }})
                </option>
            @endforeach
        </select>
        @error('penandatangan_id')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
@endif
             <div class="button-row">
                    <button type="reset" class="btn-reset">Reset</button>
                    <button type="submit" class="btn-submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JS Libraries --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// ========== FUNGSI CEK KELAYAKAN CUTI BESAR ==========
function cekKelayakanCutiBesar() {
    let jenisCuti = $('#jenis_permohonan').val();
    let idPengguna = $('input[name="id_pengguna"]').val();
    
    if (jenisCuti === 'Cuti Besar') {
        console.log('🔍 Mengecek kelayakan Cuti Besar untuk ID:', idPengguna);
        
        $.ajax({
            url: `/cek-cuti-besar/${idPengguna}`,
            type: 'GET',
            success: function(response) {
                console.log('✅ Response Cuti Besar:', response);
                
                if (!response.boleh) {
                    Swal.fire({
                        icon: 'warning',
                        title: '⚠️ Tidak Dapat Mengajukan Cuti Besar',
                        html: `
                            <div style="padding: 15px;">
                                <div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin-bottom: 15px;">
                                    <p style="margin: 0; font-size: 15px; color: #856404; line-height: 1.6;">
                                        <strong>📅 Cuti Besar Terakhir:</strong><br>
                                        ${response.tanggal_terakhir}
                                    </p>
                                </div>
                                
                                <div style="background-color: #f8d7da; padding: 15px; border-radius: 8px; border-left: 4px solid #dc3545; margin-bottom: 15px;">
                                    <p style="margin: 0; font-size: 15px; color: #721c24; line-height: 1.6;">
                                        <strong>⏳ Sisa Waktu Tunggu:</strong><br>
                                        ${response.sisa_tahun} tahun (${response.sisa_bulan} bulan) lagi
                                    </p>
                                </div>
                                
                                <div style="background-color: #e7f3ff; padding: 12px; border-radius: 6px; border-left: 3px solid #2196F3;">
                                    <p style="margin: 0; font-size: 13px; color: #1976D2;">
                                        💡 <strong>Info:</strong> Cuti Besar hanya dapat diambil setiap <strong>5 tahun sekali</strong> dengan durasi maksimal <strong>3 bulan (90 hari)</strong>
                                    </p>
                                </div>
                            </div>
                        `,
                        confirmButtonText: 'Saya Mengerti',
                        confirmButtonColor: '#dc3545',
                        width: '550px',
                        allowOutsideClick: false,
                        customClass: {
                            popup: 'swal-custom-popup'
                        }
                    }).then(() => {
                        // Reset ke Cuti Tahunan
                        $('#jenis_permohonan').val('Cuti Tahunan').trigger('change');
                        console.log('🔄 Reset jenis cuti ke: Cuti Tahunan');
                    });
                } else {
                    console.log('✅ Pegawai boleh mengajukan Cuti Besar');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error cek cuti besar:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mengecek Data',
                    text: 'Terjadi kesalahan saat mengecek kelayakan cuti besar. Silakan coba lagi.',
                    confirmButtonColor: '#d33',
                });
            }
        });
    }
}
// ========== FUNGSI CEK STATUS KEPEGAWAIAN UNTUK CUTI BESAR ==========
function cekStatusKepegawaianCutiBesar() {
    let jenisCuti = $('#jenis_permohonan').val();
    
    if (jenisCuti === 'Cuti Besar') {
        let idPengguna = $('input[name="id_pengguna"]').val();
        
        console.log('🔍 Mengecek status kepegawaian untuk Cuti Besar...');
        
        $.ajax({
            url: `/cek-status-kepegawaian/${idPengguna}`,
            type: 'GET',
            success: function(response) {
                console.log('✅ Response Status Kepegawaian:', response);
                
                if (!response.is_asn) {
                    Swal.fire({
                        icon: 'error',
                        title: '❌ Tidak Dapat Mengajukan Cuti Besar',
                        html: `
                            <div style="padding: 15px;">
                                <div style="background-color: #f8d7da; padding: 15px; border-radius: 8px; border-left: 4px solid #dc3545; margin-bottom: 15px;">
                                    <p style="margin: 0; font-size: 15px; color: #721c24; line-height: 1.6;">
                                        <strong>⚠️ Status Kepegawaian Tidak Memenuhi Syarat</strong>
                                    </p>
                                </div>
                                
                                <div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin-bottom: 15px;">
                                    <p style="margin: 0; font-size: 14px; color: #856404; line-height: 1.6;">
                                        <strong>📋 Status Anda Saat Ini:</strong><br>
                                        ${response.status_kepegawaian || 'Belum diisi'}
                                    </p>
                                </div>
                                
                                <div style="background-color: #e7f3ff; padding: 12px; border-radius: 6px; border-left: 3px solid #2196F3;">
                                    <p style="margin: 0; font-size: 13px; color: #1976D2;">
                                        💡 <strong>Info:</strong> Cuti Besar hanya dapat diajukan oleh pegawai dengan status <strong>"Aparatur Sipil Negara (ASN)"</strong>
                                    </p>
                                </div>
                            </div>
                        `,
                        confirmButtonText: 'Saya Mengerti',
                        confirmButtonColor: '#dc3545',
                        width: '550px',
                        allowOutsideClick: false,
                        customClass: {
                            popup: 'swal-custom-popup'
                        }
                    }).then(() => {
                        // Reset ke Cuti Tahunan
                        $('#jenis_permohonan').val('Cuti Tahunan').trigger('change');
                        console.log('🔄 Reset jenis cuti ke: Cuti Tahunan');
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error cek status kepegawaian:', error);
            }
        });
    }
}

// ========== FUNGSI CEK DURASI CUTI DAN TAMPILKAN MODAL ==========
function cekDurasiCutiDanTampilkanModal() {
    let jenisCuti = $('#jenis_permohonan').val();
    let mulai = $('#tanggal_mulai').val();
    let selesai = $('#tanggal_selesai').val();
    let jumlahHariText = $('#jumlah_hari').val();
    
    let jumlahHari = parseInt(jumlahHariText.replace(/[^0-9]/g, '')) || 0;

    // Cek untuk Cuti Besar, Cuti Alasan Penting, dan Cuti Melahirkan
    if ((jenisCuti === 'Cuti Besar' || jenisCuti === 'Cuti Alasan Penting' || jenisCuti === 'Cuti Melahirkan') && mulai && selesai && jumlahHari > 0) {
        const maxHari = 90;
        
        if (jumlahHari > maxHari) {
            const selisih = jumlahHari - maxHari;
            
            Swal.fire({
                icon: 'warning',
                title: '⚠️ Peringatan Durasi Cuti',
                html: `
                    <div style="padding: 5px;">
                        <p style="margin-bottom: 10px; font-size: 14px;">
                            <strong>${jenisCuti}</strong> memiliki batas maksimal <strong style="color: #dc3545;">3 bulan (90 hari)</strong>
                        </p>
                        
                        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <div style="flex: 1; background-color: #fff3cd; padding: 10px; border-radius: 6px; border-left: 3px solid #ffc107;">
                                <div style="font-size: 12px; color: #856404; margin-bottom: 5px;">Durasi dipilih</div>
                                <div style="font-size: 18px; font-weight: bold; color: #dc3545;">${jumlahHari} hari</div>
                            </div>
                            
                            <div style="flex: 1; background-color: #d1ecf1; padding: 10px; border-radius: 6px; border-left: 3px solid #17a2b8;">
                                <div style="font-size: 12px; color: #0c5460; margin-bottom: 5px;">Batas maksimal</div>
                                <div style="font-size: 18px; font-weight: bold; color: #0c5460;">90 hari</div>
                            </div>
                            
                            <div style="flex: 1; background-color: #f8d7da; padding: 10px; border-radius: 6px; border-left: 3px solid #dc3545;">
                                <div style="font-size: 12px; color: #721c24; margin-bottom: 5px;">Kelebihan</div>
                                <div style="font-size: 18px; font-weight: bold;
                                color: #dc3545;">${selisih} hari</div>
                            </div>
                        </div>
                        
                        <div style="background-color: #f8d7da; padding: 8px 12px; border-radius: 6px; border-left: 3px solid #dc3545;">
                            <p style="margin: 0; color: #721c24; font-size: 13px;">
                                ❌ <strong>Pengajuan tidak dapat diproses</strong> dengan durasi ini.
                            </p>
                        </div>
                    </div>
                `,
                confirmButtonText: 'Saya Mengerti',
                confirmButtonColor: '#dc3545',
                width: '650px',
                allowOutsideClick: false,
                customClass: {
                    popup: 'swal-custom-popup',
                    htmlContainer: 'swal-html-custom'
                }
            });
        }
    }
}

// ========== INISIALISASI AWAL ==========
$(document).ready(function() {
    console.log('✅ jQuery loaded and ready');

    // ✅ PASTIKAN DROPDOWN JENIS CUTI EDITABLE
    $('#jenis_permohonan').prop('disabled', false);
    $('#jenis_permohonan').removeAttr('readonly');
    $('#jenis_permohonan').css({
        'pointer-events': 'auto',
        'background-color': '#fff',
        'cursor': 'pointer'
    });

    // ✅ Inisialisasi Select2 untuk tandatangan_id
    if ($('#tandatangan_id').length) {
        var placeholderTandatangan = $('#tandatangan_id').prev('label').text().includes('Ketua Tim') 
            ? "-- Pilih Ketua Tim Kerja --" 
            : "-- Pilih Kepala/Ketua Kelompok --";
        
        $('#tandatangan_id').select2({
            placeholder: placeholderTandatangan,
            allowClear: true,
            width: '100%'
        });
    }
    
    // ✅ Inisialisasi Select2 untuk penandatangan_id
    if ($('#penandatangan_id').length) {
        var placeholderPenandatangan = $('#penandatangan_id').prev('label').text().includes('Kepala Balai') 
            ? "-- Pilih Kepala Balai --" 
            : "-- Pilih Kepala/Ketua Kelompok --";
        
        $('#penandatangan_id').select2({
            placeholder: placeholderPenandatangan,
            allowClear: true,
            width: '100%'
        });
    }

    // ✅ Event listeners
    $('#tanggal_pengajuan').on('change', validasiTanggalPengajuan);
    
    $('#tanggal_mulai').on('change', function() {
        console.log('Tanggal mulai changed:', $(this).val());
        validasiTanggalPengajuan();
        hitungHari();
    });
    
    $('#tanggal_selesai').on('change', function() {
        console.log('Tanggal selesai changed:', $(this).val());
        hitungHari();
        
        setTimeout(function() {
            cekDurasiCutiDanTampilkanModal();
        }, 500);
    });
    
    // ✅ Event listener untuk jenis cuti (CEK CUTI BESAR)
    $('#jenis_permohonan').on('change', function() {
        console.log('🔄 Jenis cuti changed:', $(this).val());
        
            cekStatusKepegawaianCutiBesar();
        // Cek kelayakan cuti besar
        cekKelayakanCutiBesar();
        
        // Cek durasi
        setTimeout(function() {
            cekDurasiCutiDanTampilkanModal();
        }, 500);
    });
});

// ========== VALIDASI TANGGAL PENGAJUAN ==========
function validasiTanggalPengajuan() {
    let pengajuan = $('#tanggal_pengajuan').val();
    let mulai = $('#tanggal_mulai').val();

    if (pengajuan && mulai) {
        if (new Date(pengajuan) > new Date(mulai)) {
            Swal.fire({
                icon: 'error',
                title: 'Tanggal Pengajuan Salah',
                text: 'Tanggal pengajuan tidak boleh lebih besar dari tanggal mulai cuti!',
                confirmButtonColor: '#d33',
            });
            $('#tanggal_pengajuan').val('');
            return false;
        }
    }
    return true;
}

// ========== HITUNG JUMLAH HARI CUTI ==========
function hitungHari() {
    let mulai = $('#tanggal_mulai').val();
    let selesai = $('#tanggal_selesai').val();
    let jenisCuti = $('#jenis_permohonan').val();

    console.log('=== HITUNG HARI DEBUG ===');
    console.log('Tanggal Mulai:', mulai);
    console.log('Tanggal Selesai:', selesai);
    console.log('Jenis Cuti:', jenisCuti);

    if (mulai && selesai) {
        if (new Date(selesai) < new Date(mulai)) {
            Swal.fire({
                icon: 'error',
                title: 'Tanggal Salah',
                text: 'Maaf, tanggal selesai tidak boleh lebih kecil dari tanggal mulai!',
                confirmButtonColor: '#3085d6',
            });
            $('#jumlah_hari').val('');
            $('#tanggal_selesai').val('');
            return;
        }

        console.log('Mengirim AJAX request ke /hitung-hari-cuti...');
        
        $.ajax({
            url: '/hitung-hari-cuti',
            type: 'GET',
            data: {
                tanggal_mulai: mulai,
                tanggal_selesai: selesai,
                jenis_permohonan: jenisCuti
            },
            success: function(response) {
                console.log('✅ Response dari server:', response);
                $('#jumlah_hari').val(response.jumlah_hari + ' hari');
            },
            error: function(xhr, status, error) {
                console.error('❌ Error AJAX:', error);
                $('#jumlah_hari').val('');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menghitung',
                    text: 'Terjadi kesalahan saat menghitung jumlah hari. Silakan coba lagi.',
                    confirmButtonColor: '#d33',
                });
            }
        });
    } else {
        $('#jumlah_hari').val('');
    }
}

// ========== VALIDASI FORM SEBELUM SUBMIT ==========
// ========== VALIDASI FORM SEBELUM SUBMIT ==========
function validateForm() {
    let pengajuan = $('#tanggal_pengajuan').val();
    let mulai = $('#tanggal_mulai').val();
    let selesai = $('#tanggal_selesai').val();
    let jenisCuti = $('#jenis_permohonan').val();
    let jumlahHariText = $('#jumlah_hari').val();
    let idPengguna = $('input[name="id_pengguna"]').val();
    
    let jumlahHari = parseInt(jumlahHariText.replace(/[^0-9]/g, '')) || 0;
    let tahunPengajuan = new Date(mulai).getFullYear();

    console.log('=== VALIDASI FORM ===');
    console.log('Jenis Cuti:', jenisCuti);
    console.log('Jumlah Hari:', jumlahHari);
    console.log('Tahun Pengajuan:', tahunPengajuan);

    // Validasi tanggal pengajuan vs tanggal mulai
    if (pengajuan && mulai && new Date(pengajuan) > new Date(mulai)) {
        Swal.fire({
            icon: 'error',
            title: 'Validasi Gagal',
            text: 'Tanggal pengajuan tidak boleh lebih besar dari tanggal mulai cuti!',
            confirmButtonColor: '#d33',
        });
        return false;
    }

    // Validasi tanggal selesai vs tanggal mulai
    if (mulai && selesai && new Date(selesai) < new Date(mulai)) {
        Swal.fire({
            icon: 'error',
            title: 'Validasi Gagal',
            text: 'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai!',
            confirmButtonColor: '#d33',
        });
        return false;
    }

    // ========== VALIDASI KHUSUS CUTI BESAR ==========
    if (jenisCuti === 'Cuti Besar') {
        const maxHari = 90;
        
        // 1. Validasi maksimal 3 bulan
        if (jumlahHari > maxHari) {
            const selisih = jumlahHari - maxHari;
            
            Swal.fire({
                icon: 'warning',
                title: '⚠️ Melebihi Batas Maksimal',
                html: `
                    <div style="padding: 5px;">
                        <p style="margin-bottom: 10px; font-size: 14px;">
                            <strong>Cuti Besar</strong> memiliki batas maksimal <strong>3 bulan (90 hari)</strong>
                        </p>
                        
                        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <div style="flex: 1; background-color: #fff3cd; padding: 10px; border-radius: 6px; text-align: center;">
                                <div style="font-size: 12px; color: #856404; margin-bottom: 5px;">Durasi Anda</div>
                                <div style="font-size: 20px; font-weight: bold; color: #dc3545;">${jumlahHari} hari</div>
                            </div>
                            
                            <div style="flex: 1; background-color: #d1ecf1; padding: 10px; border-radius: 6px; text-align: center;">
                                <div style="font-size: 12px; color: #0c5460; margin-bottom: 5px;">Maksimal</div>
                                <div style="font-size: 20px; font-weight: bold; color: #0c5460;">90 hari</div>
                            </div>
                            
                            <div style="flex: 1; background-color: #f8d7da; padding: 10px; border-radius: 6px; text-align: center;">
                                <div style="font-size: 12px; color: #721c24; margin-bottom: 5px;">Kelebihan</div>
                                <div style="font-size: 20px; font-weight: bold; color: #dc3545;">${selisih} hari</div>
                            </div>
                        </div>
                        
                        <p style="margin: 5px 0; color: #6c757d; font-size: 13px; text-align: center;">
                            💡 <em>Silakan kurangi durasi cuti atau hubungi admin</em>
                        </p>
                    </div>
                `,
                confirmButtonText: 'Saya Mengerti',
                confirmButtonColor: '#dc3545',
                width: '600px',
                customClass: {
                    popup: 'swal-custom-popup'
                }
            });
            return false;
        }

        // 2. Validasi frekuensi 5 tahun sekali
        console.log('🔍 Validasi frekuensi 5 tahun untuk Cuti Besar...');
        
        let xhr = new XMLHttpRequest();
        xhr.open('GET', `/cek-cuti-besar/${idPengguna}`, false);
        xhr.send();

        if (xhr.status === 200) {
            let response = JSON.parse(xhr.responseText);
            console.log('Response validasi frekuensi:', response);
            
            if (!response.boleh) {
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak Dapat Mengajukan',
                    html: `
                        <div style="padding: 10px;">
                            <p style="font-size: 15px; margin-bottom: 10px;">${response.pesan}</p>
                            <div style="background-color: #fff3cd; padding: 10px; border-radius: 6px;">
                                <p style="margin: 0; font-size: 13px; color: #856404;">
                                    <strong>Sisa waktu:</strong> ${response.sisa_tahun} tahun (${response.sisa_bulan} bulan)
                                </p>
                            </div>
                        </div>
                    `,
                    confirmButtonColor: '#d33',
                });
                return false;
            }
        }
    }

    // ===== ✅ VALIDASI: CEK APAKAH CUTI TAHUNAN SAAT ADA CUTI BESAR DI TAHUN YANG SAMA =====
if (jenisCuti === 'Cuti Tahunan') {
    console.log('🔍 Mengecek apakah ada Cuti Besar di tahun ini untuk validasi Cuti Tahunan...');
    
    let xhrCekCutiBesar = new XMLHttpRequest();
    xhrCekCutiBesar.open('GET', `/cek-cuti-besar-tahun-ini/${idPengguna}?tahun=${tahunPengajuan}`, false);
    xhrCekCutiBesar.send();

    if (xhrCekCutiBesar.status === 200) {
        let response = JSON.parse(xhrCekCutiBesar.responseText);
        console.log('Response cek cuti besar untuk Cuti Tahunan:', response);
        
        if (response.ada_cuti_besar) {
            Swal.fire({
                icon: 'error',
                title: '❌ Tidak Bisa Mengajukan Cuti Tahunan',
                html: `
                    <div style="padding: 15px;">
                        <div style="background-color: #f8d7da; padding: 15px; border-radius: 8px; border-left: 4px solid #dc3545; margin-bottom: 15px;">
                            <p style="margin: 0; font-size: 15px; color: #721c24; line-height: 1.6;">
                                <strong>⚠️ Anda sudah mengajukan Cuti Besar di tahun ${response.tahun}</strong>
                            </p>
                        </div>
                        
                        <div style="background-color: #fff3cd; padding: 12px; border-radius: 6px; border-left: 3px solid #ffc107;">
                            <p style="margin: 0; font-size: 13px; color: #856404; line-height: 1.5;">
                                📋 <strong>Aturan:</strong><br>
                                Jika sudah mengambil <strong>Cuti Besar</strong> dalam 1 tahun, maka <strong>tidak dapat mengajukan Cuti Tahunan</strong> di tahun yang sama. (Sesuai PP No. 11 Tahun 2017)
                            </p>
                        </div>
                        
                        <div style="background-color: #d4edda; padding: 12px; border-radius: 6px; border-left: 3px solid #28a745; margin-top: 12px;">
                            <p style="margin: 0; font-size: 13px; color: #155724;">
                                ✅ <strong>Alternatif:</strong> Anda masih dapat mengajukan <strong>Cuti Sakit</strong>, <strong>Cuti Melahirkan</strong>, atau <strong>Cuti Alasan Penting</strong> di tahun ini.
                            </p>
                        </div>
                    </div>
                `,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Mengerti',
                width: '550px',
                allowOutsideClick: false,
                customClass: {
                    popup: 'swal-custom-popup'
                }
            });
            return false;
        }
    }
}
    // ========== VALIDASI MAKSIMAL 3 BULAN (CUTI ALASAN PENTING & MELAHIRKAN) ==========
    if (jenisCuti === 'Cuti Alasan Penting' || jenisCuti === 'Cuti Melahirkan') {
        const maxHari = 90;
        
        if (jumlahHari > maxHari) {
            const selisih = jumlahHari - maxHari;
            
            Swal.fire({
                icon: 'warning',
                title: '⚠️ Melebihi Batas Maksimal',
                html: `
                    <div style="padding: 5px;">
                        <p style="margin-bottom: 10px; font-size: 14px;">
                            <strong>${jenisCuti}</strong> memiliki batas maksimal <strong>3 bulan (90 hari)</strong>
                        </p>
                        
                        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <div style="flex: 1; background-color: #fff3cd; padding: 10px; border-radius: 6px; text-align: center;">
                                <div style="font-size: 12px; color: #856404; margin-bottom: 5px;">Durasi Anda</div>
                                <div style="font-size: 20px; font-weight: bold; color: #dc3545;">${jumlahHari} hari</div>
                            </div>
                            
                            <div style="flex: 1; background-color: #d1ecf1; padding: 10px; border-radius: 6px; text-align: center;">
                                <div style="font-size: 12px; color: #0c5460; margin-bottom: 5px;">Maksimal</div>
                                <div style="font-size: 20px; font-weight: bold; color: #0c5460;">90 hari</div>
                            </div>
                            
                            <div style="flex: 1; background-color: #f8d7da; padding: 10px; border-radius: 6px; text-align: center;">
                                <div style="font-size: 12px; color: #721c24; margin-bottom: 5px;">Kelebihan</div>
                                <div style="font-size: 20px; font-weight: bold; color: #dc3545;">${selisih} hari</div>
                            </div>
                        </div>
                        
                        <p style="margin: 5px 0; color: #6c757d; font-size: 13px; text-align: center;">
                            💡 <em>Silakan kurangi durasi cuti atau hubungi admin</em>
                        </p>
                    </div>
                `,
                confirmButtonText: 'Saya Mengerti',
                confirmButtonColor: '#dc3545',
                width: '600px',
                customClass: {
                    popup: 'swal-custom-popup'
                }
            });
            return false;
        }
    }

    // ========== VALIDASI OVERLAP TANGGAL ==========
    if (mulai && selesai) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', `/cek-overlap-cuti/${idPengguna}?mulai=${mulai}&selesai=${selesai}`, false);
        xhr.send();
        
        if (xhr.status === 200) {
            let response = JSON.parse(xhr.responseText);
            if (response.overlap) {
                Swal.fire({
                    icon: 'error',
                    title: 'Tanggal Bertabrakan',
                    text: 'Tanggal cuti yang dipilih bertabrakan dengan pengajuan cuti Anda yang sudah ada!',
                    confirmButtonColor: '#d33',
                });
                return false;
            }
        }
    }

    console.log('✅✅✅ Semua validasi LOLOS, form akan disubmit...');
    return true;
}
</script>

@endsection