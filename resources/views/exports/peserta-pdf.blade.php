<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Peserta Magang</title>
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

        .status-aktif {
            color: #16a34a;
            font-weight: bold;
        }

        .status-nonaktif {
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
            body {
                margin: 0;
            }

            .no-print {
                display: none;
            }
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
                        @php
                            $selectedLokasi = null;
                            if (request('lokasi_id')) {
                                $selectedLokasi = \App\Models\Lokasi::find(request('lokasi_id'));
                            }
                        @endphp

                        @if($selectedLokasi)
                            <div class="company-name">{{ $selectedLokasi->nama_lokasi }}</div>
                            <div class="company-subtitle">Sistem dan Teknologi Informasi Jawa Barat</div>
                            <div class="company-address">{{ $selectedLokasi->alamat }}</div>
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
        <h1>LAPORAN DATA PESERTA MAGANG</h1>
    </div>

    <!-- Information Section -->
    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="info-label">Tanggal Cetak</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</td>
            </tr>
            @if($selectedLokasi)
                <tr>
                    <td class="info-label">Filter Lokasi</td>
                    <td class="info-colon">:</td>
                    <td class="info-value">{{ $selectedLokasi->nama_lokasi }}</td>
                </tr>
            @endif
            <tr>
                <td class="info-label">Jumlah Peserta</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ $peserta->count() }} orang</td>
            </tr>
            <tr>
                <td class="info-label">Status Aktif</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ $peserta->where('status', 'aktif')->count() }} orang</td>
            </tr>
            <tr>
                <td class="info-label">Status Non-Aktif</td>
                <td class="info-colon">:</td>
                <td class="info-value">{{ $peserta->where('status', 'non-aktif')->count() }} orang</td>
            </tr>
        </table>
    </div>

    <!-- Main Table -->
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 16%;">Nama Lengkap</th>
                <th style="width: 9%;">NIM/NISN</th>
                <th style="width: 14%;">Universitas/Sekolah</th>
                <th style="width: 12%;">Jurusan</th>
                <th style="width: 14%;">Pembimbing</th>
                <th style="width: 12%;">Lokasi</th>
                <th style="width: 10%;">Periode Magang</th>
                <th style="width: 9%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($peserta as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $item->nama_lengkap ?? $item->user->name }}</td>
                    <td>{{ $item->nim }}</td>
                    <td class="text-left">{{ $item->universitas }}</td>
                    <td class="text-left">{{ $item->jurusan }}</td>
                    <td class="text-left">
                        {{ $item->pembimbingDetail ? $item->pembimbingDetail->nama_lengkap : ($item->pembimbing ? $item->pembimbing->name : 'Belum ada') }}
                    </td>
                    <td class="text-left">{{ $item->lokasi ? $item->lokasi->nama_lokasi : 'Belum ditentukan' }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') }}
                        -
                        {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}
                    </td>

                    <td class="{{ $item->status === 'aktif' ? 'status-aktif' : 'status-nonaktif' }}">
                        {{ ucfirst($item->status) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="padding: 20px; color: #666; text-align: center;">
                        Tidak ada data peserta sesuai filter yang dipilih
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