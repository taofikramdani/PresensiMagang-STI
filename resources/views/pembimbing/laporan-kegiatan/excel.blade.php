<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Kegiatan Peserta</title>
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
        <h2>LAPORAN KEGIATAN PESERTA MAGANG</h2>
        <h3>PT PLN (Persero) UP2D Jawa Barat</h3>
    </div>

    <div class="info">
        <table style="width: 50%; border: none;">
            <tr style="border: none;">
                <td style="border: none; width: 150px;"><strong>Pembimbing:</strong></td>
                <td style="border: none;">{{ $pembimbing->nama_lengkap ?? $pembimbing->user->name ?? 'N/A' }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>Peserta:</strong></td>
                <td style="border: none;">{{ $selectedPeserta ? ($selectedPeserta->nama_lengkap ?? $selectedPeserta->user->name ?? 'N/A') : 'Semua Peserta' }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>Periode:</strong></td>
                <td style="border: none;">{{ $periode ?? 'Semua Periode' }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>Kategori:</strong></td>
                <td style="border: none;">{{ $kategoriFilter ?? 'Semua Kategori' }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>Total Kegiatan:</strong></td>
                <td style="border: none;">{{ $kegiatanList->count() }} kegiatan</td>
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
                <th>Jam Mulai</th>
                <th>Jam Selesai</th>
                <th>Durasi</th>
                <th>Judul Kegiatan</th>
                <th>Kategori</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kegiatanList as $index => $kegiatan)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="center">{{ \Carbon\Carbon::parse($kegiatan->tanggal)->format('d/m/Y') }}</td>
                <td>{{ $kegiatan->peserta->nama_lengkap ?? $kegiatan->peserta->user->name ?? 'N/A' }}</td>
                <td class="center">{{ $kegiatan->peserta->nim ?? '-' }}</td>
                <td class="center">{{ $kegiatan->jam_mulai ? \Carbon\Carbon::parse($kegiatan->jam_mulai)->format('H:i') : '-' }}</td>
                <td class="center">{{ $kegiatan->jam_selesai ? \Carbon\Carbon::parse($kegiatan->jam_selesai)->format('H:i') : '-' }}</td>
                <td class="center">
                    @if($kegiatan->jam_mulai && $kegiatan->jam_selesai)
                        @php
                            try {
                                $mulai = \Carbon\Carbon::parse($kegiatan->jam_mulai);
                                $selesai = \Carbon\Carbon::parse($kegiatan->jam_selesai);
                                $durasi = $mulai->diff($selesai);
                                echo $durasi->format('%h:%I');
                            } catch (\Exception $e) {
                                echo '-';
                            }
                        @endphp
                    @else
                        -
                    @endif
                </td>
                <td>{{ $kegiatan->judul ?? '-' }}</td>
                <td class="center">{{ $kegiatan->kategori_aktivitas ? ucfirst(str_replace('_', ' ', $kegiatan->kategori_aktivitas)) : '-' }}</td>
                <td>{{ $kegiatan->deskripsi ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($kegiatanList->count() == 0)
    <div style="text-align: center; padding: 20px; color: #666;">
        <p>Tidak ada data kegiatan sesuai filter yang dipilih</p>
    </div>
    @endif

    <div class="stats">
        <h3>RINGKASAN KEGIATAN</h3>
        <table>
            <tr>
                <td><strong>Total Kegiatan:</strong></td>
                <td>{{ $kegiatanList->count() }}</td>
            </tr>
            <tr>
                <td><strong>Kegiatan Meeting:</strong></td>
                <td>{{ $kegiatanList->where('kategori_aktivitas', 'meeting')->count() }}</td>
            </tr>
            <tr>
                <td><strong>Pengerjaan Tugas:</strong></td>
                <td>{{ $kegiatanList->where('kategori_aktivitas', 'pengerjaan_tugas')->count() }}</td>
            </tr>
            <tr>
                <td><strong>Dokumentasi:</strong></td>
                <td>{{ $kegiatanList->where('kategori_aktivitas', 'dokumentasi')->count() }}</td>
            </tr>
            <tr>
                <td><strong>Laporan:</strong></td>
                <td>{{ $kegiatanList->where('kategori_aktivitas', 'laporan')->count() }}</td>
            </tr>
        </table>
    </div>
</body>
</html>