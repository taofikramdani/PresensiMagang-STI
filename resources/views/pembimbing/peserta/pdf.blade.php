<!DOCTYPE html>        
<head>
    <meta charset="UTF-8">
    <title>Daftar Peserta Magang {{ $pembimbing->name }}</title>
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
        
        .peserta-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
        }
        
        .peserta-table th {
            background: #00b0f0;
            color: white;
            padding: 8px 6px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #d1d5db;
            font-size: 9px;
        }
        
        .peserta-table td {
            padding: 6px 4px;
            text-align: left;
            border: 1px solid #d1d5db;
            font-size: 8px;
            vertical-align: top;
        }
        
        .peserta-table td.center {
            text-align: center;
        }
        
        .peserta-table tbody tr:nth-child(even) {
            background: #fff;
        }
        
        .peserta-table tbody tr:nth-child(odd) {
            background: #fff;
        }
        
        .status-badge {
            padding: 2px 4px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-hadir { background: #fff; color: #333; }
        .status-terlambat { background: #fff; color: #333; }
        .status-izin { background: #fff; color: #333; }
        .status-sakit { background: #fff; color: #333; }
        .status-tidak-hadir { background: #fff; color: #333; }
        
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
        <h1>DAFTAR PESERTA MAGANG</h1>
        <div class="periode">{{ $periode }}</div>
    </div>

    <!-- Informasi Pembimbing -->
    <div class="info-section">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nama Pembimbing</div>
                <div class="info-colon">:</div>
                <div class="info-value">{{ $pembimbing->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email</div>
                <div class="info-colon">:</div>
                <div class="info-value">{{ $pembimbing->email }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total Peserta</div>
                <div class="info-colon">:</div>
                <div class="info-value">{{ $peserta->count() }} orang</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal Laporan</div>
                <div class="info-colon">:</div>
                <div class="info-value">{{ $tanggal->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Tabel Peserta -->
    <table class="peserta-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 18%;">Nama Lengkap</th>
                <th style="width: 10%;">NIM</th>
                <th style="width: 15%;">Email</th>
                <th style="width: 12%;">No. HP</th>
                <th style="width: 15%;">Universitas</th>
                <th style="width: 10%;">Periode</th>
                <th style="width: 5%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($peserta as $index => $p)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $p->nama_lengkap }}</td>
                    <td class="center">{{ $p->nim }}</td>
                    <td>{{ $p->user->email ?? '-' }}</td>
                    <td class="center">{{ $p->no_telepon ?? '-' }}</td>
                    <td>{{ $p->universitas }}</td>
                    <td class="center">
                        {{ $p->tanggal_mulai ? \Carbon\Carbon::parse($p->tanggal_mulai)->format('d/m/Y') : '-' }}
                        @if($p->tanggal_selesai)
                            <br><small>s/d {{ \Carbon\Carbon::parse($p->tanggal_selesai)->format('d/m/Y') }}</small>
                        @endif
                    </td>
                    <td class="center">{{ ucfirst($p->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="center" style="padding: 20px; color: #666;">
                        Tidak ada data peserta
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer dengan Tanda Tangan -->
    <div class="footer">
        <div class="signature-section">
            <div class="signature-left">
                <div><strong>Pembuat Laporan</strong></div>
                <div class="signature-box"></div>
                <div style="margin-top: 5px;">{{ $pembimbing->name }}</div>
            </div>
            <div class="signature-right">
                <div><strong>Mengetahui</strong></div>
                <div class="signature-box"></div>
                <div style="margin-top: 5px;">___________________</div>
            </div>
        </div>
        
        <div class="print-date">
            Dicetak pada: {{ $tanggal->format('d/m/Y H:i:s') }} WIB
        </div>
    </div>
</body>
</html>