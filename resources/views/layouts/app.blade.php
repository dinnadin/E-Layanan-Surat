<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboardadmin.css') }}">
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>
            <button class="menu-toggle">☰</button> 
            E - Layanan Surat Kepegawaian 
        </h1> 
        <a href="{{ route('logout') }}"> 
            <img src="{{ asset('download/logout.png') }}" 
                 alt="" style="width:30px; height:20px; vertical-align:middle; margin-right:8px;"> 
            <span>logout</span> 
        </a> 
    </div>
<div class="sidebar">
    <div class="profile">
        <a href="{{ route('pengaturan') }}">
            <div class="avatar">
                @if(session('foto') && session('foto') !== 'images/default-avatar.png')
                    <img src="{{ asset(session('foto')) }}" alt="Profile Photo" class="avatar-img">
                @else
                    @php
                        $nama = session('nama_lengkap') ?? '';
                        $inisial = '';
                        if ($nama) {
                            $parts = explode(' ', $nama);
                            $inisial = strtoupper(substr($parts[0],0,1) . (isset($parts[1]) ? substr($parts[1],0,1) : ''));
                        }
                    @endphp
                    <div class="avatar-initials">{{ $inisial }}</div>
                @endif
            </div>

        </a>
        <div class="username">
            {{ session('nama_lengkap') ?? 'Admin' }}
        </div>
        <div class="user-role" style="font-size: 12px; color: #ccc;">
            {{ session('role') ?? 'Role' }}
        </div>
    </div>

    {{-- Dashboard --}}
    <a href="{{ route('dashboard.admin') }}" 
       class="{{ request()->routeIs('dashboard.admin') ? 'active' : '' }}">
        <img src="{{ asset('download/dashboard.PNG') }}">
        <span>Dashboard</span>
    </a>

    {{-- Data Dropdown --}}
  @php
    $dataActive = request()->routeIs('jabatan.index') ||
                  request()->routeIs('unit.index') ||
                  request()->routeIs('pangkat.index') ||
                  request()->routeIs('data-pimpinan.*'); // ✅ TAMBAHKAN || sebelumnya
@endphp
    <div class="dropdown-btn {{ $dataActive ? 'active' : '' }}">
        <img src="{{ asset('download/data.png') }}">
        <span class="menu-text">Data</span>
        <i class="bi bi-chevron-down dropdown-icon"></i>
    </div>
    
    <div class="dropdown-container" style="{{ $dataActive ? 'display:flex;' : '' }}">
        
<a href="{{ route('data-pimpinan.index') }}" class="{{ request()->routeIs('data-pimpinan.*') ? 'active' : '' }}">
    <img src="{{ asset('download/DataPimpinan.png') }}" class="submenu-icon">
    Data Pimpinan
</a>
        <a href="{{ route('jabatan.index') }}" class="{{ request()->routeIs('jabatan.index') ? 'active' : '' }}">
            <img src="{{ asset('download/data jabatan.png') }}" class="submenu-icon">
            Jabatan
        </a>
        <a href="{{ route('unit.index') }}" class="{{ request()->routeIs('unit.index') ? 'active' : '' }}">
            <img src="{{ asset('download/data unit kerja.png') }}" class="submenu-icon">
            Unit Kerja
        </a>
        <a href="{{ route('pangkat.index') }}" class="{{ request()->routeIs('pangkat.index') ? 'active' : '' }}">
            <img src="{{ asset('download/data pangkat.png') }}" class="submenu-icon">
            Pangkat/Golongan Kerja
        </a>
    </div>

    {{-- Data Libur --}}
    <a href="{{ route('data_libur.index') }}" class="{{ request()->routeIs('data_libur.index') ? 'active' : '' }}">
        <img src="{{ asset('download/data libur.png') }}">
        <span>Data Libur</span>
    </a>

    {{-- Data Pegawai --}}
    <a href="{{ route('data.pegawai') }}" class="{{ request()->routeIs('data.pegawai') ? 'active' : '' }}">
        <img src="{{ asset('download/data pegawai.png') }}">
        <span>Data Pegawai</span>
    </a>

    {{-- Data Cuti --}}
    <a href="{{ route('data_cuti.index') }}" class="{{ request()->routeIs('data_cuti.index') ? 'active' : '' }}">
        <img src="{{ asset('download/data cuti.png') }}">
        <span>Data Cuti</span>
    </a>

    {{-- Pelayanan Surat Aktif --}}
    @php
        use Illuminate\Support\Str;
        $role = strtolower(session('role'));
    @endphp

    @if(Str::contains($role, 'admin'))
        <a href="{{ route('surataktif.index') }}" class="{{ request()->routeIs('surataktif.index') ? 'active' : '' }}">
            <img src="{{ asset('download/pelayanan surat aktif.png') }}">
            <span>Pelayanan Surat Aktif</span>
        </a>
    @elseif(Str::contains($role, 'pegawai'))
        <a href="{{ route('surataktif.create') }}" class="{{ request()->routeIs('surataktif.create') ? 'active' : '' }}">
            <img src="{{ asset('download/pelayanan surat aktif.png') }}">
            <span>Pelayanan Surat Aktif</span>
        </a>
    @endif

    {{-- Laporan Pengajuan Surat --}}
    <a href="{{ route('laporan.pengajuan.surat') }}" class="{{ request()->routeIs('laporan.pengajuan.surat') ? 'active' : '' }}">
        <img src="{{ asset('download/laporan pengajuan surat.PNG') }}">
        <span>Laporan Pengajuan Surat</span>
    </a>

    {{-- Rekapitulasi Data Cuti --}}
    <a href="{{ route('rekapitulasi_cuti.index') }}" class="{{ request()->routeIs('rekapitulasi_cuti.index') ? 'active' : '' }}">
        <img src="{{ asset('download/rekapitulasi data cuti.png') }}">
        <span>Rekapitulasi Data Cuti</span>
    </a>

    {{-- Pengaturan Akun --}}
    <a href="{{ route('pengaturan') }}" class="{{ request()->routeIs('pengaturan') ? 'active' : '' }}">
        <img src="{{ asset('download/pengaturan akun.png') }}">
        <span>Pengaturan Akun</span>
    </a>
</div>
 <!-- Overlay (muncul saat sidebar aktif di HP) -->
    <div class="overlay"></div>
    
    <!-- Content -->
    <div class="content">
        @yield('content')
    </div>

    <!-- Footer -->
    <div class="footer">
        © 2025 - Balai Besar Veteriner Farma Pusvetma
    </div>

<!-- Script toggle -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.querySelector(".sidebar");
    const overlay = document.querySelector(".overlay");
    const dropdownButtons = document.querySelectorAll(".dropdown-btn");
    const menuToggle = document.querySelector(".menu-toggle");
    const content = document.querySelector(".content");
    const footer = document.querySelector(".footer");

    menuToggle.addEventListener("click", () => {
        if(window.innerWidth <= 992){
            // HP / tablet: pakai overlay
            sidebar.classList.toggle("active");
            overlay.classList.toggle("active");
        } else {
              // Desktop: toggle sidebar collapse
        sidebar.classList.toggle("collapsed");
        content.classList.toggle("expanded");
        footer.classList.toggle("expanded");
    }
    });

    // Tutup sidebar saat overlay diklik (HP)
    overlay.addEventListener("click", () => {
        sidebar.classList.remove("active");
        overlay.classList.remove("active");
    });

    // Dropdown menu handler
    dropdownButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
            const dropdown = btn.nextElementSibling;
            const icon = btn.querySelector(".dropdown-icon");

            if(dropdown.style.display === "flex"){
                dropdown.style.display = "none";
                icon.classList.remove("bi-chevron-up");
                icon.classList.add("bi-chevron-down");
            } else {
                dropdown.style.display = "flex";
                icon.classList.remove("bi-chevron-down");
                icon.classList.add("bi-chevron-up");
            }
        });
    });
});
</script>

<!-- Bootstrap JS (wajib untuk modal) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Bootstrap 5 CSS (untuk modal) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>