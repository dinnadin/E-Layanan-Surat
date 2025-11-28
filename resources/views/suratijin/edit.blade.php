@extends('layouts.appKepala')

@section('content')
<link rel="stylesheet" href="{{ asset('css/konfirmasisuratijin.css') }}">

<div class="surat-page">
    <div class="surat-card">
        <div class="surat-box">
            <!-- HEADER -->
            <div class="surat-header">
                <a href="{{ route('suratijin.index') }}" class="surat-back">&larr;</a>
                <div class="surat-title">KONFIRMASI SURAT IJIN KELUAR</div>
            </div>

            <div class="separator"></div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <!-- FORM REVIEW -->
            <div class="surat-form">
                <!-- Nama & NIP Pengaju -->
                <div class="row-2">
                    <div class="field">
                        <label class="surat-label">Nama</label>
                        <input type="text" class="surat-input" 
                               value="{{ $permintaan->pengguna->nama_lengkap ?? '-' }}" readonly>
                    </div>
                    <div class="field">
                        <label class="surat-label">NIP</label>
                        <input type="text" class="surat-input" 
                               value="{{ $permintaan->pengguna->nip ?? '-' }}" readonly>
                    </div>
                </div>

                <!-- Jabatan & Unit Kerja -->
                <div class="row-2">
                    <div class="field">
                        <label class="surat-label">Jabatan</label>
                        <input type="text" class="surat-input" 
                               value="{{ $permintaan->pengguna->jabatan->nama_jabatan ?? '-' }}" readonly>
                    </div>
                    <div class="field">
                        <label class="surat-label">Unit Kerja</label>
                        <input type="text" class="surat-input" 
                               value="{{ $permintaan->pengguna->unitKerja->nama_unit_kerja ?? '-' }}" readonly>
                    </div>
                </div>

                <!-- Tanggal & Mulai Jam -->
                <div class="row-2">
                    <div class="field">
                        <label class="surat-label">Tanggal</label>
                        <input type="date" class="surat-input" 
                               value="{{ $permintaan->mulai_tanggal }}" readonly>
                    </div>
                    <div class="field">
                        <label class="surat-label">Mulai Jam</label>
                        <input type="time" class="surat-input"
                               value="{{ \Carbon\Carbon::parse($permintaan->mulai_jam)->format('H:i') }}" readonly>
                    </div>
                </div>

                <!-- Selesai Jam & Jenis Alasan -->
                <div class="row-2">
                    <div class="field">
                        <label class="surat-label">Selesai Jam</label>
                        <input type="time" class="surat-input"
                               value="{{ \Carbon\Carbon::parse($permintaan->selesai_jam)->format('H:i') }}" readonly>
                    </div>
                    <div class="field">
                        <label class="surat-label">Jenis Alasan</label>
                        <input type="text" class="surat-input" 
                               value="{{ $permintaan->jenis_alasan }}" readonly>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="row-1">
                    <div class="field">
                        <label class="surat-label">Deskripsi Alasan</label>
                        <textarea class="surat-input" readonly>{{ $permintaan->deskripsi_alasan }}</textarea>
                    </div>
                </div>

                <!-- Info Pimpinan -->
                <div class="row-2">
                    <div class="field">
                        <label class="surat-label">Nama Pimpinan</label>
                        <input type="text" class="surat-input"
                               value="{{ $permintaan->penandatangan->nama_lengkap ?? '-' }}" readonly>
                    </div>
                    <div class="field">
                        <label class="surat-label">NIP Pimpinan</label>
                        <input type="text" class="surat-input"
                               value="{{ $permintaan->penandatangan->nip ?? '-' }}" readonly>
                    </div>
                </div>

                <!-- TTD Info -->
                <div class="row-1">
                    <div class="field">
                        <label class="surat-label">Status TTD</label>
                        @if(Auth::user()->tanda_tangan)
                            <div class="alert alert-success">
                                ✅ TTD Anda sudah tersedia di sistem
                                <img src="{{ asset('storage/' . Auth::user()->tanda_tangan) }}" 
                                     alt="TTD" style="max-width: 200px; margin-top: 10px; border: 1px solid #ddd; padding: 5px;">
                            </div>
                        @else
                            <div class="alert alert-warning">
                                ⚠️ TTD Anda belum tersedia. Silakan upload TTD di menu <a href="{{ route('pengaturan') }}">Pengaturan</a>
                            </div>
                        @endif
                    </div>
                </div>
                <!-- Modal Setuju -->
<div id="modalSetuju" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%;">
        <h3 style="margin-bottom: 20px; color: #28a745;">✓ Konfirmasi Persetujuan</h3>
        
        <p style="margin-bottom: 25px; font-size: 16px; line-height: 1.6;">
            Apakah Anda yakin ingin <strong>menyetujui</strong> surat ijin ini?
        </p>
        
        <form action="{{ route('kepala.permintaan.setuju', $permintaan->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- ✅ TAMBAHAN: Field Catatan Opsional -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">
                    Catatan (Opsional):
                </label>
                <textarea name="catatan" 
                          class="surat-input" 
                          rows="4" 
                          placeholder="Contoh: Harap segera kembali setelah urusan selesai..."
                          style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; resize: vertical;"></textarea>
                <small style="color: #666; font-size: 13px;">
                    *Catatan ini bersifat opsional dan akan disimpan bersama surat ijin.
                </small>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" 
                        onclick="hideModalSetuju()"
                        style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                    Batal
                </button>
                <button type="submit" 
                        style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                    Ya, Setujui
                </button>
            </div>
        </form>
    </div>
</div>

                <!-- Actions -->
                <div class="form-actions" style="display: flex; gap: 15px; justify-content: center;">
                    <button type="button" class="btn-setuju" 
                            onclick="showModalSetuju()"
                            style="background: #28a745; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                        ✓ SETUJU
                    </button>

                    <button type="button" class="btn-tolak" 
                            onclick="showModalTolak()"
                            style="background: #dc3545; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                        ✗ TOLAK
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Setuju -->
<div id="modalSetuju" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%;">
        <h3 style="margin-bottom: 20px; color: #28a745;">✓ Konfirmasi Persetujuan</h3>
        
        <p style="margin-bottom: 25px; font-size: 16px; line-height: 1.6;">
            Apakah Anda yakin ingin <strong>menyetujui</strong> surat ijin ini?
        </p>
        
        <form action="{{ route('kepala.permintaan.setuju', $permintaan->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" 
                        onclick="hideModalSetuju()"
                        style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                    Batal
                </button>
                <button type="submit" 
                        style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                    Ya, Setujui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tolak -->
<div id="modalTolak" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%;">
        <h3 style="margin-bottom: 20px; color: #dc3545;">⚠️ Alasan Penolakan</h3>
        
        <form action="{{ route('kepala.permintaan.tolak', $permintaan->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Masukkan alasan penolakan:</label>
                <textarea name="alasan_penolakan" 
                          class="surat-input" 
                          rows="5" 
                          required 
                          placeholder="Contoh: Data tidak lengkap, waktu tidak sesuai jadwal, dll..."
                          style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" 
                        onclick="hideModalTolak()"
                        style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                    Batal
                </button>
                <button type="submit" 
                        style="background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                    Tolak Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showModalSetuju() {
    document.getElementById('modalSetuju').style.display = 'flex';
}

function hideModalSetuju() {
    document.getElementById('modalSetuju').style.display = 'none';
}

function showModalTolak() {
    document.getElementById('modalTolak').style.display = 'flex';
}

function hideModalTolak() {
    document.getElementById('modalTolak').style.display = 'none';
}


// Close modal when clicking outside
document.getElementById('modalSetuju').addEventListener('click', function(e) {
    if (e.target === this) {
        hideModalSetuju();
    }
});
  // ✅ TAMPILKAN MODAL ERROR JIKA TANDA TANGAN DUPLIKAT
    @if(session('error_modal'))
        Swal.fire({
            icon: 'error',
            title: '{{ session("error_modal.title") }}',
            html: '{!! session("error_modal.message") !!}<br><small style="color:#666;">{{ session("error_modal.submessage") }}</small>',
            confirmButtonText: 'OK, Saya Mengerti',
            confirmButtonColor: '#dc2626',
            allowOutsideClick: false
        });
    @endif

document.getElementById('modalTolak').addEventListener('click', function(e) {
    if (e.target === this) {
        hideModalTolak();
    }
});

</script>

@endsection