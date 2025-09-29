<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Peserta Magang</title>
    <meta http-equiv="Content-Type" content="application/vnd.ms-excel; charset=utf-8" />
</head>
<body>
    <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr style="background-color: #f3f4f6; font-weight: bold;">
                <th style="text-align: center;">No</th>
                <th style="text-align: center;">Nama Lengkap</th>
                <th style="text-align: center;">NIM</th>
                <th style="text-align: center;">Email</th>
                <th style="text-align: center;">Universitas</th>
                <th style="text-align: center;">Jurusan</th>
                <th style="text-align: center;">No. HP</th>
                <th style="text-align: center;">Pembimbing</th>
                <th style="text-align: center;">Tanggal Mulai</th>
                <th style="text-align: center;">Tanggal Selesai</th>
                <th style="text-align: center;">Status</th>
                <th style="text-align: center;">Alamat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($peserta as $index => $item)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $item->nama_lengkap ?? $item->user->name }}</td>
                <td>{{ $item->nim }}</td>
                <td>{{ $item->user->email }}</td>
                <td>{{ $item->universitas }}</td>
                <td>{{ $item->jurusan }}</td>
                <td>{{ $item->no_hp }}</td>
                <td>{{ $item->pembimbingDetail ? $item->pembimbingDetail->nama_lengkap : ($item->pembimbing ? $item->pembimbing->name : 'Belum ada pembimbing') }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}</td>
                <td style="text-align: center;">{{ ucfirst($item->status) }}</td>
                <td>{{ $item->alamat }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" style="text-align: center; color: #666; font-style: italic;">Tidak ada data peserta</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        // Set headers for Excel download
        document.addEventListener('DOMContentLoaded', function() {
            var filename = 'Data_Peserta_Magang_' + new Date().toISOString().slice(0, 10).replace(/-/g, '');
            document.title = filename;
        });
    </script>
</body>
</html>