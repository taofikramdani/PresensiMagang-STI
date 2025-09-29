<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Kehadiran {{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}</title>
    <meta http-equiv="Content-Type" content="application/vnd.ms-excel; charset=utf-8" />
</head>
<body>
    <!-- Header Info -->
    <table border="0" cellpadding="5" cellspacing="0" style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td colspan="10" style="text-align: center; font-size: 18px; font-weight: bold;">
                LAPORAN KEHADIRAN PESERTA MAGANG
            </td>
        </tr>
        <tr>
            <td colspan="10" style="text-align: center; font-size: 14px;">
                Tanggal: {{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}
            </td>
        </tr>
        <tr><td colspan="10">&nbsp;</td></tr>
        <tr>
            <td style="font-weight: bold;">Total Peserta:</td>
            <td>{{ $dailyStats['total_peserta'] }}</td>
            <td style="font-weight: bold;">Hadir:</td>
            <td>{{ $dailyStats['hadir'] }}</td>
            <td style="font-weight: bold;">Izin:</td>
            <td>{{ $dailyStats['izin'] }}</td>
            <td style="font-weight: bold;">Alpa:</td>
            <td>{{ $dailyStats['alpa'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Status Hari:</td>
            <td colspan="3">{{ $dailyStats['is_working_day'] ? 'Hari Kerja' : 'Hari Libur' }}</td>
            @if(!$dailyStats['is_working_day'])
            <td colspan="4" style="font-style: italic; color: #666;">*Tidak ada pengecekan alpa pada hari libur</td>
            @endif
        </tr>
        @if(isset($dailyStats['filter_info']))
        <tr>
            <td style="font-weight: bold;">Filter Aktif:</td>
            <td colspan="7" style="{{ $dailyStats['filter_info']['has_filters'] ? 'color: #0066cc;' : 'color: #666;' }}">
                {{ $dailyStats['filter_info']['filter_text'] }}
            </td>
        </tr>
        @endif
        <tr><td colspan="10">&nbsp;</td></tr>
    </table>

    <!-- Data Kehadiran -->
    <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
        <thead>
            <tr style="background-color: #f3f4f6; font-weight: bold;">
                <th style="text-align: center;">No</th>
                <th style="text-align: center;">Nama Peserta</th>
                <th style="text-align: center;">NIM</th>
                <th style="text-align: center;">Pembimbing</th>
                <th style="text-align: center;">Jam Masuk</th>
                <th style="text-align: center;">Jam Keluar</th>
                <th style="text-align: center;">Status</th>
                <th style="text-align: center;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($presensiRecords as $index => $record)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $record->peserta->nama_lengkap ?? $record->peserta->user->name }}</td>
                <td>{{ $record->peserta->nim }}</td>
                <td>{{ $record->peserta->pembimbingDetail ? $record->peserta->pembimbingDetail->nama_lengkap : 'Belum ada pembimbing' }}</td>
                <td style="text-align: center;">{{ $record->jam_masuk ? \Carbon\Carbon::parse($record->jam_masuk)->format('H:i') : '-' }}</td>
                <td style="text-align: center;">{{ $record->jam_keluar ? \Carbon\Carbon::parse($record->jam_keluar)->format('H:i') : '-' }}</td>
                <td style="text-align: center;">{{ ucfirst($record->status) }}</td>
                <td>{{ $record->keterangan ?? '-' }}</td>
            </tr>
            @empty
            @endforelse
            
            <!-- Data Peserta Alpa -->
            @if($pesertaAlpa->count() > 0)
                @foreach($pesertaAlpa as $index => $peserta)
                <tr>
                    <td style="text-align: center;">{{ $presensiRecords->count() + $index + 1 }}</td>
                    <td>{{ $peserta->nama_lengkap ?? $peserta->user->name }}</td>
                    <td>{{ $peserta->nim }}</td>
                    <td>{{ $peserta->pembimbingDetail ? $peserta->pembimbingDetail->nama_lengkap : 'Belum ada pembimbing' }}</td>
                    <td style="text-align: center;">-</td>
                    <td style="text-align: center;">-</td>
                    <td style="text-align: center;">Alpa</td>
                    <td>Tidak melakukan presensi</td>
                </tr>
                @endforeach
            @endif
            
            @if($presensiRecords->count() == 0 && $pesertaAlpa->count() == 0)
            <tr>
                <td colspan="8" style="text-align: center; color: #666; font-style: italic;">Tidak ada data kehadiran</td>
            </tr>
            @endif
        </tbody>
    </table>

    <script>
        // Set headers for Excel download
        document.addEventListener('DOMContentLoaded', function() {
            var filename = 'Laporan_Kehadiran_' + '{{ $tanggal }}';
            document.title = filename;
        });
    </script>
</body>
</html>