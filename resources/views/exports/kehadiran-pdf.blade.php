<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kehadiran {{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}</title>
    <style>
        body {
            font-family: Calibri, "Trebuchet MS", sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
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
            margin: 20px 0;
        }
        
        .info-table {
            width: 100%;
            border: 1px solid #ddd;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        .info-label {
            width: 130px;
            font-weight: bold;
            background-color: #f8f9fa;
        }
        
        .info-colon {
            width: 10px;
            text-align: center;
            background-color: #f8f9fa;
        }
        
        .info-value {
            color: #333;
        }
        
        .stats-row {
            display: table;
            width: 100%;
            margin: 15px 0;
        }
        
        .stats-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
        }
        
        .stats-label {
            font-size: 11px;
            color: #666;
            font-weight: bold;
        }
        
        .stats-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10px;
        }
        
        .main-table th,
        .main-table td {
            border: 1px solid #585858;
            padding: 5px;
            text-align: center;
        }
        
        .main-table th {
            background-color: #00b0f0;
            color: white;
            font-weight: bold;
            font-size: 9px;
        }
        
        .main-table td.text-left {
            text-align: left;
        }
        
        .status-hadir {
            color: #16a34a;
            font-weight: bold;
        }
        
        .status-terlambat {
            color: #f59e0b;
            font-weight: bold;
        }
        
        .status-izin, .status-sakit {
            color: #3b82f6;
            font-weight: bold;
        }
        
        .status-alpa {
            color: #dc2626;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        
        .signature-section {
            display: table;
            width: 100%;
            margin-top: 30px;
        }
        
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 20px;
            vertical-align: top;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            width: 200px;
            margin: 60px auto 10px;
        }
        
        .print-date {
            font-size: 10px;
            color: #666;
            text-align: right;
            margin-top: 20px;
        }
        
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
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
                        <div class="company-name">PT PLN (Persero) UP2D Jawa Barat</div>
                        <div class="company-subtitle">Sistem Teknologi Informasi Jawa Barat</div>
                        <div class="company-address">Jl. Dr. Ir. Sukarno No.03, Braga, Kec. Sumur Bandung, Kota Bandung, Jawa Barat 40111</div>
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
        <h1>LAPORAN KEHADIRAN PESERTA MAGANG</h1>
        <div class="periode">{{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}</div>
    </div>

    <!-- Statistics Section -->
    <div class="stats-row">
        <div class="stats-item">
            <div class="stats-label">Total Peserta</div>
            <div class="stats-value">{{ $dailyStats['total_peserta'] }}</div>
        </div>
        <div class="stats-item">
            <div class="stats-label">Hadir</div>
            <div class="stats-value">{{ $dailyStats['hadir'] }}</div>
        </div>
        <div class="stats-item">
            <div class="stats-label">Izin/Sakit</div>
            <div class="stats-value">{{ $dailyStats['izin'] }}</div>
        </div>
        <div class="stats-item">
            <div class="stats-label">Alpa</div>
            <div class="stats-value">{{ $dailyStats['alpa'] }}</div>
        </div>
    </div>

    <!-- Information Section -->
    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="info-label">Tanggal Laporan</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}</td>
            </tr>
            <tr>
                <td class="info-label">Total Kehadiran</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ $presensiRecords->count() + $pesertaAlpa->count() }} record</td>
            </tr>
            <tr>
                <td class="info-label">Persentase Kehadiran</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ $dailyStats['total_peserta'] > 0 ? round(($dailyStats['hadir'] / $dailyStats['total_peserta']) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td class="info-label">Status Hari</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ $dailyStats['is_working_day'] ? 'Hari Kerja' : 'Hari Libur' }}</td>
            </tr>
            @if(!$dailyStats['is_working_day'])
            <tr>
                <td class="info-label">Keterangan</td>
                <td class="info-colon">:</td>
                <td class="info-value" style="font-style: italic; color: #666;">Tidak ada pengecekan alpa pada hari libur</td>
            </tr>
            @endif
            @if(isset($dailyStats['filter_info']))
            <tr>
                <td class="info-label">Filter Aktif</td>
                <td class="info-colon">:</td>
                <td class="info-value" style="{{ $dailyStats['filter_info']['has_filters'] ? 'color: #0066cc; font-weight: bold;' : 'color: #666;' }}">
                    {{ $dailyStats['filter_info']['filter_text'] }}
                </td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Main Table -->
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 20%;">Nama Peserta</th>
                <th style="width: 12%;">NIM</th>
                <th style="width: 18%;">Pembimbing</th>
                <th style="width: 10%;">Jam Masuk</th>
                <th style="width: 10%;">Jam Keluar</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 18%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($presensiRecords as $index => $record)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left">{{ $record->peserta->nama_lengkap ?? $record->peserta->user->name }}</td>
                <td>{{ $record->peserta->nim }}</td>
                <td class="text-left">{{ $record->peserta->pembimbingDetail ? $record->peserta->pembimbingDetail->nama_lengkap : 'Belum ada pembimbing' }}</td>
                <td>{{ $record->jam_masuk ? \Carbon\Carbon::parse($record->jam_masuk)->format('H:i') : '-' }}</td>
                <td>{{ $record->jam_keluar ? \Carbon\Carbon::parse($record->jam_keluar)->format('H:i') : '-' }}</td>
                <td class="status-{{ $record->status }}">{{ ucfirst($record->status) }}</td>
                <td class="text-left">{{ $record->keterangan ?? '-' }}</td>
            </tr>
            @empty
            @endforelse
            
            <!-- Data Peserta Alpa -->
            @if($pesertaAlpa->count() > 0)
                @foreach($pesertaAlpa as $index => $peserta)
                <tr>
                    <td>{{ $presensiRecords->count() + $index + 1 }}</td>
                    <td class="text-left">{{ $peserta->nama_lengkap ?? $peserta->user->name }}</td>
                    <td>{{ $peserta->nim }}</td>
                    <td class="text-left">{{ $peserta->pembimbingDetail ? $peserta->pembimbingDetail->nama_lengkap : 'Belum ada pembimbing' }}</td>
                    <td>-</td>
                    <td>-</td>
                    <td class="status-alpa">Alpa</td>
                    <td class="text-left">Tidak melakukan presensi</td>
                </tr>
                @endforeach
            @endif
            
            @if($presensiRecords->count() == 0 && $pesertaAlpa->count() == 0)
            <tr>
                <td colspan="8" style="padding: 20px; color: #666; text-align: center;">
                    Tidak ada data kehadiran untuk tanggal ini
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div class="signature-section">
            <div class="signature-box">
                <div>Mengetahui,</div>
                <div><strong>Administrator</strong></div>
                <div class="signature-line"></div>
                <div><strong>Admin Sistem</strong></div>
            </div>
            <div class="signature-box">
                <div>Bandung, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</div>
                <div><strong>Penanggung Jawab</strong></div>
                <div class="signature-line"></div>
                <div><strong>Sistem Presensi Magang</strong></div>
            </div>
        </div>
        
        <div class="print-date">
            Dicetak pada: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y HH:mm') }} WIB
        </div>
    </div>
</body>
</html>