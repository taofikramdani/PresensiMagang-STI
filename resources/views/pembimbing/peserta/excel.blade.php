<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .company-info {
            margin-bottom: 5px;
        }
        
        .table-header th {
            background-color: #00B0F0;
            color: white;
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-weight: bold;
        }
        
        .table-data td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        
        .table-data .center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">PT PLN (Persero) UP2D Jawa Barat</div>
        <div class="company-info">Sistem dan Teknologi Informasi</div>
        <div style="margin-top: 10px; font-size: 16px;">DAFTAR PESERTA MAGANG</div>
        <div style="margin-top: 5px; font-size: 12px;">Pembimbing: {{ $pembimbing->name }}</div>
        <div style="font-size: 12px;">Tanggal: {{ $tanggal->format('d/m/Y') }}</div>
    </div>
    
    <table>
        <thead>
            <tr class="table-header">
                <th>No</th>
                <th>Nama Lengkap</th>
                <th>NIM</th>
                <th>Email</th>
                <th>No. Telepon</th>
                <th>Universitas</th>
                <th>Jurusan</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Selesai</th>
                <th>Status Hari Ini</th>
                <th>Status Peserta</th>
            </tr>
        </thead>
        <tbody>
            @foreach($peserta as $index => $p)
                <tr class="table-data">
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $p->nama_lengkap }}</td>
                    <td class="center">{{ $p->nim }}</td>
                    <td>{{ $p->user->email ?? '-' }}</td>
                    <td class="center">{{ $p->no_telepon ?? '-' }}</td>
                    <td>{{ $p->universitas }}</td>
                    <td>{{ $p->jurusan }}</td>
                    <td class="center">{{ $p->tanggal_mulai ? \Carbon\Carbon::parse($p->tanggal_mulai)->format('d/m/Y') : '-' }}</td>
                    <td class="center">{{ $p->tanggal_selesai ? \Carbon\Carbon::parse($p->tanggal_selesai)->format('d/m/Y') : '-' }}</td>
                    <td class="center">{{ ucfirst($p->status_hari_ini ?? 'Belum Presensi') }}</td>
                    <td class="center">{{ ucfirst($p->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="margin-top: 20px; font-size: 12px;">
        <strong>Ringkasan:</strong><br>
        Total Peserta: {{ $peserta->count() }} orang<br>
        Dicetak pada: {{ $tanggal->format('d/m/Y H:i:s') }} WIB
    </div>
</body>
</html>