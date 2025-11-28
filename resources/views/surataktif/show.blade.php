@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/konfirmasisurataktif.css') }}">

<div class="surat-page">

    <div class="surat-card">
        <div class="surat-box">
            <!-- HEADER -->
            <div class="surat-header">
                <a href="{{ route('surataktif.index') }}" class="surat-back">&larr;</a>
                <div class="surat-title">SURAT KETERANGAN AKTIF</div>
            </div>

            <div class="separator"></div>

            <!-- FORM -->
            <form class="surat-form" 
                  method="POST" 
                  action="{{ route('admin.surataktif.approve', $permintaan->id_permintaan) }}" 
                  enctype="multipart/form-data"
                  id="approveForm">
                @csrf
                
                <!-- ✅ HIDDEN INPUTS -->
                <input type="hidden" name="confirm_change" id="confirm_change" value="">
                <input type="hidden" name="temp_ttd_path" id="temp_ttd_path" value="{{ session('temp_ttd_path') }}">

                <!-- Nomor Surat: FULL-WIDTH -->
                <div class="row-1">
                    <div class="field">
                        <label class="surat-label">Nomor Surat</label>
                        <input type="text" name="nomor_surat" class="surat-input"
                               value="{{ old('nomor_surat', $permintaan->nomor_surat ?? '') }}"
                               maxlength="30"
                               required>
                        @error('nomor_surat')
                            <small style="color: red; display: block; margin-top: 5px;">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

          <!-- Tanggal Terbit -->
<div class="row-1">
    <div class="field">
        <label class="surat-label">Tanggal Terbit</label>
        <input type="date" name="tanggal_terbit" class="surat-input"
               value="{{ old('tanggal_terbit', now()->toDateString()) }}"
               disabled
               required>
        <input type="hidden" name="tanggal_terbit" value="{{ old('tanggal_terbit', now()->toDateString()) }}">
    </div>
</div>
                <!-- Nama Kepala + NIP -->
                <div class="row-2">
                    <div class="field">
                        <label class="surat-label">Nama Kepala</label>
                        <select class="surat-input" disabled>
                            <option>
                                {{ $permintaan->penandatangan->nama_lengkap ?? $permintaan->nama_kepala ?? '-' }}
                            </option>
                        </select>
                        <input type="hidden" name="penandatangan_id" value="{{ $permintaan->penandatangan_id }}">
                    </div>

                    <div class="field">
                        <label class="surat-label">NIP Kepala</label>
                        <input type="text" class="surat-input" readonly
                               value="{{ $permintaan->penandatangan->nip ?? $permintaan->nip_kepala ?? '' }}">
                    </div>
                </div>

                <!-- Pangkat/Golongan + Jabatan Kepala -->
                <div class="row-2">
                    <div class="field">
                        <label class="surat-label">Pangkat / Golongan</label>
                        @php
                            $pgKepala = \App\Models\PangkatGolonganRuang::find($permintaan->penandatangan->id_pangkat_golongan_ruang ?? null);
                        @endphp
                        <input type="text" class="surat-input" readonly
                               value="{{ $pgKepala ? ($pgKepala->pangkat . ' / ' . $pgKepala->golongan . ' (' . $pgKepala->ruang . ')') : '-' }}">
                    </div>

                    <div class="field">
                        <label class="surat-label">Jabatan</label>
                        <input type="text" class="surat-input" readonly
                               value="{{ $permintaan->penandatangan->jabatan->nama_jabatan ?? $permintaan->jabatan_kepala ?? '' }}">
                    </div>
                </div>

                <!-- Nama Pengaju + NIP -->
                <div class="row-2">
                    <div class="field">
                        <label class="surat-label">Nama</label>
                        <input type="text" class="surat-input" readonly
                               value="{{ $permintaan->pengguna->nama_lengkap ?? $permintaan->nama ?? '' }}">
                    </div>

                    <div class="field">
                        <label class="surat-label">NIP</label>
                        <input type="text" class="surat-input" readonly
                               value="{{ $permintaan->pengguna->nip ?? $permintaan->nip ?? '' }}">
                    </div>
                </div>

                <!-- Pangkat/Golongan + Jabatan Pengaju -->
                <div class="row-2">
                    <div class="field">
                        <label class="surat-label">Pangkat / Golongan</label>
                        <input type="text" class="surat-input" readonly
                               value="{{ $permintaan->pengguna->pangkatGolongan->pangkat ?? '-' }} / {{ $permintaan->pengguna->pangkatGolongan->golongan ?? '-' }} ({{ $permintaan->pengguna->pangkatGolongan->ruang ?? '-' }})">
                    </div>

                    <div class="field">
                        <label class="surat-label">Jabatan</label>
                        <input type="text" class="surat-input" readonly
                               value="{{ $permintaan->pengguna->jabatan->nama_jabatan ?? $permintaan->jabatan->nama_jabatan ?? '-' }}">
                    </div>
                </div>

 <!-- ✅ TAMPILAN TTD TANPA UPLOAD -->
<div class="row-1">
    <div class="field">
        <label class="surat-label">TTD Kepala</label>

        @if(isset($tandaTangan))
            <div style="margin-bottom: 10px; border: 2px solid #28a745; padding: 10px; border-radius: 8px; background: #f0f9ff;">
                <p style="margin: 0 0 8px 0; color: #155724; font-weight: 600;">
                    ✅ TTD Sudah Tersedia
                </p>
                <img src="{{ $tandaTangan }}" 
                     alt="TTD Penandatangan" 
                     style="max-width: 200px; max-height: 100px; border: 1px solid #ddd; border-radius: 4px; background: white; padding: 5px;">
            </div>
        @else
            <div style="padding: 10px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px; margin-bottom: 10px;">
                <p style="margin: 0; color: #856404;">
                    ⚠️ Penandatangan belum memiliki TTD.
                </p>
            </div>
        @endif
    </div>
</div>
                <!-- Status Kepegawaian Pengaju -->
<div class="row-1">
    <div class="field">
        <label class="surat-label">Status Kepegawaian</label>
        <input type="text" class="surat-input" readonly
               value="{{ $permintaan->pengguna->status_kepegawaian ?? '-' }}">
    
        
        @if(!$permintaan->pengguna->status_kepegawaian)
            <small style="display: block; margin-top: 5px; color: #dc3545;">
                ⚠️ Status kepegawaian belum diisi untuk pengguna ini.
            </small>
        @endif
    </div>
</div>

                <!-- ACTIONS -->
                <div class="form-actions">
                    <button type="submit" class="btn-simpan">SIMPAN</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ✅ MODAL KONFIRMASI TTD -->
@if(session('confirm_ttd'))
<div id="ttdModal">
    <div class="ttd-modal-box">
        <h3 class="ttd-modal-title">
            ⚠️ Perubahan TTD Terdeteksi
        </h3>
        
        <p class="ttd-modal-message">
            TTD yang Anda upload berbeda dengan TTD sebelumnya.<br>
            Apakah Anda ingin mengubah TTD penandatangan?
        </p>

        <div class="ttd-comparison-wrapper">
            <div class="ttd-item">
                <p class="ttd-label">TTD Lama:</p>
                <img src="data:image/png;base64,{{ session('old_ttd') }}" 
                     alt="TTD Lama"
                     class="ttd-preview-image">
            </div>
            <div class="ttd-item">
                <p class="ttd-label">TTD Baru:</p>
                <img src="data:image/png;base64,{{ session('new_ttd') }}" 
                     alt="TTD Baru"
                     class="ttd-preview-image">
            </div>
        </div>

        <div class="ttd-modal-buttons">
            <button onclick="submitWithConfirm('yes')" class="ttd-btn ttd-btn-yes">
                Ya, Ubah TTD
            </button>
            <button onclick="submitWithConfirm('no')" class="ttd-btn ttd-btn-no">
                Tidak, Pakai TTD Lama
            </button>
        </div>
    </div>
</div>
@endif

<script>
// ✅ VALIDASI NOMOR SURAT SEBELUM SUBMIT
document.getElementById('approveForm').addEventListener('submit', async function(e) {
    // Jika ada modal TTD konfirmasi yang sedang aktif, skip validasi nomor surat
    if (document.getElementById('ttdModal')) {
        return true;
    }
    
    e.preventDefault();
    
    const nomorSurat = document.querySelector('input[name="nomor_surat"]').value.trim();
    const idPermintaan = {{ $permintaan->id_permintaan }};
    
    if (!nomorSurat) {
        showModalNomorSuratKosong();
        return false;
    }
    
    try {
        const response = await fetch('{{ route("check.nomor.surat") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                nomor_surat: nomorSurat,
                id_permintaan: idPermintaan
            })
        });
        
        const data = await response.json();
        
        if (data.exists) {
            // Tampilkan modal notifikasi
            showModalNomorSuratDuplikat();
        } else {
            // Lanjutkan submit form
            this.submit();
        }
    } catch (error) {
        console.error('Error checking nomor surat:', error);
        // Jika ada error, tetap lanjutkan submit (fail-safe)
        this.submit();
    }
});

// ✅ FUNGSI MODAL NOMOR SURAT DUPLIKAT
function showModalNomorSuratDuplikat() {
    const modalHTML = `
        <div id="nomorSuratModal" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease-in-out;
        ">
            <div style="
                background: white;
                padding: 35px;
                border-radius: 16px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                max-width: 450px;
                width: 90%;
                text-align: center;
                animation: slideIn 0.3s ease-out;
            ">
                <div style="
                    font-size: 64px;
                    margin-bottom: 20px;
                    animation: bounce 0.6s ease-in-out;
                ">⚠️</div>
                
                <h3 style="
                    color: #dc3545;
                    margin-bottom: 15px;
                    font-size: 24px;
                    font-weight: 700;
                ">Nomor Surat Sudah Digunakan</h3>
                
                <p style="
                    color: #555;
                    margin-bottom: 30px;
                    line-height: 1.8;
                    font-size: 16px;
                ">
                    Nomor surat telah digunakan.<br>
                    <strong>Tolong gunakan nomor surat lain.</strong>
                </p>
                
                <button onclick="closeModalNomorSurat()" style="
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border: none;
                    padding: 14px 40px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 16px;
                    font-weight: 700;
                    transition: all 0.3s;
                    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
                " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.6)'" 
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.4)'">
                    OK, SAYA MENGERTI
                </button>
            </div>
        </div>
        
        <style>
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideIn {
                from { 
                    transform: translateY(-50px); 
                    opacity: 0; 
                }
                to { 
                    transform: translateY(0); 
                    opacity: 1; 
                }
            }
            @keyframes bounce {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }
        </style>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Focus ke input nomor surat setelah modal ditutup
    setTimeout(() => {
        const nomorSuratInput = document.querySelector('input[name="nomor_surat"]');
        if (nomorSuratInput) {
            nomorSuratInput.focus();
            nomorSuratInput.select();
        }
    }, 100);
}

// ✅ FUNGSI MODAL NOMOR SURAT KOSONG
function showModalNomorSuratKosong() {
    const modalHTML = `
        <div id="nomorSuratModal" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        ">
            <div style="
                background: white;
                padding: 35px;
                border-radius: 16px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                max-width: 450px;
                width: 90%;
                text-align: center;
            ">
                <div style="font-size: 64px; margin-bottom: 20px;">❌</div>
                
                <h3 style="
                    color: #dc3545;
                    margin-bottom: 15px;
                    font-size: 24px;
                    font-weight: 700;
                ">Nomor Surat Kosong</h3>
                
                <p style="
                    color: #555;
                    margin-bottom: 30px;
                    line-height: 1.8;
                    font-size: 16px;
                ">
                    Nomor surat harus diisi.<br>
                    <strong>Silakan isi nomor surat terlebih dahulu.</strong>
                </p>
                
                <button onclick="closeModalNomorSurat()" style="
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border: none;
                    padding: 14px 40px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 16px;
                    font-weight: 700;
                    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
                ">
                    OK
                </button>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

// ✅ TUTUP MODAL
function closeModalNomorSurat() {
    const modal = document.getElementById('nomorSuratModal');
    if (modal) {
        modal.remove();
    }
    // Focus ke input nomor surat
    const nomorSuratInput = document.querySelector('input[name="nomor_surat"]');
    if (nomorSuratInput) {
        nomorSuratInput.focus();
        nomorSuratInput.select();
    }
}

// ✅ TOGGLE TTD UPLOAD
function toggleTtdUpload() {
    const checkbox = document.getElementById('change_ttd');
    const uploadSection = document.getElementById('ttd_upload_section');
    
    if (checkbox.checked) {
        uploadSection.style.display = 'block';
    } else {
        uploadSection.style.display = 'none';
        const fileInput = document.getElementById('file_ttd');
        if (fileInput) {
            fileInput.value = '';
        }
    }
}

// ✅ SUBMIT DENGAN KONFIRMASI TTD
function submitWithConfirm(choice) {
    console.log('🔘 User pilih:', choice);
    
    const form = document.getElementById('approveForm');
    const confirmInput = document.getElementById('confirm_change');
    const tempPathInput = document.getElementById('temp_ttd_path');
    
    confirmInput.value = choice;
    
    console.log('📤 Data yang akan dikirim:', {
        confirm_change: confirmInput.value,
        temp_ttd_path: tempPathInput.value
    });
    
    form.submit();
}
</script>

@endsection