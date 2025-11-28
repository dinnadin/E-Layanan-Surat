@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/tambahdatapegawai.css') }}">

<div class="form-container">
    <div class="form-box">
        <!-- Judul dengan panah kembali -->
        <div class="title-row">
            <a href="javascript:history.back()" class="back-link" aria-label="Kembali">&larr;</a>
            <h1 style="margin:0; padding:0; border-bottom:none;">TAMBAH DATA PEGAWAI</h1>
        </div>

        @if ($errors->any())
            <div style="background:#fee2e2; color:#b91c1c; padding:10px; border-radius:5px; margin-bottom:15px;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 🔥 TAMBAHAN: Notifikasi Error & Success --}}
        @if(session('error'))
            <div style="background:#fee2e2; color:#b91c1c; padding:10px; border-radius:5px; margin-bottom:15px;">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div style="background:#d1fae5; color:#065f46; padding:10px; border-radius:5px; margin-bottom:15px;">
                {{ session('success') }}
            </div>
        @endif

        {{-- Tambahkan autocomplete=off agar browser tidak autofill --}}
        <form action="{{ route('data.pegawai.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required>
                </div>

                {{-- NIP --}}
                <div class="form-group">
                    <label>NIP</label>
                    <input type="text"
                           id="nip"
                           name="nip"
                           value="{{ old('nip') }}"
                           autocomplete="new-nip"
                           required
                           maxlength="16"
                           pattern="\d{1,16}"
                           title="Hanya angka, maksimal 16 digit"
                           readonly
                           onfocus="this.removeAttribute('readonly');">
                </div>

                {{-- Password --}}
                <div class="form-group">
                    <label>Password</label>
                    <input type="password"
                           id="password"
                           name="password"
                           value="{{ old('password') }}"
                           autocomplete="new-password"
                           required
                           maxlength="16"
                           pattern="\d{1,16}"
                           title="Hanya angka, maksimal 16 digit"
                           readonly
                           onfocus="this.removeAttribute('readonly');">
                </div>

                {{-- 🔥 TANGGAL LAHIR --}}
                <div class="form-group">
                    <label>Tanggal Lahir <span style="color:#999; font-size:0.85em;"></span></label>
                    <input type="date" 
                           id="tanggal_lahir"
                           name="tanggal_lahir" 
                           value="{{ old('tanggal_lahir') }}"
                           max="{{ date('Y-m-d') }}">
                    @error('tanggal_lahir')
                        <small style="color:#b91c1c;">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Pangkat/Golongan Ruang</label>
                    <select name="id_pangkat_golongan_ruang" required>
                        <option value="">-- Pilih Pangkat/Golongan --</option>
                        @foreach($pangkats as $pangkat)
                            <option value="{{ $pangkat->id_pangkat }}"
                                {{ old('id_pangkat_golongan_ruang') == $pangkat->id_pangkat ? 'selected' : '' }}>
                                {{ $pangkat->pangkat }} - {{ $pangkat->golongan }} ({{ $pangkat->ruang }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Jabatan</label>
                    <select name="id_jabatan" required>
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($jabatans as $jabatan)
                            <option value="{{ $jabatan->id_jabatan }}"
                                {{ old('id_jabatan') == $jabatan->id_jabatan ? 'selected' : '' }}>
                                {{ $jabatan->nama_jabatan }}
                            </option>
                        @endforeach
                    </select>
                </div>
<div class="form-group">
    <label>Unit Kerja</label>
    <select name="id_unit_kerja" required>
        <option value="">-- Pilih Unit Kerja --</option>
        @foreach($unitKerjas as $unit)
            <option value="{{ $unit->id_unit_kerja }}"
                {{ old('id_unit_kerja') == $unit->id_unit_kerja ? 'selected' : '' }}>
                {{ $unit->nama_unit_kerja }}
                @if($unit->sub_unit_kerja)
                    ({{ $unit->sub_unit_kerja }})
                @endif
            </option>
        @endforeach
    </select>
</div>

                {{-- 🔥 PIMPINAN (OPSIONAL - BARU) --}}
                <div class="form-group">
                    <label>Pimpinan <span style="color:#999; font-size:0.85em;">(Opsional)</span></label>
                    <select name="id_pimpinan">
                        <option value="">-- Pilih Pimpinan --</option>
                        @foreach($dataPimpinan as $pimpinan)
                            <option value="{{ $pimpinan->id_pimpinan }}"
                                {{ old('id_pimpinan') == $pimpinan->id_pimpinan ? 'selected' : '' }}>
                                {{ $pimpinan->nama_pimpinan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Tanggal Masuk</label>
                    <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk') }}" required>
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="role" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="Kepala" {{ old('role') == 'Kepala' ? 'selected' : '' }}>Kepala</option>
                        <option value="pegawai" {{ old('role') == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                    </select>
                </div>

                {{-- ✅ STATUS KEPEGAWAIAN (OPSIONAL - BARU) --}}
                <div class="form-group">
                    <label>Status Kepegawaian <span style="color:#999; font-size:0.85em;">(Opsional)</span></label>
                    <select name="status_kepegawaian">
                        <option value="">-- Pilih Status Kepegawaian --</option>
                        <option value="Aparatur Sipil Negara" {{ old('status_kepegawaian') == 'Aparatur Sipil Negara' ? 'selected' : '' }}>Aparatur Sipil Negara</option>
                        <option value="Tenaga Harian Lepas" {{ old('status_kepegawaian') == 'Tenaga Harian Lepas' ? 'selected' : '' }}>Tenaga Harian Lepas</option>
                        <option value="Pegawai Pemerintah dengan Perjanjian Kerja" {{ old('status_kepegawaian') == 'Pegawai Pemerintah dengan Perjanjian Kerja' ? 'selected' : '' }}>Pegawai Pemerintah dengan Perjanjian Kerja</option>
                    </select>
                    <small style="color:#666; font-size:0.85em;">Status ini akan digunakan untuk Surat Keterangan Aktif</small>
                </div>
            </div>

            {{-- ✅ TANDA TANGAN (OPSIONAL) --}}
            <div class="form-group">
                <label>Tanda Tangan <span style="color:#999; font-size:0.85em;">(Opsional)</span></label>
                <input type="file" 
                       name="tanda_tangan" 
                       id="tanda_tangan"
                       accept="image/jpeg,image/png,image/jpg"
                       onchange="previewTandaTangan(event)">
                <small style="color:#666; font-size:0.85em;">Format: JPG, JPEG, PNG. Maksimal 2MB</small>
                
                {{-- Preview Tanda Tangan --}}
                <div id="preview-ttd" style="margin-top:10px; display:none;">
                    <img id="preview-ttd-img" src="" alt="Preview TTD" style="max-width:200px; max-height:100px; border:1px solid #ddd; padding:5px; border-radius:5px;">
                </div>
                
                @error('tanda_tangan')
                    <small style="color:#b91c1c;">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">SIMPAN</button>
            </div>
        </form>
    </div>
</div>

{{-- Script untuk validasi dan otomatis isi password --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nipInput = document.getElementById('nip');
    const passwordInput = document.getElementById('password');
    const roleSelect = document.getElementById('role');
    const tteField = document.getElementById('tte-field');

    // Hanya izinkan angka & maksimal 16 digit
    function filterNumeric(input) {
        input.value = input.value.replace(/\D/g, '').slice(0, 16);
    }

    nipInput.addEventListener('input', function() {
        filterNumeric(this);
        passwordInput.value = this.value; // otomatis isi password sama seperti NIP
    });

    passwordInput.addEventListener('input', function() {
        filterNumeric(this);
    });

    if (roleSelect && tteField) {
        roleSelect.addEventListener('change', function() {
            tteField.style.display = (this.value === 'Kepala') ? 'block' : 'none';
        });
    }
});

function previewTandaTangan(event) {
    const file = event.target.files[0];
    const previewContainer = document.getElementById('preview-ttd');
    const previewImg = document.getElementById('preview-ttd-img');
    
    if (file) {
        // Validasi ukuran file (max 2MB)
        if (file.size > 2048000) {
            alert('Ukuran file terlalu besar! Maksimal 2MB');
            event.target.value = '';
            previewContainer.style.display = 'none';
            return;
        }
        
        // Validasi tipe file
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung! Gunakan JPG, JPEG, atau PNG');
            event.target.value = '';
            previewContainer.style.display = 'none';
            return;
        }
        
        // Tampilkan preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewContainer.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        previewContainer.style.display = 'none';
    }
}
</script>
@endsection