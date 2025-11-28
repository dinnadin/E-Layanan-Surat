<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Pengajuan Surat</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
     <style>
        body {
            font-family: "Helvetica Neue", Arial, sans-serif;
            color:#222;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: #fff; /* hilangkan background abu */
        }

        /* Header */
        .report-header {
            text-align:center;
            margin-bottom: 15px;
        }
        .report-title {
            font-weight:700;
            font-size:16px;
            margin:0 0 4px 0;
        }
        .report-sub {
            font-size:12px;
            margin:0;
        }

        /* Table styling */
        table {
            width:100%;
            border-collapse:collapse;
        }
        table thead th {
            border:1px solid #000;
            padding:6px;
            font-weight:700;
            font-size:12px;
            text-align:center;
            background: #e0e0e0;
        }
        table tbody td {
            border:1px solid #000;
            padding:6px;
            font-size:12px;
            vertical-align:top;
        }

        .col-no { width:6%; text-align:center; }
        .col-nama { width:28%; }
        .col-nip { width:22%; }
        .col-pengajuan { width:24%; }
        .col-tanggal { width:20%; text-align:center; }

        /* Footer */
        .report-footer {
            text-align:center;
            font-size:10px;
            color:#666;
            margin-top:12px;
        }

        @media print {
            body { margin: 0; }
            table thead th, table tbody td {
                font-size:11px;
                padding:5px;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrap">
        <div class="report" role="main">
            <div class="report-inner">
                <div class="report-header">
                    <div class="report-title">Laporan Pengajuan Surat</div>
                    <div class="report-sub">Periode Bulan: {{ $periode ?? 'xxxxxx' }}</div>
                </div>

                <table class="sheet-table" role="table" aria-label="Laporan pengajuan surat">
                    <thead>
                        <tr>
                            <th class="col-no">NO.</th>
                            <th class="col-nama_lengkap">NAMA</th>
                            <th class="col-nip">NIP</th>
                            <th class="col-pengajuan">PENGAJUAN SURAT</th>
                            <th class="col-tanggal">TANGGAL PENGAJUAN</th>
                        </tr>
                    </thead>
<tbody>
    @if(isset($pengajuan) && $pengajuan->count() > 0)
        @foreach($pengajuan as $index => $item)
            <tr>
                <td class="col-no">{{ $index + 1 }}</td>
                <td class="col-nama_lengkap">{{ $item->nama_lengkap ?? '-' }}</td>
                <td class="col-nip">{{ $item->nip ?? '-' }}</td>
<td class="col-pengajuan">
    @if($item->jenis_surat === 'Surat Ijin')
        Surat Ijin ({{ $item->keterangan_ijin ?? '-' }})
    @elseif($item->jenis_surat === 'Surat Aktif')
        Surat Aktif
    @elseif($item->jenis_surat === 'Cuti')
        Cuti ({{ $item->alasan_cuti ?? '-' }})
    @else
        Tidak diketahui
    @endif
</td>
                <td>
                    {{ \Carbon\Carbon::parse($item->tanggal_pengajuan)->format('d-m-Y') }}
                </td>
            </tr>
        @endforeach
    @else
        <tr class="empty-row">
            <td colspan="5">Belum ada data</td>
        </tr>
    @endif
</tbody>
                </table>

                <div class="report-footer">
                    Dicetak tanggal: {{ \Carbon\Carbon::now()->format('d-m-Y') }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
