<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Monitoring Kegiatan {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d F Y') }} - {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d F Y') }}</title>
    <meta http-equiv="Content-Type" content="application/vnd.ms-excel; charset=utf-8" />
</head>
<body>
    <!-- Header Info -->
    <table border="0" cellpadding="5" cellspacing="0" style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td colspan="6" style="text-align: center; font-size: 18px; font-weight: bold;">
                LAPORAN MONITORING KEGIATAN PESERTA MAGANG
            </td>
        </tr>
        <tr>
            <td colspan="6" style="text-align: center; font-size: 14px;">
                PT PLN (Persero) - Kantor Pusat
            </td>
        </tr>
        <tr><td colspan="6">&nbsp;</td></tr>
        
        <!-- Filter Information -->
        <tr>
            <td colspan="6" style="font-weight: bold; background-color: #f3f4f6; padding: 10px;">
                INFORMASI FILTER
            </td>
        </tr>
        @foreach($filterInfo as $key => $value)
        <tr>
            <td style="font-weight: bold; width: 150px;">{{ $key }}</td>
            <td style="width: 10px;">:</td>
            <td colspan="4">{{ $value }}</td>
        </tr>
        @endforeach
        <tr><td colspan="6">&nbsp;</td></tr>

        <!-- Statistics Summary -->
        <tr>
            <td colspan="6" style="font-weight: bold; background-color: #f3f4f6; padding: 10px;">
                RINGKASAN STATISTIK
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Total Kegiatan</td>
            <td>:</td>
            <td>{{ $statistics['total_kegiatan'] }}</td>
            <td style="font-weight: bold;">Kegiatan Hari Ini</td>
            <td>:</td>
            <td>{{ $statistics['kegiatan_hari_ini'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Kegiatan Minggu Ini</td>
            <td>:</td>
            <td>{{ $statistics['kegiatan_minggu_ini'] }}</td>
            <td style="font-weight: bold;">Rata-rata Per Hari</td>
            <td>:</td>
            <td>{{ $statistics['rata_rata_per_hari'] }}</td>
        </tr>
        <tr><td colspan="6">&nbsp;</td></tr>
    </table>

    <!-- Data Table -->
    <table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr style="background-color: #1f2937; color: white; font-weight: bold;">
                <th style="text-align: center; width: 50px;">No</th>
                <th style="text-align: center; width: 100px;">Tanggal</th>
                <th style="text-align: center; width: 200px;">Peserta</th>
                <th style="text-align: center; width: 150px;">Lokasi</th>
                <th style="text-align: center; width: 180px;">Pembimbing</th>
                <th style="text-align: center; width: 250px;">Judul Kegiatan</th>
                <th style="text-align: center; width: 120px;">Kategori</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kegiatanRecords as $index => $kegiatan)
            <tr style="{{ $index % 2 == 0 ? 'background-color: #f9fafb;' : 'background-color: white;' }}">
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="text-align: center;">{{ \Carbon\Carbon::parse($kegiatan->tanggal)->format('d/m/Y') }}</td>
                <td>{{ $kegiatan->peserta->nama_lengkap ?? 'N/A' }}</td>
                <td>{{ $kegiatan->peserta->lokasi->nama_lokasi ?? '-' }}</td>
                <td>{{ $kegiatan->peserta->pembimbingDetail->nama_lengkap ?? 'N/A' }}</td>
                <td>{{ $kegiatan->judul }}</td>
                <td style="text-align: center;">{{ $kegiatan->kategori_aktivitas ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px; font-style: italic; color: #6b7280;">
                    Tidak ada data kegiatan untuk periode yang dipilih
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <table border="0" cellpadding="5" cellspacing="0" style="width: 100%; margin-top: 30px;">
        <tr>
            <td colspan="7" style="text-align: center; font-size: 12px; color: #6b7280;">
                Laporan digenerate pada {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }} WIB
            </td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center; font-size: 12px; color: #6b7280;">
                Sistem Presensi Magang - PT PLN (Persero)
            </td>
        </tr>
    </table>
</body>
</html>