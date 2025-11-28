<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Izin</title>
    <style>
        /* ===== dasar ===== */
        body {
            font-family: "Arial";
            font-size: 12pt;
            position: relative;
        }
        .judul {
            text-align: center;
            font-weight: bold;
            margin-bottom: 30px;
            text-transform: uppercase;
        }
        .isi {
            margin-left: 60px;
        }

        .ttd {
            display: flex;
            justify-content: space-between;
            margin-top: 80px;
            font-size: 12pt;
        }

        .kolom-ttd {
            width: 45%;
            line-height: 1.5;
        }
        table td {
            padding: 3px 10px 3px 0;
        }

        .kop-container {
            text-align: center;
            border-bottom: 3px solid black;
            padding-bottom: 8px;
            margin-bottom: 20px;
            position: relative;
        }
        .logo-left {
            position: absolute;
            top: -20;
            left: 0;
            width: 130px;
        }
        .logo-right-top {
            position: absolute;
            top: 0;
            right:20;
            width: 70px;
        }
        .logo-blu {
            position: absolute;
            top: 75px;   /* atur jarak di bawah pusvetma */
            right: 70px; /* agak ke kiri dari pusvetma */
            width: 20px;
        }
        .logo-garuda {
            position: absolute;
            top: 70px;
            right: 15;
            width:60px;
        }
        .kop-container h2, 
        .kop-container h3, 
        .kop-container p {
            margin: 0;
            padding: 0;
            : #220664ff;
        }
        .kop-container h2 {
            font-size: 14pt;
            font-weight: bold;
            color: #1c0651ff;
        }
        .kop-container h3 {
            font-size: 13pt;
        }
        .kop-container p {
            font-size: 11pt;
            color: #1c0651ff;
        }

        .motto {
            position: fixed !important;
            bottom: 20px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            text-align: center !important;
            font-size: 12pt !important;
            margin: 0 !important;
            white-space: nowrap !important;
            width: auto !important;
            color: #1c0651ff;   
        }

    </style>
</head>
<body>
    <div class="kop-container">
        <img src="{{ public_path('download/logo-menteri-pertanian.PNG') }}" class="logo-left" alt="Logo Menteri Pertanian">
        <img src="{{ public_path('download/logo-pusvetma.PNG') }}" class="logo-right-top" alt="Logo Pusvetma">
        <img src="{{ public_path('download/logo-blu.PNG') }}" class="logo-blu" alt="Logo BLU">
        <img src="{{ public_path('download/logo-garuda-sertifikasi-indonesia.PNG') }}" class="logo-garuda" alt="Logo Garuda">
        <p>KEMENTERIAN PERTANIAN</p>
        <p>DIREKTORAT JENDERAL PETERNAKAN DAN KESEHATAN HEWAN</p>
        <h2>BALAI BESAR VETERINER FARMA PUSVETMA</h2>
        <p>Jalan Jenderal A. Yani 68 - 70, Surabaya 60231</p>
        <p>Telepon (031) 8291124 - 8291125, Faksimile (031) 8291183</p>
        <p>Website: pusvetma.ditjenpkh.pertanian.go.id | Email: pusvetma@pertanian.go.id</p>
    </div>
    {{-- Garis tipis tambahan --}}
    <hr style="border: 1px solid black; margin: -13 0 15px 0; color: #220664ff;">

    <h4 class="judul">SURAT IZIN</h4>

    <p class="isi">Yang bertanda tangan di bawah ini:</p>

    <div class="isi">
        <table>
            <tr><td>Nama</td>
<td>: {{ $surat->permintaan->nama_lengkap ?? $surat->permintaan->pengguna->nama_lengkap ?? '-' }}</td>

            <tr><td>NIP</td>
                <td>: {{ $surat->Permintaan->nip ?? '-' }}</td></tr>

           <tr><td>Jabatan</td>
    <td>: {{ $surat->permintaan->pengguna->jabatan->nama_jabatan ?? '-' }}</td></tr>

<tr><td>Unit Kerja</td>
    <td>: {{ $surat->permintaan->unit_kerja ?? '-' }}</td></tr>

            <tr><td>Tanggal</td>
                <td>: {{ \Carbon\Carbon::parse($surat->permintaan->mulai_tanggal ?? $surat->created_at)->translatedFormat('d F Y') }}</td></tr>

            <tr><td>Mulai (Jam)</td>
                <td>: {{ $surat->permintaan->mulai_jam ?? '-' }}</td></tr>

            <tr><td>Selesai (Jam)</td>
                <td>: {{ $surat->permintaan->selesai_jam ?? '-' }}</td></tr>

            <tr><td>Alasan</td>
                <td>: {{ $surat->permintaan->jenis_alasan ?? '-' }}</td></tr>

            <tr><td>Deskripsi Alasan</td>
                <td>: {{ $surat->permintaan->deskripsi_alasan ?? '-' }}</td></tr>
        </table>
    </div>

    {{-- BAGIAN TTD - DIUBAH AGAR JADI 1 HALAMAN --}}
    <div style="display: table; width: 100%; margin-top: 40px;">
        {{-- Kolom Kiri: Yang bersangkutan --}}
        <div style="display: table-cell; width: 50%; vertical-align: top; padding-left: 80px;">
            <div style="text-align: center; margin-bottom: 10px;">
                &nbsp;
            </div>
            <div style="text-align: left; margin-left: 0px;">
                Yang bersangkutan<br>
                
                {{-- Spasi untuk TTD - disesuaikan dengan tinggi TTD di kanan (margin 10px + tinggi img + margin 10px = sekitar 140px total) --}}
                <div style="height: 140px;"></div>
                
                <div style="margin-top: 5px;">
                    <div>{{ $surat->permintaan->nama_lengkap ?? $surat->permintaan->pengguna->nama_lengkap ?? '-' }}</div>
                    <div>NIP {{ $surat->permintaan->nip ?? '.....................' }}</div>
                </div>
            </div>
        </div>

       {{-- Kolom Kanan: Penandatangan --}}
<div style="display: table-cell; width: 50%; vertical-align: top; text-align: left;">
    <div style="text-align: left; margin-bottom: 10px; margin-left: 50px;">
        Surabaya, {{ \Carbon\Carbon::parse($surat->created_at)->translatedFormat('d F Y') }}
    </div>
    <div style="text-align: left; margin-left: 50px;">
        {{ $surat->penandatangan->pimpinan->nama_pimpinan ?? '...................' }} <br>

      
        {{-- ✅ TAMPILKAN TTD --}}
        @if(!empty($surat->penandatangan->ttd_path) && file_exists($surat->penandatangan->ttd_path))
            <img src="{{ $surat->penandatangan->ttd_path }}" 
                alt="Tanda Tangan" 
                style="margin:10px 0; width:120px; height:auto; display:block;">
        @else
            <div style="height:80px; display:flex; align-items:center;">
                <span style="color:#999; font-size:10pt;">(Tanda tangan digital)</span>
            </div>
        @endif

        <div style="margin-top:5px;">
            <div>{{ $surat->penandatangan->nama_lengkap ?? '......................' }}</div>
            <div>NIP {{ $surat->penandatangan->nip ?? '.....................' }}</div>
        </div>
    </div>
</div>
    </div>

    <div class="motto">
        Hewan Sehat, Rakyat Selamat, Negara Kuat
    </div>
</body>
</html>