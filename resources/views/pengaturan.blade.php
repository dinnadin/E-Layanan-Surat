@php
    $role = strtolower(session('role') ?? 'admin');

    $layout = match($role) {
        'admin' => 'layouts.app',
        'pegawai' => 'layouts.apppegawai',
        'kepala' => 'layouts.appkepala',
        default => 'layouts.app',
    };
@endphp

@extends($layout)

@section('content')

<link rel="stylesheet" href="{{ asset('css/pengaturan.css') }}">

<div class="form-container">
    <h3 class="text-center">Pengaturan Akun</h3>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Row 1: Nama - NIP --}}
        <div class="form-row">
            <div class="form-group left">
                <label for="nama">Nama</label>
                <input type="text" id="nama" value="{{ session('nama_lengkap') ?? '-' }}" readonly>
            </div>
            <div class="form-group">
                <label for="nip">NIP</label>
                <input type="text" id="nip" value="{{ session('nip') ?? '-' }}" readonly>
            </div>
        </div>

        {{-- Row 2: Jabatan - Pangkat --}}
        <div class="form-row">
            <div class="form-group">
                <label for="jabatan">Jabatan</label>
                <input type="text" id="jabatan" class="form-control" value="{{ session('jabatan') ?? '-' }}" readonly>
            </div>
            <div class="form-group">
                <label for="pangkat">Pangkat/Golongan</label>
                <input type="text" id="pangkat_golongan_ruang" class="form-control" value="{{ session('pangkat_golongan_ruang') ?? '-' }}" readonly>
            </div>
        </div>

        {{-- Row 3: Unit Kerja - Masa Kerja --}}
        <div class="form-row">
            <div class="form-group">
                <label for="unit_kerja">Unit Kerja</label>
                <input type="text" id="unit_kerja" class="form-control" 
                    value="{{ session('unit_kerja') ?? '-' }}" readonly>
            </div>
            <div class="form-group">
                <label for="masa_kerja">Masa Kerja</label>
                <input type="text" id="masa_kerja" class="form-control" 
                    value="{{ auth()->user()->masa_kerja_lengkap ?? '-' }}" readonly>
            </div>
        </div>

        {{-- Row 4: Tanggal Lahir & Informasi Pensiun --}}
        @php
            // ✅ Cek status kepegawaian
            $statusKepegawaian = session('status_kepegawaian');
            $statusNonASN = in_array($statusKepegawaian, [
                'Pegawai Pemerintah dengan Perjanjian Kerja',
                'Tenaga Harian Lepas'
            ]);
        @endphp

        <div class="form-row">
            {{-- ✅ Tanggal Lahir - Bisa Diedit --}}
            <div class="form-group">
                <label for="tanggal_lahir">Tanggal Lahir</label>
                @php
                    $tanggalLahir = session('tanggal_lahir') ?? auth()->user()->tanggal_lahir ?? '';
                    // Format tanggal jika ada (dari database format Y-m-d)
                    if ($tanggalLahir && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalLahir)) {
                        $tanggalLahir = \Carbon\Carbon::parse($tanggalLahir)->format('Y-m-d');
                    }
                @endphp
                <input type="date" 
                       name="tanggal_lahir" 
                       id="tanggal_lahir" 
                       class="form-control"
                       value="{{ $tanggalLahir }}"
                       max="{{ date('Y-m-d') }}">
            </div>

            {{-- ✅ Hanya tampil untuk ASN --}}
            @if(!$statusNonASN)
                <div class="form-group">
                    <label for="sisa_pensiun">Pensiun Anda Kurang</label>
                    <input type="text" id="sisa_pensiun" class="form-control"
                        value="{{ auth()->user()->sisa_pensiun ?? '-' }}" readonly>
                </div>
            @else
                {{-- ✅ Untuk non-ASN, tampilkan info status kepegawaian --}}
                <div class="form-group">
                    <label for="status_kepegawaian">Status Kepegawaian</label>
                    <input type="text" id="status_kepegawaian" class="form-control"
                        value="{{ $statusKepegawaian ?? '-' }}" readonly>
                </div>
            @endif
        </div>

        {{-- Foto Profil --}}
        <div class="foto-container">
            <label for="foto">Foto Profil</label>

            <div class="foto-preview-area">

                <input type="file" name="foto" id="foto" accept="image/*">

                <div class="foto-preview-wrapper" id="fotoContainer">
                    <div id="fotoPreview" class="foto-preview">
                        @php
                            $fotoPath = session('foto');
                            $isDefault = !$fotoPath ||
                                in_array($fotoPath, [
                                    'images/default-avatar.png',
                                    'images/default-profile.png',
                                    asset('images/default-avatar.png'),
                                    asset('images/default-profile.png'),
                                ]);

                            $nama = session('nama_lengkap') ?? '';
                            $inisial = '';
                            if ($nama) {
                                $namaParts = explode(' ', $nama);
                                $inisial = strtoupper(substr($namaParts[0], 0, 1) . (isset($namaParts[1]) ? substr($namaParts[1], 0, 1) : ''));
                            }
                        @endphp

                        @if(!$isDefault)
                            <img src="{{ asset($fotoPath) }}">
                        @else
                            {{ $inisial }}
                        @endif
                    </div>
                </div>

                @if(session('foto'))
                    <button type="button" id="hapusFoto" class="btn-hapus-foto" title="Hapus Foto">
                        🗑️
                    </button>
                    <input type="hidden" name="hapus_foto" id="hapusFotoInput" value="0">
                @endif
            </div>
        </div>

        <button type="submit" class="btn-submit">Simpan</button>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const previewWrapper = document.getElementById("fotoPreview");
    const inputFoto = document.getElementById("foto");
    const hapusFoto = document.getElementById("hapusFoto");
    const hapusFotoInput = document.getElementById("hapusFotoInput");

    inputFoto.addEventListener("change", function(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            previewWrapper.innerHTML = '';

            const img = document.createElement("img");
            img.src = e.target.result;
            img.style.width = "100%";
            img.style.height = "100%";
            img.style.objectFit = "cover";
            img.style.borderRadius = "50%";
            img.style.position = "absolute";
            img.style.top = "0";
            img.style.left = "0";
            img.style.cursor = "grab";

            previewWrapper.appendChild(img);

            let startX=0, startY=0, currentX=0, currentY=0, isDragging=false;
            img.addEventListener("mousedown", e => {
                isDragging = true;
                startX = e.clientX - currentX;
                startY = e.clientY - currentY;
                img.style.cursor = "grabbing";
            });
            window.addEventListener("mouseup", () => {
                isDragging = false;
                img.style.cursor = "grab";
            });
            window.addEventListener("mousemove", e => {
                if (!isDragging) return;
                currentX = e.clientX - startX;
                currentY = e.clientY - startY;
                img.style.left = currentX + "px";
                img.style.top = currentY + "px";
            });

            if(hapusFotoInput) hapusFotoInput.value = "0";
        };
        reader.readAsDataURL(file);
    });

    if(hapusFoto) {
        hapusFoto.addEventListener("click", function() {
            if (!confirm("Yakin ingin menghapus foto profil ini?")) return;
            hapusFotoInput.value = "1";

            previewWrapper.innerHTML = '';
            const nama = "{{ session('nama_lengkap') }}";
            let inisial = '';
            if (nama) {
                const parts = nama.split(' ');
                inisial = (parts[0][0] || '') + (parts[1] ? parts[1][0] : '');
                inisial = inisial.toUpperCase();
            }
            previewWrapper.textContent = inisial;
        });
    }

    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(a => a.remove());
    }, 3500);
});
</script>

@endsection