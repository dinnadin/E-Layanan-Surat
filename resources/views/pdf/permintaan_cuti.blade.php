
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Formulir Cuti</title>
   <style>
    body { 
        font-family: Arial; 
        font-size: 12px; 
        margin: 2mm;  
    }
    table { 
        border-collapse: collapse; 
        width: 85%;      
        table-layout: auto;
        margin: 0 auto;   /* penting untuk posisi tengah */
    }
    td, th {
        border: none;
        outline: 0.5px solid #000;
        padding: 1px 3px; 
        vertical-align: top;
    }
    .center { text-align: center; }
    .no-border td { border: none; }
    .spasi-bawah { margin-bottom: 10px; }
    @page {
        size: 21cm 33cm; 
        margin: 1cm;   
    }
</style>
</head>
<body>
    <style>
    .header-kanan {
        text-align: right;
        margin: 0;
        padding: 0;
    }
    .header-kanan .blok {
        display: inline-block;
        text-align: left;
        margin: 0;
        padding: 0;
    }
    .header-kanan p {
        margin: 2px 0;
        line-height: 1.2;
    }
    </style>

<div class="header-kanan">
    <div class="blok">
        <p>
            ANAK LAMPIRAN <br>
            PERATURAN BADAN KEPEGAWAIAN NEGARA <br>
            REPUBLIK INDONESIA <br>
            NOMOR 24 TAHUN 2017 <br>
            TENTANG <br>
            TATA CARA PEMBERIAN CUTI PEGAWAI NEGERI SIPIL <br>
            Surabaya, {{ \Carbon\Carbon::parse($surat->tanggal_pengajuan)->translatedFormat('d F Y') }}
        </p>
        <p>
            <br>
            Yang terhormat, <br>
            Kepala Balai Besar Veteriner Farma Pusvetma <br>
            Di <br>
            Surabaya
        </p>
    </div>
</div>

<h4 style="text-align:center; margin-top:10px; margin-bottom:0; font-weight:normal;">
    FORMULIR PERMINTAAN DAN PEMBERIAN CUTI
</h4>

<!-- I. DATA PEGAWAI -->
<table class="spasi-bawah" style="margin-top:0;">
    <!-- Baris 1 -->
    <tr>
        <td colspan="4">I. DATA PEGAWAI</td>
    </tr>
    <!-- Baris 2 -->
    <tr>
        <td style="width:15%;">Nama</td>
        <td style="width:35%;">{{ $surat->pengguna->nama_lengkap }}</td>
        <td style="width:15%;">NIP</td>
        <td style="width:35%;">{{ $surat->pengguna->nip }}</td>
    </tr>
   <!-- Baris 3 di bagian I. DATA PEGAWAI -->
<tr>
    <td>Jabatan</td>
    <td>{{ $surat->pengguna->jabatan->nama_jabatan ?? '-' }}</td>
    <td>Masa Kerja</td>
    <td>{{ $surat->pengguna->masa_kerja_lengkap ?? '-' }}</td>
</tr>
<tr>
    <td>Unit Kerja</td>
    <td colspan="3">
        {{ $surat->pengguna->unitKerja->nama_unit_kerja ?? '-' }}
        ({{ $surat->pengguna->unitKerja->sub_unit_kerja ?? '-' }})
    </td>
</tr>
</table>
<!-- II. JENIS CUTI -->
<table>
    <tr><td colspan="4">II. JENIS CUTI YANG DIAMBIL**</td></tr>
    <tr>
        <td style="width:25%;">1. Cuti tahunan</td>
        <td style="width:25%; text-align:center;">
            {!! $surat->jenis_permohonan == 'Cuti Tahunan' ? '<span style="font-family: DejaVu Sans;">✓</span>' : '' !!}
        </td>
        <td style="width:25%;">2. Cuti besar</td>
        <td style="width:25%; text-align:center;">
            {!! $surat->jenis_permohonan == 'Cuti Besar' ? '<span style="font-family: DejaVu Sans;">✓</span>' : '' !!}
        </td>
    </tr>
    <tr>
        <td style="width:25%;">3. Cuti sakit</td>
        <td style="width:25%; text-align:center;">
            {!! $surat->jenis_permohonan == 'Cuti Sakit' ? '<span style="font-family: DejaVu Sans;">✓</span>' : '' !!}
        </td>
        <td style="width:25%;">4. Cuti melahirkan</td>
        <td style="width:25%; text-align:center;">
            {!! $surat->jenis_permohonan == 'Cuti Melahirkan' ? '<span style="font-family: DejaVu Sans;">✓</span>' : '' !!}
        </td>
    </tr>
    <tr>
        <td style="width:35%;">5. Cuti alasan penting</td>
        <td style="width:15%; text-align:center;">
            {!! $surat->jenis_permohonan == 'Cuti Alasan Penting' ? '<span style="font-family: DejaVu Sans;">✓</span>' : '' !!}
        </td>
        <td style="width:35%;">6. Cuti diluar tanggungan negara</td>
        <td style="width:15%; text-align:center;">
            {!! $surat->jenis_permohonan == 'Cuti Diluar Tanggungan Negara' ? '<span style="font-family: DejaVu Sans;">✓</span>' : '' !!}
        </td>
    </tr>
</table>

<!-- III. ALASAN -->
<table>
    <tr>
        <td colspan="4">III. ALASAN CUTI</td>
    </tr>
    <tr>
        <td colspan="4" style="text-align:center; height:25px;">
            {{ $surat->alasan }}
        </td>
    </tr>
</table>

<!-- IV. LAMANYA -->
<table>
    <!-- Baris 1 -->
    <tr>
        <td colspan="5">IV. LAMANYA CUTI</td>
    </tr>
    <!-- Baris 2 -->
    <tr>
        <td style="width:20%;">Selama</td>
        <td style="width:20%;">(hari/bulan/tahun)*</td>
        <td style="width:20%;">Mulai tanggal</td>
        <td style="width:10%;"></td>
        <td style="width:30%;">Selesai tanggal</td>
    </tr>
   <!-- Baris 3 -->
<tr>
    <td>{{ $surat->lama }}</td>
    <td>{{ $surat->satuan_lama }}</td>
    <td>{{ \Carbon\Carbon::parse($surat->tanggal_mulai)->translatedFormat('d F Y') }}</td>
    <td>SD</td>
    <td>{{ \Carbon\Carbon::parse($surat->tanggal_selesai)->translatedFormat('d F Y') }}</td>
</tr>
    <!-- Baris 4 -->
    <tr>
        <td colspan="5">&nbsp;</td>
    </tr>
</table>

<!-- V. CATATAN CUTI -->
<table>
    <tr>
        <td colspan="4">V. CATATAN CUTI ***</td>
    </tr>
    <tr>
        <td colspan="3">1. Cuti Tahunan</td>
        <td>
            2. Cuti Besar
            @if($surat->jenis_permohonan == 'Cuti Besar')
                &nbsp;&nbsp;&nbsp;<b>{{ $surat->lama }} {{ strtolower($surat->satuan_lama) }}</b>
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align:center;">Tahun</td>
        <td style="text-align:center;">Keterangan</td>
        <td>
            3. Cuti Sakit
            @if($surat->jenis_permohonan == 'Cuti Sakit')
                &nbsp;&nbsp;&nbsp;<b>{{ $surat->lama }} {{ strtolower($surat->satuan_lama) }}</b>
            @endif
        </td>
    </tr>
    <tr>
        <td style="text-align:center;">N-2</td>
        <td style="text-align:center;">
            @if($surat->jenis_permohonan == 'Cuti Tahunan')
                {{ optional($dataCuti)->n_2 ?? '-' }}
            @endif
        </td>
        <td>&nbsp;</td>
        <td>
            4. Cuti Melahirkan
            @if($surat->jenis_permohonan == 'Cuti Melahirkan')
                &nbsp;&nbsp;&nbsp;<b>{{ $surat->lama }} {{ strtolower($surat->satuan_lama) }}</b>
            @endif
        </td>
    </tr>
    <tr>
        <td style="text-align:center;">N-1</td>
        <td style="text-align:center;">
            @if($surat->jenis_permohonan == 'Cuti Tahunan')
                {{ optional($dataCuti)->n_1 ?? '-' }}
            @endif
        </td>
        <td>&nbsp;</td>
        <td>
            5. Cuti karena alasan penting
            @if($surat->jenis_permohonan == 'Cuti Alasan Penting')
                &nbsp;&nbsp;&nbsp;<b>{{ $surat->lama }} {{ strtolower($surat->satuan_lama) }}</b>
            @endif
        </td>
    </tr>
    <tr>
        <td style="text-align:center;">N</td>
        <td style="text-align:center;">
            @if($surat->jenis_permohonan == 'Cuti Tahunan')
                {{ optional($dataCuti)->n ?? '-' }}
            @endif
        </td>
        <td>&nbsp;</td>
        <td>
            6. Cuti di luar tanggungan negara
            @if($surat->jenis_permohonan == 'Cuti Diluar Tanggungan Negara')
                &nbsp;&nbsp;&nbsp;<b>{{ $surat->lama }} {{ strtolower($surat->satuan_lama) }}</b>
            @endif
        </td>
    </tr>
    <tr>
        <td style="text-align:center;">Jumlah</td>
        <td style="text-align:center;">
            @if($surat->jenis_permohonan == 'Cuti Tahunan')
                {{ optional($dataCuti)->jumlah ?? '-' }}
            @else
            @endif
        </td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td style="text-align:center;">Diambil</td>
        <td style="text-align:center;">
            @if($surat->jenis_permohonan == 'Cuti Tahunan')
                {{ optional($dataCuti)->diambil ?? '-' }}
            @else
            @endif
        </td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td style="text-align:center;">Sisa</td>
        <td style="text-align:center;">
            @if($surat->jenis_permohonan == 'Cuti Tahunan')
                {{ optional($dataCuti)->sisa ?? '-' }}
            @else
            @endif
        </td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
</table>
<!-- VI. ALAMAT -->
<table>
 <tr>
    <td colspan="3">VI. ALAMAT SELAMA MENJALANKAN CUTI : {{ $surat->alamat_cuti }}</td>
</tr>
    <td style="width:30%;"></td>
    <td style="width:5%;">Tlp:</td>
    <td style="width:25%;">{{ $surat->pengguna->telepon ?? '-' }}</td>
</tr>
</table>

<!-- Yang Mengajukan Cuti -->
<table style="width:85%; margin-top:1px; border:none;">
    <tr>
        <td style="width:50%; border:none;"></td>
        <td style="width:50%; border:none; text-align:left; padding-left: 10px;%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%">
            Surabaya, {{ \Carbon\Carbon::parse($surat->tanggal_pengajuan)->translatedFormat('d F Y') }} <br>
            Hormat Saya, <br><br><br><br><br>
            <u>{{ $surat->pengguna->nama_lengkap }}</u><br>
            NIP. {{ $surat->pengguna->nip }}
        </td>
    </tr>
</table>

<!-- VII. PERTIMBANGAN ATASAN LANGSUNG -->
<table style="width:85%; margin-top:1px; border:none;">
    <tr>
        <td colspan="4">VII. PERTIMBANGAN ATASAN LANGSUNG**</td>
    </tr>
    <tr>
  <td style="width:25%; text-align:center;">DISETUJUI</td>
        <td style="width:25%; text-align:center;">PERUBAHAN****</td>
        <td style="width:25%; text-align:center;">DITANGGUHKAN****</td>
        <td style="width:25%; text-align:center;">TIDAK DISETUJUI****</td>
    </tr>
</table>
<table style="width:85%; border:none; margin-top:1px;">
    <tr>
        <td style="width:50%; border:none;"></td>
        <td style="width:50%; border:none; text-align:left; padding-left: 10px;">
            Atasan Langsung, <br><br><br><br><br>
   <u>{{ $surat->tandatangan->nama_lengkap ?? '' }}</u><br>
            NIP. {{ $surat->tandatangan->nip ?? '' }}
        </td>
    </tr>
</table>

<!-- VIII. KEPUTUSAN PEJABAT BERWENANG -->
<table style="width:85%; margin-top:1px; border:none;">
    <tr>
        <td colspan="4">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI**</td>
    </tr>
    <tr>
        <td style="width:25%; text-align:center;">DISETUJUI</td>
        <td style="width:25%; text-align:center;">PERUBAHAN****</td>
        <td style="width:25%; text-align:center;">DITANGGUHKAN****</td>
        <td style="width:25%; text-align:center;">TIDAK DISETUJUI****</td>
    </tr>
</table>
<table style="width:85%; border:none; margin-top:1px;">
    <tr>
        <td style="width:50%; border:none;"></td>
        <td style="width:50%; border:none; text-align:left; padding-left: 10px;">
            Pejabat Berwenang, <br><br><br><br><br>
  <u>{{ $surat->penandatangan->nama_lengkap ?? '' }}</u><br>
            NIP. {{ $surat->penandatangan->nip ?? '' }}
        </td>
    </tr>
</table>
<table>
    <br>
    <tr>
        <small>Catatan:</small>
    </tr>
    <tr>
        <small>* Coret yang tidak perlu</small>
    </tr>
    <tr>
        <small>** Pilih salah satu dengan tanda (v)</small>
    </tr>
    <tr>
        <small>*** Diisi oleh pejabat yang menangani bidang kepegawaian sebelum PNS mengajukan cuti</small>
    </tr>
    <tr>
        <small>**** Diberi tanda centang dan alasannya</small>
    </tr>
    <tr>
        <small>N  = Cuti tahun berjalan</small>
    </tr>
    <tr>
        <small>N-1 = Sisa cuti tahun sebelumnya</small>
    </tr>
    <tr>
        <small>N-2 = Sisa cuti 2 tahun sebelumnya</small>
    </tr>
</table>
</body>
</html>