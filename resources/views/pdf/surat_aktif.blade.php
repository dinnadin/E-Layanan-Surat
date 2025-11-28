<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            line-height: 1.2;
        }
        .judul {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            font-size: 14pt;
            margin-bottom: 5px;
        }
        .nomor {
            text-align: center;
            margin-bottom: 20px;
        }
        .isi {
            margin-left: 50px;
            margin-right: 50px;
            text-align: justify;
        }
      /* -- signature block: align left like your reference image -- */
.ttd {
    margin-top: 40px;
    margin-left: 390px;      /* sejajar dengan isi dokumen */
    margin-right: 50px;     /* pertahankan jarak kanan */
    font-size: 14pt;        /* ukuran untuk baris SURABAYA / tanggal */
    line-height: 1.15;
}

/* tanda tangan (gambar) akan berada pada alur dokumen, kiri */
.ttd img {
    display: block;
    margin: 12px 0 8px 0;   /* jarak vertikal; tidak di-center */
    width: 120px;
    height: auto;
}

/* nama & NIP tetap di bawah tanda tangan, rata kiri */
.nama-ttd {
    margin-top: 8px;
    font-weight: bold;      /* pertahankan bila Anda mau tetap tebal */
    margin-left: 0px;
    font-size: 12pt;        /* ukuran nama/nip */
}


        /* ===== CSS titik dua rapi ===== */
        .data-surat {
            width: 100%;
            border-collapse: collapse;
        }
        .data-surat td {
            padding: 4px 2px;
            vertical-align: top;
            font-size: 12pt;
        }
        .data-surat td.label {
            width: 260px;
            text-align: left;
            padding-right: 8px;
            white-space: nowrap;
        }
        .data-surat td.colon {
            width: 18px;
            text-align: right;
            padding-right: 6px;
        }
        .data-surat td.value {
            text-align: left;
        }
        .data-surat .spacer {
            height: 10px;
        }
       .kop-container {
    text-align: center;
    border-bottom: 2.8px solid #2e297bff;  /* garis bawah biru */
    padding: 8px 0;
    margin-bottom: 15px;
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
        /* Logo Garuda bawah kanan Pusvetma */
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
            color: #2e297bff;
        }
        .kop-container h2 {
            font-size: 14pt;
            font-weight: bold;
            color: #2e297bff;
        }
        .kop-container h3 {
            font-size: 13pt;
        }
        .kop-container p {
            font-size: 11pt;
            color: #2e297bff;       
         }
.motto {
    display: block;
    box-sizing: border-box;
    /* lebarkan elemen agar melampaui margin-left .ttd (390px) */
    width: calc(100% + 390px);
    margin: 100px auto 0;
    margin-left: -390px;   /* offset sama dengan margin-left .ttd */
    text-align: center;
    font-size: 12pt;
    color: #2e297bff;   
}
    </style>
</head>
<body>
     {{-- Kop Surat --}}
    <div class="kop-container">
       {{-- Logo kiri --}}
        <img src="{{ public_path('download/logo-menteri-pertanian.PNG') }}" class="logo-left" alt="Logo Menteri Pertanian">

        {{-- Logo kanan atas (Pusvetma) --}}
        <img src="{{ public_path('download/logo-pusvetma.PNG') }}" class="logo-right-top" alt="Logo Pusvetma">

        {{-- Logo kanan bawah (BLU + Garuda) --}}
        <div class="logo-right-bottom">
            <<img src="{{ public_path('download/logo-blu.PNG') }}" class="logo-blu" alt="Logo BLU">

        {{-- Logo Garuda bawah kanan Pusvetma --}}
        <img src="{{ public_path('download/logo-garuda-sertifikasi-indonesia.PNG') }}" class="logo-garuda" alt="Logo Garuda-Sertifikasi">
</div>

        {{-- Teks kop --}}
        <p>KEMENTERIAN PERTANIAN</p>
        <p>DIREKTORAT JENDERAL PETERNAKAN DAN KESEHATAN HEWAN</p>
        <h2>BALAI BESAR VETERINER FARMA PUSVETMA</h2>
        <p>Jalan Jenderal A. Yani 68 - 70, Surabaya 60231</p>
        <p>Telepon (031) 8291124 - 8291125, Faksimile (031) 8291183</p>
        <p>Website: pusvetma.ditjenpkh.pertanian.go.id | Email: pusvetma@pertanian.go.id</p>
    </div>
    {{-- Garis tipis tambahan --}}
<hr style="border: 0.5px solid #2e297bff; margin: -13px 0 15px 0;">


    <div class="judul">SURAT KETERANGAN</div>
    <div class="nomor">NOMOR: {{ $surat->nomor_surat }}</div>

    <div class="isi">
        <table class="data-surat">
            <!-- Bagian yang bertanda tangan -->
            <tr>
                <td class="label">Yang bertanda tangan dibawah ini</td>
                <td class="colon">&nbsp;</td>
                <td class="value"></td>
            </tr>

            <tr>
                <td class="label">Nama</td>
                <td class="colon">:</td>
                <td class="value">{{ $surat->penandatangan->nama_lengkap ?? ($surat->penandatangan->nama_lengkap ?? '-') }}</td>
            </tr>
           <tr>
    <td class="label">Pangkat/Golongan Ruang</td>
    <td class="colon">:</td>
    <td class="value">
        {{ $surat->penerima && $surat->penerima->pangkatGolongan
            ? $surat->penerima->pangkatGolongan->pangkat 
              . ' ' . $surat->penerima->pangkatGolongan->golongan 
              . '/' . $surat->penerima->pangkatGolongan->ruang
            : '-' }}
    </td>
</tr>
<tr>
    <td class="label">Jabatan</td>
    <td class="colon">:</td>
<td class="value">
    {{ $surat->penandatangan->pimpinan->nama_pimpinan 
        ?? $surat->penandatangan->jabatan->nama_jabatan 
        ?? '-' }}
</td>
</tr>

            <tr class="spacer"><td colspan="3"></td></tr>

            <!-- Bagian yang menerangkan -->
            <tr>
                <td class="label">Menerangkan dengan sesungguhnya bahwa </td>
                <td class="colon">&nbsp;</td>
                <td class="value"></td>
            </tr>

            <tr>
                <td class="label">Nama</td>
                <td class="colon">:</td>
                <td class="value">{{ $surat->penerima->nama_lengkap ?? '-' }}</td>
            </tr>
          <tr>
    <td class="label">Pangkat/Golongan Ruang</td>
    <td class="colon">:</td>
    <td class="value">
        {{ $surat->penerima && $surat->penerima->pangkatGolongan
            ? $surat->penerima->pangkatGolongan->pangkat 
              . ' ' . $surat->penerima->pangkatGolongan->golongan 
              . '/' . $surat->penerima->pangkatGolongan->ruang
            : '-' }}
    </td>
</tr>
<tr>
    <td class="label">Jabatan</td>
    <td class="colon">:</td>
    <td class="value">{{ $surat->penerima->jabatan->nama_jabatan ?? '-' }}</td>
</tr>
        </table>

       <p style="margin-top:18px;">
    Yang bersangkutan hingga saat ini adalah benar-benar dan masih aktif sebagai 
    {{ $surat->penerima->status_kepegawaian ?? '-' }}
    pada Balai Besar Veteriner Farma Pusvetma.<br>
    <br>Demikian Surat Keterangan ini kami buat dengan sebenarnya untuk dipergunakan sebagaimana mestinya.</br>
</p>

</div>

<div class="ttd">
    Surabaya, {{ \Carbon\Carbon::parse($surat->tanggal_terbit)->translatedFormat('d F Y') }} <br>
{{ $surat->penandatangan->pimpinan->nama_pimpinan 
    ?? $surat->penandatangan->jabatan->nama_jabatan 
    ?? '...................' }} <br>
    
{{-- ✅ TTD Online --}}
@if(isset($surat->penandatangan->ttd_path) && file_exists($surat->penandatangan->ttd_path))
    <img src="{{ $surat->penandatangan->ttd_path }}" width="120">
@elseif(!empty($surat->penandatangan->tanda_tangan))
    @php
        // Fallback: coba baca dari storage
        $ttdPath = $surat->penandatangan->tanda_tangan;
        if (strpos($ttdPath, 'storage/') === 0) {
            $sourcePath = public_path($ttdPath);
        } else {
            $sourcePath = storage_path('app/public/' . $ttdPath);
        }
        
        if(file_exists($sourcePath)) {
            $ttdBase64 = base64_encode(file_get_contents($sourcePath));
        } else {
            $ttdBase64 = null;
        }
    @endphp
    
    @if($ttdBase64)
        <img src="data:image/png;base64,{{ $ttdBase64 }}" width="120">
    @else
        <p style="color:red;">TTD tidak ditemukan di: {{ $sourcePath }}</p>
    @endif
@else
    <p style="color:red;">Penandatangan belum upload TTD</p>
@endif
   <div class="nama-ttd" style="font-weight: normal;">
    {{ $surat->penandatangan->nama_lengkap ?? '...................' }} <br>
    NIP {{ $surat->penandatangan->nip ?? '...................' }}
</div>
      {{-- Motto bawah --}}
    <div class="motto">
        Hewan Sehat, Rakyat Selamat, Negara Kuat
    </div>
</div>
</div>
</body>
</html>