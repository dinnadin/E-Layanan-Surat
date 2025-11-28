@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/editdatapegawai.css') }}">

<div class="form-container">
    <div class="form-box">
     <div class="title-row">
    <a href="javascript:history.back()" class="back-link">&larr;</a>
    <h1>EDIT DATA PEGAWAI</h1>
</div>

{{-- ✅ Notifikasi sukses / error --}}
@if (session('success'))
    <div style="background:#dcfce7; color:#166534; padding:10px; border-radius:5px; margin-bottom:15px;">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div style="background:#fee2e2; color:#b91c1c; padding:10px; border-radius:5px; margin-bottom:15px;">
        {{ session('error') }}
    </div>
@endif

        @if ($errors->any())
            <div style="background:#fee2e2; color:#b91c1c; padding:10px; border-radius:5px; margin-bottom:15px;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('data.pegawai.update', $pengguna->id_pengguna) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $pengguna->nama_lengkap) }}" required>
                </div>

                <div class="form-group">
                    <label>NIP</label>
                    <input type="text" name="nip" value="{{ old('nip', $pengguna->nip) }}" required maxlength="18">
                </div>

                {{-- TANGGAL LAHIR --}}
                <div class="form-group">
                    <label>Tanggal Lahir <span style="color:#999; font-size:0.85em;">(Opsional)</span></label>
                    <input type="date" 
                           id="tanggal_lahir"
                           name="tanggal_lahir" 
                           value="{{ old('tanggal_lahir', $pengguna->tanggal_lahir ? $pengguna->tanggal_lahir->format('Y-m-d') : '') }}"
                           max="{{ date('Y-m-d') }}">
                    @error('tanggal_lahir')
                        <small style="color:#b91c1c;">{{ $message }}</small>
                    @enderror
                    
                    {{-- Tampilkan umur jika tanggal lahir sudah ada --}}
                    @if($pengguna->tanggal_lahir)
                        <small style="color:#059669; display:block; margin-top:5px;">
                            <i class="fas fa-info-circle"></i> 
                            Umur saat ini: <strong>{{ $pengguna->umur }}</strong>
                        </small>
                     <small style="color:#059669;">
    Pensiun anda kurang: <strong>{{ $pengguna->sisa_pensiun }} lagi</strong>
</small>

                    @endif
                </div>

                {{-- ✅ TANGGAL MASUK (BISA DIEDIT) --}}
                <div class="form-group">
                    <label>Tanggal Masuk <span style="color:#999; font-size:0.85em;">(Opsional)</span></label>
                    <input type="date" 
                           id="tanggal_masuk"
                           name="tanggal_masuk" 
                           value="{{ old('tanggal_masuk', $pengguna->tanggal_masuk ? $pengguna->tanggal_masuk->format('Y-m-d') : '') }}"
                           max="{{ date('Y-m-d') }}">
                    @error('tanggal_masuk')
                        <small style="color:#b91c1c;">{{ $message }}</small>
                    @enderror
                    
                    {{-- Tampilkan masa kerja jika tanggal masuk sudah ada --}}
                    @if($pengguna->tanggal_masuk)
                        <small style="color:#059669; display:block; margin-top:5px;">
                            <i class="fas fa-briefcase"></i> 
                            Masa kerja: <strong>{{ $pengguna->masa_kerja_lengkap }}</strong>
                        </small>
                    @endif
                </div>

                <div class="form-group">
                    <label>Pangkat/Golongan</label>
                    <select name="id_pangkat_golongan_ruang" required>
                        @foreach($pangkats as $pangkat)
                            <option value="{{ $pangkat->id_pangkat }}"
                                {{ old('id_pangkat_golongan_ruang', $pengguna->id_pangkat_golongan_ruang) == $pangkat->id_pangkat ? 'selected' : '' }}>
                                {{ $pangkat->pangkat }} - {{ $pangkat->golongan }} ({{ $pangkat->ruang }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Jabatan</label>
                    <select name="id_jabatan" required>
                        @foreach($jabatans as $jabatan)
                            <option value="{{ $jabatan->id_jabatan }}"
                                {{ old('id_jabatan', $pengguna->id_jabatan) == $jabatan->id_jabatan ? 'selected' : '' }}>
                                {{ $jabatan->nama_jabatan }}
                            </option>
                        @endforeach
                    </select>
                </div>

           <div class="form-group">
    <label>Unit Kerja</label>
    <select name="id_unit_kerja" required>
        @foreach($unitKerjas as $unit)
            <option value="{{ $unit->id_unit_kerja }}"
                {{ old('id_unit_kerja', $pengguna->id_unit_kerja) == $unit->id_unit_kerja ? 'selected' : '' }}>
                {{ $unit->nama_unit_kerja }}
                @if($unit->sub_unit_kerja)
                    ({{ $unit->sub_unit_kerja }})
                @endif
            </option>
        @endforeach
    </select>
</div>

                {{-- ✅ PIMPINAN --}}
                <div class="form-group">
                    <label>Pimpinan <span style="color:#999; font-size:0.85em;">(Opsional)</span></label>
                    <select name="id_pimpinan">
                        <option value="">- Tidak Ada Pimpinan -</option>
                        @foreach($pimpinans as $pimpinan)
                            <option value="{{ $pimpinan->id_pimpinan }}"
                                {{ old('id_pimpinan', $pengguna->id_pimpinan) == $pimpinan->id_pimpinan ? 'selected' : '' }}>
                                {{ $pimpinan->nama_pimpinan }} - {{ $pimpinan->jabatan_pimpinan }}
                            </option>
                        @endforeach
                    </select>
                    
                    {{-- Tampilkan pimpinan saat ini --}}
                    @if($pengguna->pimpinan)
                        <small style="color:#059669; display:block; margin-top:5px;">
                            <i class="fas fa-user-tie"></i> 
                            Pimpinan saat ini: <strong>{{ $pengguna->pimpinan->nama_pimpinan }}</strong>
                        </small>
                        
                    @endif
                </div>

                <!-- Dropdown Role --> 
                <div class="form-group"> 
                    <label>Role</label> 
                    <select name="role" required> 
                        <option value="Kepala" {{ old('role', $pengguna->role) == 'Kepala' ? 'selected' : '' }}>Kepala</option> 
                        <option value="Admin" {{ old('role', $pengguna->role) == 'Admin' ? 'selected' : '' }}>Admin</option> 
                        <option value="Pegawai" {{ old('role', $pengguna->role) == 'Pegawai' ? 'selected' : '' }}>Pegawai</option> 
                    </select> 
                </div>

                {{-- ✅ STATUS KEPEGAWAIAN (OPSIONAL - BARU) --}}
                <div class="form-group">
                    <label>Status Kepegawaian <span style="color:#999; font-size:0.85em;">(Opsional)</span></label>
                    <select name="status_kepegawaian">
                        <option value="">-- Pilih Status Kepegawaian --</option>
                        <option value="Aparatur Sipil Negara" {{ old('status_kepegawaian', $pengguna->status_kepegawaian) == 'Aparatur Sipil Negara' ? 'selected' : '' }}>Aparatur Sipil Negara</option>
                        <option value="Tenaga Harian Lepas" {{ old('status_kepegawaian', $pengguna->status_kepegawaian) == 'Tenaga Harian Lepas' ? 'selected' : '' }}>Tenaga Harian Lepas</option>
                        <option value="Pegawai Pemerintah dengan Perjanjian Kerja" {{ old('status_kepegawaian', $pengguna->status_kepegawaian) == 'Pegawai Pemerintah dengan Perjanjian Kerja' ? 'selected' : '' }}>Pegawai Pemerintah dengan Perjanjian Kerja</option>
                    </select>
                    <small style="color:#666; font-size:0.85em; display:block; margin-top:5px;">Status ini akan digunakan untuk Surat Keterangan Aktif</small>
                    
                    @if($pengguna->status_kepegawaian)
                        <small style="color:#059669; display:block; margin-top:5px;">
                            <i class="fas fa-id-card"></i> 
                            Status saat ini: <strong>{{ $pengguna->status_kepegawaian }}</strong>
                        </small>
                    @endif
                </div>

                {{-- ✅ UPLOAD TANDA TANGAN --}}
                <div class="form-group full-width">
                    <label>Tanda Tangan <span style="color:#999; font-size:0.85em;">(Opsional - Format: JPG, PNG | Max: 2MB)</span></label>
                    
                    {{-- Preview tanda tangan yang sudah ada --}}
                    @if($pengguna->tanda_tangan)
                        <div style="margin-bottom: 10px; padding: 10px; background: #f0f9ff; border-radius: 5px; border-left: 4px solid #0284c7;">
                            <small style="color:#0369a1; display:block; margin-bottom:5px;">
                                <i class="fas fa-signature"></i> 
                                <strong>Tanda Tangan Saat Ini:</strong>
                            </small>
                            <img src="{{ asset('storage/' . $pengguna->tanda_tangan) }}" 
                                 alt="Tanda Tangan" 
                                 id="preview-ttd-existing"
                                 style="max-width: 200px; max-height: 100px; border: 1px solid #ddd; border-radius: 4px; padding: 5px; background: white;">
                            <div style="margin-top: 5px;">
                                <label style="display: inline-flex; align-items: center; cursor: pointer;">
                                    <input type="checkbox" id="hapus_ttd" name="hapus_ttd" value="1" style="margin-right: 5px;">
                                    <span style="color: #dc2626; font-size: 0.9em;">Hapus tanda tangan ini</span>
                                </label>
                            </div>
                        </div>
                    @endif
                    
                    <input type="file" 
                           name="tanda_tangan" 
                           id="tanda_tangan"
                           accept="image/jpeg,image/png,image/jpg"
                           style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 100%;">
                    
                    @error('tanda_tangan')
                        <small style="color:#b91c1c; display:block; margin-top:5px;">{{ $message }}</small>
                    @enderror
                    
                    {{-- Preview tanda tangan baru --}}
                    <div id="preview-container" style="display:none; margin-top:10px;">
                        <small style="color:#059669; display:block; margin-bottom:5px;">
                            <i class="fas fa-eye"></i> Preview Tanda Tangan Baru:
                        </small>
                        <img id="preview-ttd" 
                             src="#" 
                             alt="Preview" 
                             style="max-width: 200px; max-height: 100px; border: 1px solid #ddd; border-radius: 4px; padding: 5px; background: white;">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">SIMPAN</button>
            </div>
        </form>
    </div>
</div>

{{-- Script untuk real-time preview umur, masa kerja, dan tanda tangan --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tanggalLahirInput = document.getElementById('tanggal_lahir');
    const tanggalMasukInput = document.getElementById('tanggal_masuk');
    const ttdInput = document.getElementById('tanda_tangan');
    const previewContainer = document.getElementById('preview-container');
    const previewTtd = document.getElementById('preview-ttd');
    const hapusTtdCheckbox = document.getElementById('hapus_ttd');
    const existingTtdPreview = document.getElementById('preview-ttd-existing');
    
    // Preview umur saat tanggal lahir diubah
    if (tanggalLahirInput) {
        tanggalLahirInput.addEventListener('change', function() {
            if (this.value) {
                const birthDate = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                
                console.log('Umur: ' + age + ' tahun');
            }
        });
    }
    
    // Preview masa kerja saat tanggal masuk diubah
    if (tanggalMasukInput) {
        tanggalMasukInput.addEventListener('change', function() {
            if (this.value) {
                const startDate = new Date(this.value);
                const today = new Date();
                
                let years = today.getFullYear() - startDate.getFullYear();
                let months = today.getMonth() - startDate.getMonth();
                let days = today.getDate() - startDate.getDate();
                
                if (days < 0) {
                    months--;
                    days += new Date(today.getFullYear(), today.getMonth(), 0).getDate();
                }
                
                if (months < 0) {
                    years--;
                    months += 12;
                }
                
                console.log('Masa Kerja: ' + years + ' tahun, ' + months + ' bulan, ' + days + ' hari');
            }
        });
    }
    
    // ✅ Preview tanda tangan baru
    if (ttdInput) {
        ttdInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Validasi ukuran file (max 2MB)
                if (file.size > 2048 * 1024) {
                    alert('Ukuran file terlalu besar! Maksimal 2MB');
                    this.value = '';
                    previewContainer.style.display = 'none';
                    return;
                }
                
                // Validasi tipe file
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file tidak valid! Hanya JPG, JPEG, dan PNG yang diperbolehkan');
                    this.value = '';
                    previewContainer.style.display = 'none';
                    return;
                }
                
                // Tampilkan preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewTtd.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.style.display = 'none';
            }
        });
    }
    
    // ✅ Handle checkbox hapus tanda tangan
    if (hapusTtdCheckbox && existingTtdPreview) {
        hapusTtdCheckbox.addEventListener('change', function() {
            if (this.checked) {
                existingTtdPreview.style.opacity = '0.3';
                existingTtdPreview.style.filter = 'grayscale(100%)';
            } else {
                existingTtdPreview.style.opacity = '1';
                existingTtdPreview.style.filter = 'none';
            }
        });
    }
});
</script>
@endsection