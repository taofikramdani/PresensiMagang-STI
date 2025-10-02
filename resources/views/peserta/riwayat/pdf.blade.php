<!DOCTYPE html>        
<head>
    <meta charset="UTF-8">
    <title>Rekap Presensi {{ $peserta->nama_lengkap }}</title>
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
        
        .text-green { color: #333; }
        .text-blue { color: #333; }
        .text-yellow { color: #333; }
        .text-red { color: #333; }
        .text-gray { color: #6b7280; }
        
        .presensi-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
        }
        
        .presensi-table th {
            background: #00b0f0;
            color: white;
            padding: 10px 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #d1d5db;
            font-size: 9px;
        }
        
        .presensi-table td {
            padding: 8px 6px;
            text-align: center;
            border: 1px solid #d1d5db;
            font-size: 9px;
        }
        
        .presensi-table tbody tr:nth-child(even) {
            background: #fff;
        }
        
        .presensi-table tbody tr:hover {
            background: #fff;
        }
        
        .status-badge {
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-hadir { background: #fff; color: #333;  }
        .status-terlambat { background: #fff; color: #333;  }
        .status-izin { background: #fff; color: #333;  }
        .status-sakit { background: #fff; color: #333;  }
        .status-alpha { background: #fff; color: #333;  }
        .status-future { background: #fff; color: #999; font-style: italic; }
        
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
        <h1>LAPORAN REKAP PRESENSI</h1>
        <div class="periode">
            @if(isset($jenisLaporan) && $jenisLaporan == 'Periode Magang')
                Periode Magang: {{ $periode }}
            @else
                Periode {{ $periode }}
            @endif
        </div>
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
                <div class="info-label">Periode Magang</div>
                <div class="info-colon">:</div>
                <div class="info-value">
                    @if($periodeMagang)
                        {{ $periodeMagang }}
                    @else
                        Belum ditentukan
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Jenis Laporan</div>
                <div class="info-colon">:</div>
                <div class="info-value">
                    @if(isset($jenisLaporan))
                        {{ $jenisLaporan }}
                    @else
                        Bulanan
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

    @if(isset($jenisLaporan) && $jenisLaporan == 'Periode Magang')
    <!-- Keterangan untuk Periode Magang -->
    <div style="margin-bottom: 15px; padding: 8px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
        <div style="font-size: 10px; color: #6c757d;">
            <strong>Keterangan:</strong> Data menampilkan seluruh periode magang. Tanggal yang belum berjalan ditampilkan dengan data kosong (-).
        </div>
    </div>
    @endif

    <!-- Tabel Detail Presensi -->
    <table class="presensi-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Tanggal</th>
                <th width="8%">Hari</th>
                <th width="10%">Jam Masuk</th>
                <th width="10%">Jam Keluar</th>
                <th width="8%">Durasi</th>
                <th width="12%">Status</th>
                <th width="15%">Lokasi</th>
                <th width="20%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayatPresensi as $index => $presensi)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($presensi->tanggal)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($presensi->tanggal)->locale('id')->dayName }}</td>
                <td>{{ $presensi->jam_masuk ? \Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i') : '-' }}</td>
                <td>{{ $presensi->jam_keluar ? \Carbon\Carbon::parse($presensi->jam_keluar)->format('H:i') : '-' }}</td>
                <td>
                    @if($presensi->jam_masuk && $presensi->jam_keluar)
                        @php
                            $masuk = \Carbon\Carbon::parse($presensi->jam_masuk);
                            $keluar = \Carbon\Carbon::parse($presensi->jam_keluar);
                            $durasi = $masuk->diff($keluar);
                            echo $durasi->format('%h:%I');
                        @endphp
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($presensi->status)
                        <span class="status-badge status-{{ $presensi->status }}">
                            {{ ucfirst($presensi->status) }}
                        </span>
                    @elseif(isset($presensi->is_future_entry) && $presensi->is_future_entry)
                        <span class="status-badge status-future">-</span>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if(isset($presensi->lokasi) && $presensi->lokasi)
                        {{ $presensi->lokasi->nama_lokasi }}
                    @else
                        {{ $peserta->lokasi->nama_lokasi ?? 'N/A' }}
                    @endif
                </td>
                <td>
                    @if($presensi->status)
                        {{ $presensi->keterangan ?? '-' }}
                    @elseif(isset($presensi->is_future_entry) && $presensi->is_future_entry)
                        {{ $presensi->keterangan ?? '-' }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px; color: #6b7280;">
                    Tidak ada data presensi untuk periode ini
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