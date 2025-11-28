<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>

    <!-- Font & CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboardpegawai.css') }}?v={{ time() }}">
</head>
<body>

<!-- Header -->
<div class="header">
  <div class="header-left">
    <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
    <h1>E-Layanan Surat Kepegawaian</h1>
  </div>
  <a href="{{ route('logout') }}" class="logout-link">
    <img src="{{ asset('download/logout.png') }}" alt="logout" 
         style="width:30px; height:20px; vertical-align:middle; margin-right:8px;">
    <span>Logout</span>
  </a>
</div>

    <!-- Sidebar -->
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
            <div class="username">{{ session('nama_lengkap') ?? 'Pegawai' }}</div>
            <div class="role" style="font-size:12px; color:#ccc; margin-top:4px;">
                {{ session('nama_pimpinan') ?? session('nama_jabatan') ?? session('role') ?? 'Pegawai' }}
            </div>
        </div>

        <!-- Menu -->
        <a href="{{ route('dashboard.pegawai') }}" 
           class="{{ request()->routeIs('dashboard.pegawai') ? 'active' : '' }}">
            <img src="{{ asset('download/dashboard.PNG') }}" alt="Dashboard" 
                 style="width:40px; height:40px; vertical-align:middle; margin-right:-10px;">
            <span>Dashboard</span>
        </a>

        <a href="{{ route('pengajuansurat') }}" 
           class="{{ request()->routeIs('pengajuansurat') ? 'active' : '' }}">
            <img src="{{ asset('download/pengajuan surat.png') }}" alt="Pengajuan Surat" 
                 style="width:40px; height:40px; vertical-align:middle; margin-right:-10px;">
            <span>Pengajuan Surat</span>
        </a>

        <a href="{{ route('detail_cuti.index') }}" 
           class="{{ request()->routeIs('detail_cuti.index') ? 'active' : '' }}">
            <img src="{{ asset('download/detail cuti.png') }}" alt="Detail Cuti" 
                 style="width:40px; height:40px; vertical-align:middle; margin-right:-10px;">
            <span>Detail Cuti</span>
        </a>

        <a href="{{ route('riwayat') }}" 
           class="{{ request()->routeIs('riwayat') ? 'active' : '' }}">
            <img src="{{ asset('download/riwayat pengajuan.PNG') }}" alt="Riwayat Pengajuan" 
                 style="width:40px; height:40px; vertical-align:middle; margin-right:-10px;">
            <span>Riwayat Pengajuan</span>
        </a>

        <a href="{{ route('pengaturan') }}" 
           class="{{ request()->routeIs('pengaturan') ? 'active' : '' }}">
            <img src="{{ asset('download/pengaturan akun.png') }}" alt="Pengaturan Akun" 
                 style="width:40px; height:40px; vertical-align:middle; margin-right:-10px;">
            <span>Pengaturan Akun</span>
        </a>
    </div>

    <!-- Overlay (muncul saat sidebar aktif di HP) -->
    <div class="overlay" onclick="toggleSidebar()"></div>

    <!-- Content -->
    <div class="content">
        <h2>@yield('page-title', '')</h2>
        @yield('content')
    </div>

    <!-- Footer -->
    <div class="footer">
        © 2025 - Balai Besar Veteriner Farma Pusvetma
    </div>

    <!-- Script -->
    <script>
    function toggleSidebar() {
        const sidebar = document.querySelector(".sidebar");
        const content = document.querySelector(".content");
        const overlay = document.querySelector(".overlay");

        // Responsif: HP pakai .active, Desktop pakai .collapsed
        if (window.innerWidth <= 768) {
            sidebar.classList.toggle("active");
            overlay.classList.toggle("active");
        } else {
            sidebar.classList.toggle("collapsed");
            content.classList.toggle("expanded");
        }
      }
    document.querySelector(".overlay").addEventListener("click", () => {
    document.querySelector(".sidebar").classList.remove("active");
    document.querySelector(".overlay").classList.remove("active");
});
</script>

</body>
</html>