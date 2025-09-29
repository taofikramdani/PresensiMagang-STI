<!DOCTYPE html>        
<head>
    <meta charset="UTF-8">
    <title>Laporan Kegiatan Harian {{ $peserta->nama_lengkap }}</title>
    <style>
        body {
            font-family: Calibri, "Trebuchet MS", sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            line-height: 1.4;
            color: #000000;
        }
        
        .header {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .header table {
            width: 100%;
            border: none;
        }
        
        .header td {
            border: none;
            vertical-align: top;
            padding: 0;
        }
        
        .logo {
            width: 80px;
            height: auto;
        }
        
        .company-info {
            text-align: center;
            padding: 0 20px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #00b0f0;
            margin-bottom: 5px;
        }
        
        .company-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .company-address {
            font-size: 11px;
            color: #666;
        }
        
        .separator {
            border-bottom: 2px solid #000000;
            margin: 20px 0;
        }
        
        .document-title {
            text-align: center;
            margin: 20px 0;
        }
        
        .document-title h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            color: #333;
        }
        
        .document-title .periode {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .info-section {
            padding: 15px;
            margin-bottom: 20px;
            margin-top: 12px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 3px 0;
            width: 120px;
            color: #374151;
            vertical-align: top;
        }
        
        .info-colon {
            display: table-cell;
            width: 10px;
            padding: 3px 5px;
            vertical-align: top;
        }
        
        .info-value {
            display: table-cell;
            padding: 3px 0;
            color: #1f2937;
            vertical-align: top;
        }
        
        .kegiatan-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
        }
        
        .kegiatan-table th {
            background: #00b0f0;
            color: white;
            padding: 10px 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #d1d5db;
            font-size: 9px;
        }
        
        .kegiatan-table td {
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #d1d5db;
            font-size: 9px;
            vertical-align: top;
        }
        
        .kegiatan-table td.center {
            text-align: center;
        }
        
        .kegiatan-table tbody tr:nth-child(even) {
            background: #fff;
        }
        
        .kegiatan-table tbody tr:nth-child(odd) {
            background: #fff;
        }
        
        .date-group {
            border-top: 2px solid #00b0f0;
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #333;
        }
        
        .signature-section {
            display: table;
            width: 100%;
        }
        
        .signature-left {
            display: table-cell;
            width: 50%;
            text-align: left;
        }
        
        .signature-right {
            display: table-cell;
            width: 50%;
            text-align: right;
        }
        
        .signature-box {
            border: 1px solid #333;
            padding: 10px;
            margin-top: 10px;
            height: 60px;
        }
        
        .print-date {
            font-size: 9px;
            color: #666;
            text-align: right;
            margin-top: 20px;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        @page {
            margin: 20mm;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <table>
            <tr>
                <td style="width: 100px;">
                    <img src="{{ public_path('image/PLN.png') }}" alt="Logo PLN" class="logo">
                </td>
                <td>
                    <div class="company-info">
                        @if(isset($lokasi) && $lokasi)
                            <div class="company-name">{{ $lokasi->nama_lokasi }}</div>
                            <div class="company-subtitle">Sistem dan Teknologi Informasi Jawa Barat</div>
                            <div class="company-address">{{ $lokasi->alamat }}</div>
                        @elseif($peserta->lokasi)
                            <div class="company-name">{{ $peserta->lokasi->nama_lokasi }}</div>
                            <div class="company-subtitle">Sistem dan Teknologi Informasi Jawa Barat</div>
                            <div class="company-address">{{ $peserta->lokasi->alamat }}</div>
                        @else
                            <div class="company-name">PT PLN (Persero) UP2D Jawa Barat</div>
                            <div class="company-subtitle">Sistem dan Teknologi Informasi Jawa Barat</div>
                            <div class="company-address">Jl. Dr. Ir. Sukarno No.03, Braga, Kec. Sumur Bandung, Kota Bandung,
                                Jawa Barat 40111</div>
                        @endif
                    </div>
                </td>
                <td style="width: 100px; text-align: right;">
                    <img src="{{ public_path('image/Danantara.png') }}" alt="Logo Danantara" class="logo">
                </td>
            </tr>
        </table>
    </div>

    <!-- Separator -->
    <div class="separator"></div>

    <!-- Document Title -->
    <div class="document-title">
        <h1>LAPORAN KEGIATAN HARIAN</h1>
    </div>

    <!-- Informasi Peserta -->
    <div class="info-section">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nama Peserta</div>
                <div class="info-colon">:</div>
                <div class="info-value">{{ $peserta->nama_lengkap }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">NIM</div>
                <div class="info-colon">:</div>
                <div class="info-value">{{ $peserta->nim }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Universitas</div>
                <div class="info-colon">:</div>
                <div class="info-value">{{ $peserta->universitas }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Jurusan</div>
                <div class="info-colon">:</div>
                <div class="info-value">{{ $peserta->jurusan }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Pembimbing</div>
                <div class="info-colon">:</div>
                <div class="info-value">
                    @if($peserta->pembimbingDetail)
                        {{ $peserta->pembimbingDetail->nama_lengkap }}
                    @elseif($peserta->pembimbing)
                        {{ $peserta->pembimbing->name }}
                    @else
                        Belum ditentukan
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Periode Laporan</div>
                <div class="info-colon">:</div>
                <div class="info-value">{{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Tabel Kegiatan -->
    <table class="kegiatan-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 15%;">Waktu</th>
                <th style="width: 28%;">Judul Kegiatan</th>
                <th style="width: 35%;">Deskripsi/Hasil</th>
            </tr>
        </thead>
        <tbody>
            @php
                $counter = 1;
                $groupedKegiatans = $kegiatans->groupBy(function($kegiatan) {
                    return \Carbon\Carbon::parse($kegiatan->tanggal)->format('Y-m-d');
                });
            @endphp
            
            @forelse($groupedKegiatans as $tanggal => $kegiatanGroup)
                @foreach($kegiatanGroup as $index => $kegiatan)
                    <tr>
                        <td class="center">{{ $counter++ }}</td>
                        @if($index == 0)
                            <td class="center" rowspan="{{ $kegiatanGroup->count() }}" style="vertical-align: middle; font-weight: bold; background-color: #f8f9fa;">
                                {{ \Carbon\Carbon::parse($kegiatan->tanggal)->format('d/m/Y') }}<br>
                                <small style="font-weight: normal;">{{ \Carbon\Carbon::parse($kegiatan->tanggal)->locale('id')->dayName }}</small>
                            </td>
                        @endif
                        <td class="center">
                            {{ \Carbon\Carbon::parse($kegiatan->jam_mulai)->format('H:i') }}
                            @if($kegiatan->jam_selesai)
                                - {{ \Carbon\Carbon::parse($kegiatan->jam_selesai)->format('H:i') }}
                            @endif
                        </td>
                        <td>{{ $kegiatan->judul }}</td>
                        <td style="text-align: justify;">{{ $kegiatan->deskripsi }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="6" class="center" style="padding: 20px; color: #666;">
                        Tidak ada data kegiatan untuk periode ini
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer dengan Tanda Tangan -->
    <div class="footer">
        <div class="signature-section">
            <div class="signature-left">
                <div><strong>Peserta Magang</strong></div>
                <div class="signature-box"></div>
                <div style="margin-top: 5px;">{{ $peserta->nama_lengkap }}</div>
            </div>
            <div class="signature-right">
                <div><strong>Pembimbing</strong></div>
                <div class="signature-box"></div>
                <div style="margin-top: 5px;">
                    @if($peserta->pembimbingDetail)
                        {{ $peserta->pembimbingDetail->nama_lengkap }}
                    @elseif($peserta->pembimbing)
                        {{ $peserta->pembimbing->name }}
                    @else
                        ___________________
                    @endif
                </div>
            </div>
        </div>
        
        <div class="print-date">
            Dicetak pada: {{ \Carbon\Carbon::now('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB
        </div>
    </div>
</body>
</html>