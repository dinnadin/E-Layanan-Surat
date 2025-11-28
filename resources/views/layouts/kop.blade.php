<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Surat')</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            margin: 30px 50px;
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
            top: -30;
            left: -30;
            width: 130px;
        }
        .logo-right-top {
            position: absolute;
            top: -10;
            right:0;
            width: 70px;
        }
         .logo-blu {
            position: absolute;
            top: 65px;   /* atur jarak di bawah pusvetma */
            right: 40px; /* agak ke kiri dari pusvetma */
            width: 20px;
        }
        /* Logo Garuda bawah kanan Pusvetma */
        .logo-garuda {
            position: absolute;
            top: 62px;
            right: -5;
            width:60px;
        }
        .kop-container h2, 
        .kop-container h3, 
        .kop-container p {
            margin: 0;
            padding: 0;
        }
        .kop-container h2 {
            font-size: 14pt;
            font-weight: bold;
        }
        .kop-container h3 {
            font-size: 13pt;
        }
        .kop-container p {
            font-size: 11pt;
        }
        .motto {
            text-align: center;
            margin-top: 200px;
            font-size: 10pt;
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

    {{-- Isi surat --}}
    <div class="isi-surat">
        @yield('content')
    </div>

    {{-- Motto bawah --}}
    <div class="motto">
        Hewan Sehat, Rakyat Selamat, Negara Kuat
    </div>

</body>
</html>
