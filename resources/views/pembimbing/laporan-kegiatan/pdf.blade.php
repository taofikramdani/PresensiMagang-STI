<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kegiatan {{ $selectedPeserta ? $selectedPeserta->nama_lengkap : 'Semua Peserta' }}</title>
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
        
        .kategori-badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
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
                        @if(isset($lokasi) && $lokasi)
                            <div class="company-name">{{ $lokasi->nama_lokasi }}</div>
                            <div class="company-subtitle">Sistem dan Teknologi Informasi Jawa Barat</div>
                            <div class="company-address">{{ $lokasi->alamat }}</div>
                        @elseif(isset($selectedPeserta) && $selectedPeserta && $selectedPeserta->lokasi)
                            <div class="company-name">{{ $selectedPeserta->lokasi->nama_lokasi }}</div>
                            <div class="company-subtitle">Sistem dan Teknologi Informasi Jawa Barat</div>
                            <div class="company-address">{{ $selectedPeserta->lokasi->alamat }}</div>
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
        <h1>LAPORAN KEGIATAN PESERTA MAGANG</h1>
        <div class="periode">Periode {{ $periode }}</div>
    </div>

    <!-- Information Section -->
    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="info-label">Pembimbing</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ $pembimbing->nama_lengkap ?? $pembimbing->user->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="info-label">Peserta</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ $selectedPeserta ? ($selectedPeserta->nama_lengkap ?? $selectedPeserta->user->name ?? 'N/A') : 'Semua Peserta' }}</td>
            </tr>
            @if($selectedPeserta)
            <tr>
                <td class="info-label">NIM</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ $selectedPeserta->nim ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Universitas</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ $selectedPeserta->universitas ?? $selectedPeserta->instansi ?? '-' }}</td>
            </tr>
            @endif
            <tr>
                <td class="info-label">Kategori Filter</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ $kategoriFilter }}</td>
            </tr>
            <tr>
                <td class="info-label">Total Kegiatan</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ $kegiatanList->count() }} kegiatan</td>
            </tr>
        </table>
    </div>

    <!-- Main Table -->
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 18%;">Nama Peserta</th>
                <th style="width: 10%;">NIM</th>
                <th style="width: 8%;">Jam</th>
                <th style="width: 6%;">Durasi</th>
                <th style="width: 20%;">Judul Kegiatan</th>
                <th style="width: 12%;">Kategori</th>
                <th style="width: 12%;">Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kegiatanList as $index => $kegiatan)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($kegiatan->tanggal)->format('d/m/Y') }}</td>
                <td class="text-left">{{ $kegiatan->peserta->nama_lengkap }}</td>
                <td>{{ $kegiatan->peserta->nim }}</td>
                <td>
                    {{ $kegiatan->jam_mulai ? \Carbon\Carbon::parse($kegiatan->jam_mulai)->format('H:i') : '-' }}
                    @if($kegiatan->jam_selesai)
                        <br><small>{{ \Carbon\Carbon::parse($kegiatan->jam_selesai)->format('H:i') }}</small>
                    @endif
                </td>
                <td>
                    @if($kegiatan->jam_mulai && $kegiatan->jam_selesai)
                        @php
                            $mulai = \Carbon\Carbon::parse($kegiatan->jam_mulai);
                            $selesai = \Carbon\Carbon::parse($kegiatan->jam_selesai);
                            $durasi = $mulai->diff($selesai);
                            echo $durasi->format('%h:%I');
                        @endphp
                    @else
                        -
                    @endif
                </td>
                <td class="text-left">{{ $kegiatan->judul }}</td>
                <td>
                    <span class="kategori-badge">{{ ucfirst(str_replace('_', ' ', $kegiatan->kategori_aktivitas)) }}</span>
                </td>
                <td class="text-left">{{ Str::limit($kegiatan->deskripsi, 100) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="padding: 20px; color: #666; text-align: center;">
                    Tidak ada data kegiatan sesuai filter yang dipilih
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div class="signature-section">
            <div class="signature-box">
                <div>Mengetahui,</div>
                <div><strong>Pembimbing</strong></div>
                <div class="signature-line"></div>
                <div><strong>{{ $pembimbing->nama_lengkap }}</strong></div>
            </div>
            <div class="signature-box">
                <div>Bandung, {{ now()->locale('id')->isoFormat('D MMMM Y') }}</div>
                <div><strong>Admin Sistem</strong></div>
                <div class="signature-line"></div>
                <div><strong>Sistem Presensi Magang</strong></div>
            </div>
        </div>
        
        <div class="print-date">
            Dicetak pada: {{ now()->locale('id')->isoFormat('dddd, D MMMM Y HH:mm') }} WIB
        </div>
    </div>
</body>
</html>