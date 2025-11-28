<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CSS Global -->
    <link rel="stylesheet" href="{{ asset('css/dashboardkepala.css') }}">
</head>

<body>

    <!-- Header -->
    <div class="header">
        <h1>
    <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
            E - Layanan Surat Kepegawaian
        </h1>
        <a href="{{ route('logout') }}">
            <img src="{{ asset('download/logout.png') }}" alt="Logout">
            <span>Logout</span>
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
            <div class="username">{{ session('nama_lengkap') ?? 'Pegawai' }}</div>
            <div class="role" style="font-size:12px; color:#ccc; margin-top:4px;">
                {{ session('nama_pimpinan') ?? session('nama_jabatan') ?? session('role') ?? 'Pegawai' }}
            </div>
        </div>

        <!-- Menu -->
        <a href="{{ route('dashboard.kepala') }}" class="{{ Request::routeIs('dashboard.kepala') ? 'active' : '' }}">
            <img src="{{ asset('download/dashboard.PNG') }}" alt="" style="width:40px; height:40px;">
            <span>Dashboard</span>
        </a>

        <a href="{{ route('pengajuansurat') }}" class="{{ Request::routeIs('pengajuansurat') ? 'active' : '' }}">
            <img src="{{ asset('download/pengajuan surat.png') }}" alt="" style="width:40px; height:40px;">
            <span>Pengajuan Surat</span>
        </a>

        <a href="{{ route('detail_cuti.index') }}" class="{{ Request::routeIs('detail_cuti.index') ? 'active' : '' }}">
            <img src="{{ asset('download/detail cuti.png') }}" alt="" style="width:40px; height:40px;">
            <span>Detail Cuti</span>
        </a>

        <a href="{{ route('riwayat') }}" class="{{ Request::routeIs('riwayat') ? 'active' : '' }}">
            <img src="{{ asset('download/riwayat pengajuan.PNG') }}" alt="" style="width:40px; height:40px;">
            <span>Riwayat Pengajuan</span>
        </a>

        <a href="{{ route('suratijin.index') }}" class="{{ Request::routeIs('suratijin.index') ? 'active' : '' }}">
            <img src="{{ asset('download/pelayanan surat ijin.PNG') }}" alt="" style="width:40px; height:40px;">
            <span>Pelayanan Surat Ijin</span>
        </a>

        <a href="{{ route('laporan.pengajuan.surat') }}" class="{{ Request::routeIs('laporan.pengajuan.surat') ? 'active' : '' }}">
            <img src="{{ asset('download/laporan pengajuan surat.PNG') }}" alt="" style="width:40px; height:40px;">
            <span>Laporan Pengajuan Surat</span>
        </a>

        <a href="{{ route('pengaturan') }}" class="{{ Request::routeIs('pengaturan') ? 'active' : '' }}">
            <img src="{{ asset('download/pengaturan akun.png') }}" alt="pengaturan" style="width:40px; height:40px;">
            <span>Pengaturan Akun</span>
        </a>
    </div>
    
<div class="overlay"></div>

    <!-- Content -->
    <div class="content">
        <h2>@yield('page-title')</h2>
        @yield('content')
    </div>

    <!-- Footer -->
    <div class="footer">
        © 2025 - Balai Besar Veteriner Farma Pusvetma
    </div>

    <!-- Script Sidebar -->
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