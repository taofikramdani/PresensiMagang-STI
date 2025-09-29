<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Kehadiran Peserta</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
        .header { text-align: center; margin-bottom: 20px; }
        .info { margin-bottom: 15px; }
        .center { text-align: center; }
        .stats { margin-top: 20px; }
        .stats table { width: 50%; }
    </style>
</head>
<body>
    <div class="header">
        <h2>REKAP KEHADIRAN PESERTA MAGANG</h2>
        <h3>PT PLN (Persero) UP2D Jawa Barat</h3>
    </div>

    <div class="info">
        <table style="width: 50%; border: none;">
            <tr style="border: none;">
                <td style="border: none; width: 150px;"><strong>Pembimbing:</strong></td>
                <td style="border: none;">{{ $pembimbing->nama_lengkap }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>Peserta:</strong></td>
                <td style="border: none;">{{ $selectedPeserta ? $selectedPeserta->nama_lengkap : 'Semua Peserta' }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>Periode:</strong></td>
                <td style="border: none;">{{ $periode }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>Total Data:</strong></td>
                <td style="border: none;">{{ $totalPresensi }} record</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Peserta</th>
                <th>NIM</th>
                <th>Status</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Lokasi</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($presensiList as $index => $presensi)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="center">{{ $presensi->tanggal->format('d/m/Y') }}</td>
                <td>{{ $presensi->peserta->nama_lengkap }}</td>
                <td class="center">{{ $presensi->peserta->nim }}</td>
                <td class="center">{{ ucfirst($presensi->status) }}</td>
                <td class="center">{{ $presensi->jam_masuk ? substr($presensi->jam_masuk, 0, 5) : '-' }}</td>
                <td class="center">{{ $presensi->jam_keluar ? substr($presensi->jam_keluar, 0, 5) : '-' }}</td>
                <td>{{ $presensi->lokasi->nama_lokasi ?? '-' }}</td>
                <td>{{ $presensi->keterangan ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="stats">
        <h3>RINGKASAN KEHADIRAN</h3>
        <table>
            <tr>
                <td><strong>Total Presensi:</strong></td>
                <td>{{ $totalPresensi }}</td>
            </tr>
            <tr>
                <td><strong>Hadir Tepat Waktu:</strong></td>
                <td>{{ $totalTepat }}</td>
            </tr>
            <tr>
                <td><strong>Terlambat:</strong></td>
                <td>{{ $totalTerlambat }}</td>
            </tr>
            <tr>
                <td><strong>Izin:</strong></td>
                <td>{{ $totalIzin }}</td>
            </tr>
            <tr>
                <td><strong>Sakit:</strong></td>
                <td>{{ $totalSakit }}</td>
            </tr>
            <tr>
                <td><strong>Alpa:</strong></td>
                <td>{{ $totalAlpa }}</td>
            </tr>
        </table>
    </div>
</body>
</html>